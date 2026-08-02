<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvitationToken;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * API auth untuk SIMAMA Mobile (token via Laravel Sanctum).
 * Aturan status mengikuti persis LoginController web — tidak mengubah web/DB.
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Rate-limit brute-force (per username+IP; aman utk WiFi kampus share-IP).
        $key = Str::lower((string) $request->username) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'username' => ['Terlalu banyak percobaan login. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'],
            ]);
        }

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        RateLimiter::clear($key);

        // Mahasiswa harus sudah disetujui Kaprodi
        if ($user->role === 'student') {
            $status = $user->student?->status;

            if ($status === 'pending') {
                throw ValidationException::withMessages([
                    'username' => ['Akun Anda masih menunggu persetujuan dari Kaprodi.'],
                ]);
            }
            if ($status === 'rejected') {
                throw ValidationException::withMessages([
                    'username' => ['Pendaftaran Anda ditolak oleh Kaprodi. Silakan hubungi program studi.'],
                ]);
            }
        }

        // Pembimbing industri harus sudah diaktivasi
        if ($user->role === 'lecturer_industry' && ! $user->is_activated) {
            throw ValidationException::withMessages([
                'username' => ['Akun Anda belum diaktivasi. Cek email Anda untuk link aktivasi.'],
            ]);
        }

        // Mobile hanya untuk 3 role
        if (! in_array($user->role, ['student', 'lecturer', 'lecturer_industry'], true)) {
            throw ValidationException::withMessages([
                'username' => ['Peran ini tidak didukung di aplikasi mobile. Silakan gunakan versi web.'],
            ]);
        }

        $token = $user->createToken('sitama-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    /** Registrasi mahasiswa (akun berstatus pending, menunggu ACC Kaprodi). */
    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => ['required', 'string', 'max:50', 'unique:users,username', 'regex:/^\d+\.\d+\.\d+\.\d+\.\d+$/'],
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'the_class'     => 'required|string|max:50',
            'study_program' => 'required|string|max:100',
            'major'         => 'required|string|max:100',
            'academic_year' => 'required|string|max:20',
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
            'study_program.required' => 'Program studi wajib diisi.',
            'major.required'         => 'Jurusan wajib diisi.',
            'academic_year.required' => 'Tahun akademik wajib diisi.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'student',
        ]);

        Student::create([
            'user_id'       => $user->id,
            'the_class'     => $request->the_class,
            'study_program' => $request->study_program,
            'major'         => $request->major,
            'academic_year' => $request->academic_year,
            'status'        => 'pending',
        ]);

        return response()->json([
            'message' => 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan dari Kaprodi.',
        ], 201);
    }

    /** Cek token aktivasi pembimbing industri (untuk menampilkan form). */
    public function activationShow(string $token)
    {
        $invitation = InvitationToken::with('user')->where('token', $token)->first();

        if (! $invitation || ! $invitation->isValid()) {
            return response()->json(['valid' => false, 'message' => 'Link aktivasi tidak valid atau sudah kedaluwarsa.'], 410);
        }

        return response()->json([
            'valid' => true,
            'user'  => [
                'name'  => $invitation->user->name,
                'email' => $invitation->user->email,
            ],
        ]);
    }

    /** Aktivasi akun pembimbing industri: set username + password. */
    public function activate(Request $request, string $token)
    {
        $invitation = InvitationToken::with('user')->where('token', $token)->first();

        if (! $invitation || ! $invitation->isValid()) {
            return response()->json(['message' => 'Link aktivasi tidak valid atau sudah kedaluwarsa.'], 410);
        }

        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan, pilih yang lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $invitation->user->update([
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'is_activated' => true,
        ]);
        $invitation->update(['used_at' => now()]);

        return response()->json([
            'message' => 'Akun berhasil diaktivasi! Silakan login dengan username dan password baru Anda.',
        ]);
    }

    /**
     * Minta link reset password: cari akun via NIM/username, kirim link ke EMAIL
     * terdaftar. Selalu balas generik (anti user-enumeration). Link menuju halaman
     * reset di web; setelah ganti, pengguna login kembali di aplikasi.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['username' => 'required|string|max:50'], [
            'username.required' => 'NIM / username wajib diisi.',
        ]);

        $user = User::where('username', $request->username)->first();
        if ($user && $user->email) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return response()->json([
            'message' => 'Jika NIM/username terdaftar dan memiliki email, link reset password telah dikirim ke email tersebut.',
        ]);
    }

    /** Set password baru dengan token dari email (opsional, bila reset dilakukan in-app). */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password berhasil diubah. Silakan login dengan password baru.']);
        }

        return response()->json(['message' => 'Link reset tidak valid atau sudah kedaluwarsa.'], 422);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus hanya token yang sedang dipakai (bukan semua device)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil keluar.',
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'username' => $user->username,
            'email'    => $user->email,
            'role'     => $user->role,
        ];
    }
}
