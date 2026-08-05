<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Mail\PembimbingIndustriInvitation;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\Internship;
use App\Models\InvitationToken;
use App\Models\JobListing;
use App\Models\Lecturer;
use App\Models\Notification;
use App\Models\Period;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MagangRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $counts = [
            'pending'  => CompanyRequest::where('status', 'pending')->count(),
            'approved' => CompanyRequest::where('status', 'approved')->count(),
            'rejected' => CompanyRequest::where('status', 'rejected')->count(),
        ];

        $requests = CompanyRequest::with(['student.user', 'student.period', 'company', 'createdLecturer.user'])
            ->where('status', $status)
            ->latest()
            ->get();

        // Magang berjalan selang-seling: saat satu prodi magang, prodi lain
        // tidak. Pengajuan dari prodi yang belum gilirannya DITANDAI, bukan
        // ditolak — yang terkena justru mahasiswa mengulang, cuti, atau magang
        // mandiri di luar jadwal angkatannya, dan merekalah yang paling butuh
        // ditimbang manusia. Menutup pintunya berarti satu-satunya jalan keluar
        // adalah Kaprodi mengubah periode aktif, yang berdampak ke semua orang
        // demi satu kasus.
        $periodeBerjalan = Period::sekarang();

        return view('kaprodi.pengajuan-magang.index', compact(
            'requests', 'status', 'counts', 'periodeBerjalan'
        ));
    }

    public function approve(Request $request, CompanyRequest $magangRequest)
    {
        if ($magangRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $student = $magangRequest->student;

        if ($student->internships()->where('is_finished', false)->exists()) {
            return back()->with('error', 'Mahasiswa ini sudah memiliki magang aktif.');
        }

        // Magang WAJIB punya dosen pembimbing. Kalau disetujui tanpa dospem,
        // internships.lecturer_id jadi NULL dan mahasiswa hilang dari portal
        // dosen tanpa error apa pun (portal dosen memfilter lewat kolom itu).
        if (! $student->lecturer_id) {
            return back()->with('error',
                "{$student->user->name} belum punya dosen pembimbing. Plot dosen pembimbingnya dulu di menu Data Mahasiswa, lalu setujui pengajuan ini.");
        }

        // Resolve company
        if ($magangRequest->company_id) {
            $companyId = $magangRequest->company_id;
        } else {
            $company   = Company::create([
                'name'                => $magangRequest->company_name,
                'verification_status' => 'verified',
            ]);
            $companyId = $company->id;
        }

        // Mahasiswa memilih PIC yang SUDAH terdaftar → pakai langsung, tanpa buat
        // akun/aktivasi baru. Langsung nyangkut ke magang mahasiswa ini.
        if ($magangRequest->lecturer_industry_id) {
            $lecturer = Lecturer::find($magangRequest->lecturer_industry_id);

            Internship::create([
                'student_id'           => $student->id,
                'lecturer_id'          => $student->lecturer_id,
                'company_id'           => $companyId,
                'lecturer_industry_id' => $lecturer?->id,
                'position'             => $magangRequest->position,
                'start_date'           => $magangRequest->start_date ?? now(),
                'end_date'             => $magangRequest->end_date,
                'is_finished'          => false,
            ]);

            $magangRequest->update([
                'status'              => 'approved',
                'created_company_id'  => $companyId,
                'created_lecturer_id' => $lecturer?->id,
            ]);

            $this->syncDirectoryListing($magangRequest, $companyId);

            Notification::kirim($student->user_id, 'Pengajuan magang kamu disetujui Kaprodi.', 'pengajuan_magang',
                'Data magang sudah dibuat. Kamu bisa mulai mengisi logbook.', '/mahasiswa/magang-saya');

            return back()->with('success',
                "Pengajuan {$student->user->name} disetujui. Pembimbing industri "
                . "({$lecturer?->user?->name}) sudah terdaftar — tak perlu aktivasi ulang.");
        }

        // Buat akun pembimbing industri — belum aktif, username/password diisi saat aktivasi
        $tempUsername = 'pending_' . Str::random(10);
        $picUser = User::create([
            'name'         => $magangRequest->pic_name,
            'username'     => $tempUsername,
            'email'        => $this->uniqueEmail($magangRequest->pic_email, $tempUsername),
            'password'     => Hash::make(Str::random(32)),
            'role'         => 'lecturer_industry',
            'is_activated' => false,
        ]);

        $lecturer = Lecturer::create(['user_id' => $picUser->id]);

        // Buat invitation token (berlaku 7 hari)
        $token = InvitationToken::create([
            'user_id'    => $picUser->id,
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        // Catat internship
        Internship::create([
            'student_id'           => $student->id,
            'lecturer_id'          => $student->lecturer_id,
            'company_id'           => $companyId,
            'lecturer_industry_id' => $lecturer->id,
            'position'             => $magangRequest->position,
            'start_date'           => $magangRequest->start_date ?? now(),
            'end_date'             => $magangRequest->end_date,
            'is_finished'          => false,
        ]);

        $magangRequest->update([
            'status'              => 'approved',
            'created_company_id'  => $companyId,
            'created_lecturer_id' => $lecturer->id,
        ]);

        // Isi direktori industri: perusahaan tempat mahasiswa ini magang jadi
        // referensi bagi adik tingkat (tampil di tab Lowongan + bahan rekomendasi
        // chatbot). Kaprodi tetap bisa menambah/mengedit manual.
        $this->syncDirectoryListing($magangRequest, $companyId);

        // Kirim email aktivasi
        $activationUrl = url('/aktivasi/' . $token->token);
        try {
            Mail::to($picUser->email)->send(new PembimbingIndustriInvitation($token, $student->user->name));
            // Teks polos, tanpa HTML: nilainya berasal dari email yang diisi
            // MAHASISWA, dan dulu dirender mentah dengan {!! !!} di halaman Kaprodi.
            $mailStatus = 'Email aktivasi terkirim ke ' . $picUser->email . '.';
        } catch (\Exception $e) {
            $mailStatus = 'Email gagal terkirim. Bagikan link aktivasi ini secara manual:';
        }

        Notification::kirim($student->user_id, 'Pengajuan magang kamu disetujui Kaprodi.', 'pengajuan_magang',
            'Data magang sudah dibuat. Kamu bisa mulai mengisi logbook.', '/mahasiswa/magang-saya');

        return back()
            ->with('success', "Pengajuan {$student->user->name} disetujui.")
            ->with('activation_info', [
                'mail_status'    => $mailStatus,
                'activation_url' => $activationUrl,
                'pic_name'       => $magangRequest->pic_name,
                'pic_email'      => $picUser->email,
            ]);
    }

    public function reject(Request $request, CompanyRequest $magangRequest)
    {
        if ($magangRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $magangRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        Notification::kirim($magangRequest->student->user_id, 'Pengajuan magang kamu ditolak Kaprodi.', 'pengajuan_magang',
            $request->rejection_reason, '/mahasiswa/ajukan-magang');

        return back()->with('success', "Pengajuan {$magangRequest->student->user->name} ditolak.");
    }

    /**
     * Buat entri direktori (job_listings) dari magang yang disetujui, jika belum
     * ada entri serupa untuk perusahaan + posisi tersebut (hindari duplikat).
     */
    private function syncDirectoryListing(CompanyRequest $req, int $companyId): void
    {
        $company = Company::find($companyId);
        $title   = $req->position ?: 'Peserta Magang';

        $exists = JobListing::where('company_id', $companyId)
            ->where('title', $title)
            ->when($req->bidang, fn ($q) => $q->where('bidang', $req->bidang))
            ->exists();

        if ($exists) {
            return;
        }

        JobListing::create([
            'company_id'   => $companyId,
            'company_name' => $company?->name ?? $req->company_name,
            'title'        => $title,
            'bidang'       => $req->bidang,
            'location'     => $company?->address ?? $req->company_address,
            'status'       => 'active',
        ]);
    }

    private function uniqueEmail(?string $email, string $fallback): string
    {
        if ($email && !User::where('email', $email)->exists()) {
            return $email;
        }
        return $fallback . '@simama.local';
    }

    public function resendInvitation(CompanyRequest $magangRequest)
    {
        $lecturer = $magangRequest->createdLecturer;
        if (!$lecturer || $lecturer->user->is_activated) {
            return back()->with('error', 'Tidak bisa mengirim ulang — akun sudah aktif atau belum ada.');
        }

        // Invalidasi token lama, buat token baru
        InvitationToken::where('user_id', $lecturer->user_id)
            ->whereNull('used_at')
            ->update(['expires_at' => now()]);

        $token = InvitationToken::create([
            'user_id'    => $lecturer->user_id,
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        $student = $magangRequest->student;
        $activationUrl = url('/aktivasi/' . $token->token);

        try {
            Mail::to($lecturer->user->email)->send(new PembimbingIndustriInvitation($token, $student->user->name));
            $mailStatus = 'Email aktivasi berhasil dikirim ulang ke ' . $lecturer->user->email . '.';
        } catch (\Exception $e) {
            $mailStatus = 'Email gagal terkirim. Bagikan link ini secara manual:';
        }

        return back()
            ->with('success', 'Link aktivasi berhasil dibuat ulang.')
            ->with('activation_info', [
                'mail_status'    => $mailStatus,
                'activation_url' => $activationUrl,
                'pic_name'       => $lecturer->user->name,
                'pic_email'      => $lecturer->user->email,
            ]);
    }
}
