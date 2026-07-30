<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Internship;
use App\Models\Lecturer;
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

        $query = Student::with([
            'user',
            'lecturer.user',
            'internships' => fn($q) => $q->with(['company', 'lecturer.user'])->latest(),
        ]);

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
            default:
                $query->where('status', 'active');
        }

        $students = $query->get();

        $counts = [
            'pending'      => Student::where('status', 'pending')->count(),
            'semua'        => Student::where('status', 'active')->count(),
            'aktif'        => Student::where('status', 'active')->whereHas('internships', fn($q) => $q->where('is_finished', false))->count(),
            'selesai'      => Student::where('status', 'active')->whereHas('internships', fn($q) => $q->where('is_finished', true))->count(),
            'belum_magang' => Student::where('status', 'active')->whereDoesntHave('internships')->count(),
            'rejected'     => Student::where('status', 'rejected')->count(),
        ];

        // Dropdown "Plot Dosen" hanya untuk dosen kampus (role lecturer), BUKAN
        // pembimbing industri — mereka ditugaskan lewat magang, bukan di sini.
        $lecturers = Lecturer::whereHas('user', fn($q) => $q->where('role', 'lecturer'))
            ->with('user')->get();

        return view('kaprodi.mahasiswa.index', compact('students', 'status', 'counts', 'lecturers'));
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
            'pic_password'         => 'required_without:lecturer_industry_id|nullable|string|min:6',
            'pic_phone'            => ['nullable', 'string', 'max:50', 'regex:/^[0-9()+\-\s]{7,20}$/'],
            'position'             => 'nullable|string|max:255',
            'start_date'           => 'required|date',
        ], [
            'pic_phone.regex'                 => 'Nomor HP tidak valid (hanya angka dan simbol + - ( ) spasi).',
            'company_name.required_without'   => 'Pilih perusahaan yang ada atau isi nama perusahaan baru.',
            'pic_name.required_without'       => 'Pilih pembimbing yang ada atau isi nama pembimbing baru.',
            'pic_username.required_without'   => 'Username pembimbing industri wajib diisi.',
            'pic_username.unique'             => 'Username sudah dipakai, gunakan username lain.',
            'pic_password.required_without'   => 'Password pembimbing industri wajib diisi.',
            'pic_password.min'                => 'Password minimal 6 karakter.',
            'start_date.required'             => 'Tanggal mulai magang wajib diisi.',
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

        return back()->with('success', "Magang {$student->user->name} berhasil ditandai selesai.");
    }

    public function resetPassword(Request $request, Student $student)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $student->user->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', "Password {$student->user->name} berhasil direset.");
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

        return back()->with('success', 'Dosen pembimbing berhasil ditugaskan kepada ' . $student->user->name . '.');
    }
}
