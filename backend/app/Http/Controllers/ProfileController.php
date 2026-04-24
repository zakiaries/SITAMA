<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo_profile' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();

        if ($user->photo_profile) {
            Storage::disk('public')->delete($user->photo_profile);
        }

        $path = $request->file('photo_profile')->store('profiles', 'public');
        $user->update(['photo_profile' => $path]);

        return response()->json([
            'message'       => 'Foto profil berhasil diperbarui',
            'photo_profile' => asset('storage/' . $path),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['message' => 'Password lama tidak sesuai'],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password berhasil diperbarui']);
    }
}
