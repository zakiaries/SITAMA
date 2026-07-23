<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Batasi akses rute berdasarkan peran (role) pengguna.
 *
 * Dipakai per-grup rute, mis. ->middleware('role:kaprodi'). Bisa lebih dari
 * satu peran: 'role:lecturer,kaprodi'. Pengguna yang tidak sesuai ditolak 403.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
