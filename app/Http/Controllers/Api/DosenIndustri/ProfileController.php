<?php

namespace App\Http\Controllers\Api\DosenIndustri;

use App\Http\Controllers\Api\ApiController;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends ApiController
{
    public function index(Request $request)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer_industry');
        $user     = $request->user();

        $scope = fn () => Student::whereHas('internships', fn ($q) => $q->where('lecturer_industry_id', $lecturer->id));

        return response()->json([
            'user' => [
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
            ],
            'stats' => [
                'total_mahasiswa' => $scope()->count(),
                'aktif'           => $scope()->whereHas('internships', fn ($q) => $q->where('lecturer_industry_id', $lecturer->id)->where('is_finished', false))->count(),
                'selesai'         => $scope()->whereHas('internships', fn ($q) => $q->where('lecturer_industry_id', $lecturer->id)->where('is_finished', true))->count(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $this->currentLecturer($request, 'lecturer_industry');
        $user = $request->user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update(['name' => $request->name, 'email' => $request->email]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json(['message' => 'Profil berhasil diperbarui.']);
    }
}
