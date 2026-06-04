<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckStudentApproval
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->role === 'student') {
            $student = $user->student;

            if (!$student || $student->status === 'pending') {
                return redirect()->route('mahasiswa.menunggu');
            }

            if ($student->status === 'rejected') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->with('error', 'Pendaftaran Anda ditolak. Silakan hubungi Kaprodi untuk informasi lebih lanjut.');
            }
        }

        return $next($request);
    }
}
