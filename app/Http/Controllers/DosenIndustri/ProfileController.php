<?php

namespace App\Http\Controllers\DosenIndustri;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $lecturer = $user->lecturer;

        if (!$lecturer) abort(403, 'Akses ditolak.');

        $totalMahasiswa = Student::whereHas('internships', fn($q) => $q->where('lecturer_industry_id', $lecturer->id))->count();
        $aktif          = Student::whereHas('internships', fn($q) => $q->where('lecturer_industry_id', $lecturer->id)->where('is_finished', false))->count();
        $selesai        = Student::whereHas('internships', fn($q) => $q->where('lecturer_industry_id', $lecturer->id)->where('is_finished', true))->count();

        return view('dosen-industri.profile.index', compact('user', 'lecturer', 'totalMahasiswa', 'aktif', 'selesai'));
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

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
