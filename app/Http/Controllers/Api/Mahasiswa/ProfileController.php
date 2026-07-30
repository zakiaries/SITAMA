<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Concerns\HandlesProfilePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends ApiController
{
    use HandlesProfilePhoto;

    public function index(Request $request)
    {
        $student = $this->currentStudent($request);
        $user    = $request->user();
        $internship = $student->activeInternship()->with('company')->first();

        return response()->json([
            'user' => [
                'name'      => $user->name,
                'username'  => $user->username,
                'email'     => $user->email,
                'role'      => $user->role,
                'photo_url' => $user->photoUrl(),
            ],
            'student' => [
                'the_class'     => $student->the_class,
                'study_program' => $student->study_program,
                'major'         => $student->major,
                'academic_year' => $student->academic_year,
            ],
            'internship' => $internship ? [
                'company'    => $internship->company->name ?? null,
                'position'   => $internship->position,
                'start_date' => optional($internship->start_date)->toDateString(),
                'end_date'   => optional($internship->end_date)->toDateString(),
            ] : null,
        ]);
    }

    public function update(Request $request)
    {
        $this->currentStudent($request);
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
        $this->currentStudent($request);
        $user = $request->user();
        $this->storeProfilePhoto($request, $user, true);

        return response()->json([
            'message'   => 'Foto profil berhasil diperbarui.',
            'photo_url' => $user->fresh()->photoUrl(),
        ]);
    }
}
