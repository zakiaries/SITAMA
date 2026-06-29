<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Mail\PembimbingIndustriInvitation;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\Internship;
use App\Models\InvitationToken;
use App\Models\Lecturer;
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

        $requests = CompanyRequest::with(['student.user', 'company', 'createdLecturer.user'])
            ->where('status', $status)
            ->latest()
            ->get();

        return view('kaprodi.pengajuan-magang.index', compact('requests', 'status', 'counts'));
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
            'is_finished'          => false,
        ]);

        $magangRequest->update([
            'status'              => 'approved',
            'created_company_id'  => $companyId,
            'created_lecturer_id' => $lecturer->id,
        ]);

        // Kirim email aktivasi
        $activationUrl = url('/aktivasi/' . $token->token);
        try {
            Mail::to($picUser->email)->send(new PembimbingIndustriInvitation($token, $student->user->name));
            $mailStatus = "Email aktivasi terkirim ke <strong>{$picUser->email}</strong>.";
        } catch (\Exception $e) {
            $mailStatus = "Email gagal terkirim. Bagikan link aktivasi ini secara manual:";
        }

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

        return back()->with('success', "Pengajuan {$magangRequest->student->user->name} ditolak.");
    }

    private function uniqueEmail(?string $email, string $fallback): string
    {
        if ($email && !User::where('email', $email)->exists()) {
            return $email;
        }
        return $fallback . '@sitama.local';
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
            $mailStatus = "Email aktivasi berhasil dikirim ulang ke <strong>{$lecturer->user->email}</strong>.";
        } catch (\Exception $e) {
            $mailStatus = "Email gagal terkirim. Bagikan link ini secara manual:";
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
