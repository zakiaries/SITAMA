<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:50|unique:users,username',
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

        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan dari Kaprodi.');
    }
}
