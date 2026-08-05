<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesProfilePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use HandlesProfilePhoto;

    public function index()
    {
        $user       = Auth::user();
        $student    = $user->student;
        $internship = $student ? $student->activeInternship()->with('company')->first() : null;

        return view('mahasiswa.profile.index', compact('user', 'student', 'internship'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            // Pesan bawaan Laravel berbahasa Inggris, sedangkan seluruh
            // aplikasi berbahasa Indonesia.
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->update(['name' => $request->name, 'email' => $request->email]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $this->storeProfilePhoto($request, $user);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
