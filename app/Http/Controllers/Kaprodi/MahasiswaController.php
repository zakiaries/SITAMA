<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Notification;
use App\Models\Period;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $search = $request->input('search');

        $periode      = $request->input('periode') ?: Period::pilihanBawaan();
        $periodeList  = Period::terbaru()->get();
        $tanpaPeriode = Student::whereNull('period_id')->count();

        /**
         * Tab "Menunggu" SENGAJA lepas dari penyaring periode.
         *
         * Pendaftar baru belum diterima ke angkatan mana pun — persetujuan
         * Kaprodi-lah yang menempatkannya. Menyaringnya berarti menyembunyikan
         * justru orang yang menunggu ditindak, dan Kaprodi akan menyimpulkan
         * tak ada pendaftar padahal ada.
         */
        $disaring = $status !== 'pending';

        $query = Student::with([
            'user',
            'lecturer.user',
            'period',
            'internships' => fn($q) => $q->with(['company', 'lecturer.user'])->latest(),
        ]);

        if ($disaring) {
            Period::terapkan($query, $periode);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%"))
                  ->orWhere('major', 'like', "%$search%");
            });
        }

        switch ($status) {
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'aktif':
                $query->where('status', 'active')
                      ->whereHas('internships', fn($q) => $q->where('is_finished', false));
                break;
            case 'selesai':
                $query->where('status', 'active')
                      ->whereHas('internships', fn($q) => $q->where('is_finished', true));
                break;
            case 'belum_magang':
                $query->where('status', 'active')
                      ->whereDoesntHave('internships');
                break;
            case 'rejected':
                $query->where('status', 'rejected');
                break;
            case 'nonaktif':
                $query->where('status', Student::NONAKTIF);
                break;
            default:
                $query->where('status', 'active');
        }

        $students = $query->get();

        // Angka di tab harus menjawab pertanyaan yang sama dengan isi daftarnya,
        // jadi ikut disaring periode — kecuali "Menunggu", sesuai alasan di atas.
        $dalamPeriode = fn() => Period::terapkan(Student::query(), $periode);

        $counts = [
            'pending'      => Student::where('status', 'pending')->count(),
            'semua'        => $dalamPeriode()->where('status', 'active')->count(),
            'aktif'        => $dalamPeriode()->where('status', 'active')->whereHas('internships', fn($q) => $q->where('is_finished', false))->count(),
            'selesai'      => $dalamPeriode()->where('status', 'active')->whereHas('internships', fn($q) => $q->where('is_finished', true))->count(),
            'belum_magang' => $dalamPeriode()->where('status', 'active')->whereDoesntHave('internships')->count(),
            'rejected'     => $dalamPeriode()->where('status', 'rejected')->count(),
            'nonaktif'     => $dalamPeriode()->where('status', Student::NONAKTIF)->count(),
        ];

        // Dropdown "Plot Dosen" hanya untuk dosen kampus (role lecturer), BUKAN
        // pembimbing industri — mereka ditugaskan lewat magang, bukan di sini.
        $lecturers = Lecturer::whereHas('user', fn($q) => $q->where('role', 'lecturer'))
            ->with('user')->get();

        return view('kaprodi.mahasiswa.index', compact(
            'students', 'status', 'counts', 'lecturers',
            'periode', 'periodeList', 'tanpaPeriode', 'disaring'
        ));
    }

    public function approve(Student $student)
    {
        $student->update(['status' => 'active']);
        return back()->with('success', "Akun {$student->user->name} berhasil disetujui.");
    }

    public function reject(Request $request, Student $student)
    {
        $student->update(['status' => 'rejected']);
        return back()->with('success', "Akun {$student->user->name} telah ditolak.");
    }

    public function detail(Student $student)
    {
        $student->load('user');

        $internship = $student->internships()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user'])
            ->latest()
            ->first();

        $nilai = $internship?->nilaiSummary();

        $companies = $internship ? collect() : Company::orderBy('name')->get();
        $industriLecturers = $internship ? collect()
            : Lecturer::whereHas('user', fn($q) => $q->where('role', 'lecturer_industry'))->with('user')->get();

        return view('kaprodi.mahasiswa.detail', compact(
            'student', 'internship', 'nilai', 'companies', 'industriLecturers'
        ));
    }

    public function storeInternship(Request $request, Student $student)
    {
        if ($student->internships()->exists()) {
            return back()->with('error', 'Mahasiswa sudah memiliki data magang.');
        }

        // Sama seperti jalur pengajuan mahasiswa: magang tanpa dospem akan lahir
        // dengan lecturer_id NULL dan mahasiswanya tak terlihat di portal dosen.
        if (! $student->lecturer_id) {
            return back()->with('error',
                'Plot dosen pembimbing untuk mahasiswa ini dulu, baru catat data magangnya.');
        }

        $request->validate([
            'company_id'           => 'nullable|exists:companies,id',
            'company_name'         => 'required_without:company_id|nullable|string|max:255',
            'lecturer_industry_id' => 'nullable|exists:lecturers,id',
            'pic_name'             => 'required_without:lecturer_industry_id|nullable|string|max:255',
            'pic_username'         => 'required_without:lecturer_industry_id|nullable|string|max:50|unique:users,username',
            'pic_password'         => 'required_without:lecturer_industry_id|nullable|string|min:8',
            'pic_phone'            => ['nullable', 'string', 'max:50', 'regex:/^[0-9()+\-\s]{7,20}$/'],
            'position'             => 'nullable|string|max:255',
            'start_date'           => 'required|date',
            // Sama seperti jalur pengajuan mahasiswa: tanpa ini end_date tetap
            // kosong dan halaman-halaman menampilkan "Belum selesai" selamanya.
            'end_date'             => 'required|date|after:start_date',
        ], [
            'pic_phone.regex'                 => 'Nomor HP tidak valid (hanya angka dan simbol + - ( ) spasi).',
            'company_name.required_without'   => 'Pilih perusahaan yang ada atau isi nama perusahaan baru.',
            'pic_name.required_without'       => 'Pilih pembimbing yang ada atau isi nama pembimbing baru.',
            'pic_username.required_without'   => 'Username pembimbing industri wajib diisi.',
            'pic_username.unique'             => 'Username sudah dipakai, gunakan username lain.',
            'pic_password.required_without'   => 'Password pembimbing industri wajib diisi.',
            'pic_password.min'                => 'Password minimal 8 karakter.',
            'start_date.required'             => 'Tanggal mulai magang wajib diisi.',
            'end_date.required'               => 'Tanggal selesai magang wajib diisi.',
            'end_date.after'                  => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        // Resolve company
        if ($request->filled('company_id')) {
            $companyId = $request->company_id;
        } else {
            $company   = Company::create(['name' => $request->company_name, 'verification_status' => 'verified']);
            $companyId = $company->id;
        }

        // Resolve pembimbing industri
        $credentials         = null;
        $lecturerIndustryId  = null;

        if ($request->filled('lecturer_industry_id')) {
            $lecturerIndustryId = $request->lecturer_industry_id;
        } else {
            $picUser  = User::create([
                'name'     => $request->pic_name,
                'username' => $request->pic_username,
                'email'    => $request->pic_username . '@simama.local',
                'password' => Hash::make($request->pic_password),
                'role'     => 'lecturer_industry',
            ]);
            $lecturer           = Lecturer::create(['user_id' => $picUser->id]);
            $lecturerIndustryId = $lecturer->id;

            $credentials = [
                'name'     => $request->pic_name,
                'username' => $request->pic_username,
                'password' => $request->pic_password,
            ];
        }

        Internship::create([
            'student_id'           => $student->id,
            'lecturer_id'          => $student->lecturer_id,
            'company_id'           => $companyId,
            'lecturer_industry_id' => $lecturerIndustryId,
            'position'             => $request->position,
            'start_date'           => $request->start_date,
            'end_date'             => $request->end_date,
            'is_finished'          => false,
        ]);

        $response = back()->with('success', "Data magang berhasil dicatat untuk {$student->user->name}.");

        if ($credentials) {
            $response = $response->with('new_pic_credentials', $credentials);
        }

        return $response;
    }

    public function approveFinish(Student $student)
    {
        $internship = $student->internships()->latest()->first();

        if (!$internship || $internship->is_finished) {
            return back()->with('error', 'Tidak ada pengajuan selesai magang yang bisa di-ACC.');
        }

        // Hanya boleh di-ACC jika mahasiswa benar-benar sudah mengajukan selesai.
        // Pengajuan itu sendiri sudah tergerbang syarat kelengkapan (sertifikat,
        // laporan di-ACC, nilai, minimal logbook) di MagangSayaController.
        if (!$internship->finish_requested) {
            return back()->with('error', 'Mahasiswa belum mengajukan selesai magang, atau syaratnya belum lengkap.');
        }

        $internship->update(['is_finished' => true, 'finish_requested' => false]);

        Notification::kirim($student->user_id, 'Selesai magang kamu sudah di-ACC Kaprodi.', 'selesai_magang',
            'Nilai dari pembimbing kini terkunci. Kamu bisa lanjut ke tahap seminar magang.', '/mahasiswa/magang-saya');

        return back()->with('success', "Magang {$student->user->name} berhasil ditandai selesai.");
    }

    /**
     * Buka kembali magang yang sudah ditandai selesai.
     *
     * Menandai selesai akan MENGUNCI nilai dari dosen pembimbing dan pembimbing
     * industri. Tanpa jalan membuka kembali, nilai yang keliru mustahil
     * dikoreksi — karena itu wewenang ini diberikan kepada Kaprodi, pihak yang
     * menutup magangnya.
     */
    public function bukaKembaliFinish(Student $student)
    {
        $internship = $student->internships()->latest()->first();

        if (! $internship || ! $internship->is_finished) {
            return back()->with('error', 'Magang mahasiswa ini belum berstatus selesai.');
        }

        $internship->update(['is_finished' => false, 'finish_requested' => false]);

        return back()->with('success',
            "Status selesai magang {$student->user->name} dibuka kembali. Dosen pembimbing dan pembimbing industri bisa memperbaiki nilai, lalu mahasiswa mengajukan selesai magang lagi.");
    }

    public function resetPassword(Request $request, Student $student)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $student->user->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', "Password {$student->user->name} berhasil direset.");
    }

    /**
     * Nonaktifkan atau aktifkan kembali seorang mahasiswa.
     *
     * Alasan WAJIB saat menonaktifkan, dan itu inti fiturnya: tanpa alasan,
     * setahun kemudian tak ada yang ingat kenapa seseorang dinonaktifkan, dan
     * Kaprodi berikutnya tak berani mengaktifkannya kembali.
     *
     * Hanya mahasiswa yang sudah disetujui yang bisa dinonaktifkan. Pendaftar
     * yang masih menunggu ditangani lewat Setujui/Tolak, dan menonaktifkan yang
     * pendaftarannya ditolak tak punya arti apa-apa.
     */
    public function ubahStatus(Request $request, Student $student)
    {
        $keAktif = $student->nonaktif();

        if (! $keAktif && $student->status !== 'active') {
            return back()->with('error', 'Hanya mahasiswa aktif yang bisa dinonaktifkan.');
        }

        $request->validate(
            $keAktif ? [] : ['status_note' => 'required|string|max:500'],
            ['status_note.required' => 'Sebutkan alasan menonaktifkan mahasiswa ini.']
        );

        $student->update([
            'status'            => $keAktif ? 'active' : Student::NONAKTIF,
            // Alasan lama DIPERTAHANKAN saat diaktifkan kembali — ia jejak
            // riwayat, bukan penanda keadaan sekarang.
            'status_note'       => $keAktif ? $student->status_note : $request->status_note,
            'status_changed_at' => now(),
        ]);

        $nama = $student->user->name ?? 'Mahasiswa';

        return back()->with('success', $keAktif
            ? "{$nama} diaktifkan kembali."
            : "{$nama} dinonaktifkan.");
    }

    public function assignLecturer(Request $request, Student $student)
    {
        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
        ], [
            'lecturer_id.required' => 'Silakan pilih dosen pembimbing.',
        ]);

        // Plot dosen ke student langsung (sebelum magang dimulai)
        $student->update(['lecturer_id' => $request->lecturer_id]);

        // Jika sudah ada internship aktif, sinkronkan juga
        $internship = $student->internships()->latest()->first();
        if ($internship) {
            $internship->update(['lecturer_id' => $request->lecturer_id]);
        }

        Notification::kirim($student->user_id, 'Dosen pembimbing kamu sudah ditetapkan Kaprodi.', 'pengajuan_magang',
            'Sekarang kamu bisa mengajukan magang dan mengisi bimbingan.', '/mahasiswa/ajukan-magang');

        return back()->with('success', 'Dosen pembimbing berhasil ditugaskan kepada ' . $student->user->name . '.');
    }
}
