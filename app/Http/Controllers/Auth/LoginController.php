<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /** Maksimal percobaan login gagal sebelum dikunci sementara. */
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    /** Kunci throttle per (username + IP): aman untuk WiFi kampus yang share IP. */
    private function throttleKey(Request $request): string
    {
        return Str::lower((string) $request->input('username')) . '|' . $request->ip();
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Rate-limit anti brute-force (per username+IP).
        $key = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->withInput($request->only('username'));
        }

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->remember)) {
            // Kredensial benar → reset penghitung (bukan serangan).
            RateLimiter::clear($key);
            $user = Auth::user();

            // Cek status akun mahasiswa (harus disetujui Kaprodi dulu)
            if ($user->role === 'student' && $user->student) {
                $status = $user->student->status;

                if ($status === 'pending') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'username' => 'Akun Anda masih menunggu persetujuan dari Kaprodi. Silakan coba lagi nanti.',
                    ])->withInput($request->only('username'));
                }

                if ($status === 'rejected') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'username' => 'Pendaftaran Anda ditolak oleh Kaprodi. Silakan hubungi pihak program studi.',
                    ])->withInput($request->only('username'));
                }

                // Nonaktif: alasannya ikut ditampilkan. Ditolak masuk tanpa
                // penjelasan hanya akan berakhir jadi pertanyaan ke Kaprodi
                // yang jawabannya sudah tercatat di sistem.
                if ($status === Student::NONAKTIF) {
                    $alasan = $user->student->status_note;

                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'username' => 'Akun Anda sedang dinonaktifkan oleh Kaprodi'
                            . ($alasan ? ": {$alasan}" : '.')
                            . ' Hubungi program studi bila ingin mengaktifkannya kembali.',
                    ])->withInput($request->only('username'));
                }
            }

            // Cek akun pembimbing industri sudah diaktivasi
            if ($user->role === 'lecturer_industry' && !$user->is_activated) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'username' => 'Akun Anda belum diaktivasi. Cek email Anda untuk link aktivasi.',
                ])->withInput($request->only('username'));
            }

            $request->session()->regenerate();
            return $this->redirectByRole($user->role);
        }

        // Gagal → catat percobaan (kunci sementara setelah MAX_ATTEMPTS).
        RateLimiter::hit($key, self::DECAY_SECONDS);

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirectByRole(string $role)
    {
        return match($role) {
            'student'           => redirect()->route('mahasiswa.dashboard'),
            'lecturer'          => redirect()->route('dosen.dashboard'),
            'lecturer_industry' => redirect()->route('dosen-industri.dashboard'),
            'kaprodi'           => redirect()->route('kaprodi.dashboard'),
            default             => redirect('/'),
        };
    }
}
