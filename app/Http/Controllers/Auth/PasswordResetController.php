<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /** Halaman "Lupa Password" — mahasiswa/pengguna memasukkan NIM/username. */
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Cari akun berdasarkan NIM/username, lalu kirim link reset ke EMAIL terdaftar.
     * Selalu tampilkan pesan generik (tidak membocorkan apakah NIM ada/tidak).
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
        ], [
            'username.required' => 'NIM / username wajib diisi.',
        ]);

        $generic = 'Jika NIM/username terdaftar dan memiliki email, link reset password telah dikirim ke email tersebut. Cek kotak masuk (dan folder spam).';

        $user = User::where('username', $request->username)->first();

        if (! $user || ! $user->email) {
            // Tidak membocorkan keberadaan akun.
            return back()->with('status', $generic);
        }

        // Password broker: buat token (tersimpan di password_reset_tokens) + kirim email.
        Password::sendResetLink(['email' => $user->email]);

        return back()->with('status', $generic);
    }

    /** Halaman form password baru, dibuka dari link di email (membawa token + email). */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Proses password baru: validasi token via broker, lalu simpan. */
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
        }

        return back()->withErrors(['email' => 'Link reset tidak valid atau sudah kedaluwarsa. Silakan minta link baru.']);
    }
}
