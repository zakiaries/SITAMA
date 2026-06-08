<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
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

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->remember)) {
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
            }

            $request->session()->regenerate();
            return $this->redirectByRole($user->role);
        }

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
            'industri'          => redirect()->route('industri.dashboard'),
            default             => redirect('/'),
        };
    }
}
