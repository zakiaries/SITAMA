<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\InvitationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ActivationController extends Controller
{
    public function show(string $token)
    {
        $invitation = InvitationToken::with('user')->where('token', $token)->first();

        if (!$invitation || !$invitation->isValid()) {
            return view('auth.activate', ['expired' => true, 'token' => $token]);
        }

        return view('auth.activate', [
            'expired'    => false,
            'token'      => $token,
            'invitation' => $invitation,
        ]);
    }

    public function activate(Request $request, string $token)
    {
        $invitation = InvitationToken::with('user')->where('token', $token)->first();

        if (!$invitation || !$invitation->isValid()) {
            return redirect()->route('login')->with('error', 'Link aktivasi tidak valid atau sudah kedaluwarsa.');
        }

        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan, pilih yang lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $user = $invitation->user;
        $user->update([
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'is_activated' => true,
        ]);

        $invitation->update(['used_at' => now()]);

        return redirect()->route('login')
            ->with('success', 'Akun berhasil diaktivasi! Silakan login dengan username dan password yang baru Anda buat.');
    }
}
