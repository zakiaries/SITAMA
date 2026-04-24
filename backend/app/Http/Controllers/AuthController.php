<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => ['message' => 'Username atau password salah'],
            ], 401);
        }

        $roleMap = [
            'student'           => 'Student',
            'lecturer'          => 'Lecturer',
            'lecturer_industry' => 'Lecturer Industry',
            'kaprodi'           => 'Kaprodi',
            'industri'          => 'Industri',
        ];

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'role'  => $roleMap[$user->role],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
