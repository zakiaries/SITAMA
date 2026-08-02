<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DosenController extends Controller
{
    /** Tambah akun dosen kampus / pembimbing industri (dibuat Kaprodi, langsung aktif). */
    public function store(Request $request)
    {
        $tab  = $request->input('tab') === 'industri' ? 'industri' : 'dosen';
        $role = $tab === 'industri' ? 'lecturer_industry' : 'lecturer';

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ], [
            'name.required'     => 'Nama wajib diisi.',
            'username.required' => 'NIP / username wajib diisi.',
            'username.unique'   => 'NIP / username sudah terdaftar.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        $user = User::create([
            'name'         => $request->name,
            'username'     => $request->username,
            'email'        => $request->email ?: ($request->username . '@simama.local'),
            'password'     => Hash::make($request->password),
            'role'         => $role,
            'is_activated' => true,
        ]);
        Lecturer::create(['user_id' => $user->id]);

        $label = $role === 'lecturer_industry' ? 'Pembimbing industri' : 'Dosen';

        return redirect()->route('kaprodi.dosen.index', ['tab' => $tab])
            ->with('success', "{$label} {$request->name} berhasil ditambahkan.");
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $tab    = $request->input('tab', 'dosen');

        $role = $tab === 'industri' ? 'lecturer_industry' : 'lecturer';

        $query = Lecturer::with('user')
            ->whereHas('user', fn($u) => $u->where('role', $role));

        if ($role === 'lecturer_industry') {
            // Pembimbing industri tak "diplot" — kaitannya hanya lewat magang.
            // Satu mahasiswa bisa punya >1 magang dengan pembimbing yang sama,
            // jadi dihitung distinct agar tak terhitung dobel.
            $query->withCount(['industryInternships as students_count' => fn ($q) => $q
                ->select(DB::raw('count(distinct student_id)'))]);
        } else {
            // Dosen kampus: yang dihitung adalah mahasiswa yang DIPLOT Kaprodi
            // (students.lecturer_id) — itulah arti "mahasiswa bimbingan", dan
            // angkanya harus langsung berubah begitu diplot, jauh sebelum
            // mahasiswanya punya magang.
            $query->withCount('students as students_count');
        }

        if ($search) {
            $query->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%"));
        }

        $lecturers = $query->get();

        $counts = [
            'dosen'   => Lecturer::whereHas('user', fn($u) => $u->where('role', 'lecturer'))->count(),
            'industri'=> Lecturer::whereHas('user', fn($u) => $u->where('role', 'lecturer_industry'))->count(),
        ];

        return view('kaprodi.dosen.index', compact('lecturers', 'tab', 'counts'));
    }

    public function detail(Lecturer $lecturer)
    {
        $lecturer->load('user');

        $isIndustry = $lecturer->user->role === 'lecturer_industry';

        if ($isIndustry) {
            $students = \App\Models\Student::whereHas('internships', fn($q) => $q->where('lecturer_industry_id', $lecturer->id))
                ->with([
                    'user',
                    'internships' => fn($q) => $q->where('lecturer_industry_id', $lecturer->id)->with('company')->latest(),
                ])
                ->get();
        } else {
            // Sumbernya students.lecturer_id (hasil plot Kaprodi), sama seperti
            // angka di daftar dosen — kalau di sini pakai internships, mahasiswa
            // yang sudah diplot tapi belum magang hilang dan jumlahnya berbeda
            // dengan angka di kartu.
            $students = \App\Models\Student::where('lecturer_id', $lecturer->id)
                ->with([
                    'user',
                    'internships' => fn($q) => $q->where('lecturer_id', $lecturer->id)->with('company')->latest(),
                ])
                ->get();
        }

        return view('kaprodi.dosen.detail', compact('lecturer', 'students', 'isIndustry'));
    }

    public function resetPassword(Request $request, Lecturer $lecturer)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $lecturer->user->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', "Password {$lecturer->user->name} berhasil direset.");
    }
}
