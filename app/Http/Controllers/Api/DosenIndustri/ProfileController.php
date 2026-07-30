<?php

namespace App\Http\Controllers\Api\DosenIndustri;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Concerns\HandlesProfilePhoto;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends ApiController
{
    use HandlesProfilePhoto;

    public function index(Request $request)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer_industry');
        $user     = $request->user();

        $scope = fn () => Student::whereHas('internships', fn ($q) => $q->where('lecturer_industry_id', $lecturer->id));

        return response()->json([
            'user' => [
                'name'      => $user->name,
                'username'  => $user->username,
                'email'     => $user->email,
                'role'      => $user->role,
                'photo_url' => $user->photoUrl(),
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
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->update(['name' => $request->name, 'email' => $request->email]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json(['message' => 'Profil berhasil diperbarui.']);
    }

    public function photo(Request $request)
    {
        $this->currentLecturer($request, 'lecturer_industry');
        $user = $request->user();
        $this->storeProfilePhoto($request, $user, true);

        return response()->json([
            'message'   => 'Foto profil berhasil diperbarui.',
            'photo_url' => $user->fresh()->photoUrl(),
        ]);
    }
}
