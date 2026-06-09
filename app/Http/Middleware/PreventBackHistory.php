<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventBackHistory
{
    /**
     * Cegah browser menyajikan halaman dari cache/bfcache.
     *
     * Tanpa ini, setelah logout halaman login bisa diambil dari cache dengan
     * token CSRF lama sehingga login berikutnya gagal dengan error 419,
     * dan halaman ber-otentikasi masih bisa dilihat lewat tombol "back".
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 01 Jan 2014 00:00:00 GMT');

        return $response;
    }
}
