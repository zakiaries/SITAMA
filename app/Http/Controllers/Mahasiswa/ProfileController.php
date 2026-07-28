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
        ]);

        $user->update(['name' => $request->name, 'email' => $request->email]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $this->storeProfilePhoto($request, $user);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
