<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Period;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function showForm()
    {
        // Pendaftar diberi tahu periode mana yang akan ia masuki, supaya ia tak
        // menebak-nebak setelah isian "Tahun Akademik" ditiadakan.
        return view('auth.register', ['periodeBerjalan' => Period::sekarang()]);
    }

    /**
     * Pendaftaran yang ditolak Kaprodi tak boleh mengunci NIM/email selamanya.
     *
     * Akun berstatus 'rejected' tak pernah bisa dipakai masuk, jadi menahannya
     * hanya berarti orang yang bersangkutan tak bisa mendaftar ulang — persis
     * yang terjadi: pendaftar dapat pesan "NIM sudah terdaftar" lalu terpaksa
     * memakai NIM lain yang bukan miliknya.
     */
    private function pendaftaranDitolak(?User $user): bool
    {
        return $user !== null
            && $user->role === 'student'
            && $user->student?->status === 'rejected';
    }

    /** Aturan unik yang memberi pengecualian untuk pendaftaran yang ditolak. */
    private function unikKecualiDitolak(string $kolom, string $pesan): \Closure
    {
        return function ($attribute, $value, $fail) use ($kolom, $pesan) {
            $user = User::where($kolom, $value)->first();

            if ($user !== null && ! $this->pendaftaranDitolak($user)) {
                $fail($pesan);
            }
        };
    }

    public function register(Request $request)
    {
        // Perangkap bot: field ini tersembunyi di formulir, jadi hanya pengisi
        // otomatis yang mengisinya. Dijawab seolah berhasil — memberi tahu bot
        // bahwa ia tertangkap hanya membantunya menyesuaikan diri.
        if (filled($request->input('catatan_tambahan'))) {
            return redirect()->route('login')
                ->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan dari Kaprodi.');
        }

        $request->validate([
            'name'          => 'required|string|max:255',
            // NIM Polines: 5 kelompok angka dipisah titik, mis. 3.34.23.2.12.
            'username'      => ['required', 'string', 'max:50', 'regex:/^\d+\.\d+\.\d+\.\d+\.\d+$/',
                                $this->unikKecualiDitolak('username', 'NIM sudah terdaftar.')],
            'email'         => ['required', 'email', 'max:255',
                                $this->unikKecualiDitolak('email', 'Email sudah terdaftar.')],
            'password'      => 'required|string|min:8|confirmed',
            'the_class'     => 'required|string|max:50',
            // Daftar tertutup: teks bebas dulu melahirkan dua ejaan untuk satu
            // prodi yang sama.
            'study_program' => ['required', Rule::in(Student::PRODI)],
            'major'         => 'required|string|max:100',
            // `academic_year` SENGAJA tak lagi diminta. Dulu diketik sendiri
            // pendaftar dan menghasilkan nilai seperti "2023/2026" — rentang
            // tiga tahun yang bukan tahun akademik — yang merusak penyaring
            // Kaprodi. Sekarang diturunkan dari periode yang sedang berjalan.
        ], [
            'name.required'          => 'Nama wajib diisi.',
            'username.required'      => 'NIM wajib diisi.',
            'username.unique'        => 'NIM sudah terdaftar.',
            'username.regex'         => 'Format NIM tidak valid. Gunakan format seperti 3.34.23.2.12.',
            'email.required'         => 'Email wajib diisi.',
            'email.unique'           => 'Email sudah terdaftar.',
            'password.required'      => 'Password wajib diisi.',
            'password.min'           => 'Password minimal 8 karakter.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
            'the_class.required'     => 'Kelas wajib diisi.',
            'study_program.required' => 'Program studi wajib dipilih.',
            'study_program.in'       => 'Program studi tidak dikenal.',
            'major.required'         => 'Jurusan wajib diisi.',
        ]);

        $user = DB::transaction(function () use ($request) {
            // Bersihkan pendaftaran lama yang ditolak agar NIM/email-nya bebas.
            // Akun itu tak pernah bisa dipakai masuk, jadi tak ada data yang
            // hilang; tabel milik mahasiswa semuanya cascade dari users.
            User::where('username', $request->username)
                ->orWhere('email', $request->email)
                ->get()
                ->each(function (User $lama) {
                    if ($this->pendaftaranDitolak($lama)) {
                        $lama->delete();
                    }
                });

            $user = User::create([
                'name'     => $request->name,
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'student',
            ]);

            Student::create(array_merge([
                'user_id'       => $user->id,
                'the_class'     => $request->the_class,
                'study_program' => $request->study_program,
                'major'         => $request->major,
                'status'        => 'pending',
            ], Period::penempatanPendaftarBaru()));

            return $user;
        });

        // Pendaftar tak bisa masuk sampai akunnya disetujui, sementara Kaprodi
        // tak punya alasan membuka Data Mahasiswa kalau tak merasa ada yang
        // baru. Tanpa pemberitahuan ini, pendaftar bisa menunggu berhari-hari.
        foreach (User::where('role', 'kaprodi')->pluck('id') as $kaprodiId) {
            Notification::kirim(
                $kaprodiId,
                'Mahasiswa baru mendaftar: ' . $user->name,
                'pendaftaran',
                "NIM {$user->username} · {$request->the_class} · {$request->study_program}. "
                . 'Menunggu persetujuan akun.',
                '/kaprodi/mahasiswa?status=pending'
            );
        }

        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan dari Kaprodi.');
    }
}
