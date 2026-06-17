<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Sesi/token kedaluwarsa (419): jangan tampilkan halaman 419 mentah.
        // Arahkan ke dashboard sesuai role bila masih login, atau ke login.
        $this->renderable(function (TokenMismatchException $e, $request) {
            if (Auth::check()) {
                return redirect()->to($this->dashboardFor(Auth::user()->role))
                    ->with('error', 'Sesi sempat kedaluwarsa, silakan coba lagi.');
            }

            return redirect()->route('login')
                ->withErrors(['username' => 'Sesi kedaluwarsa, silakan login kembali.']);
        });
    }

    private function dashboardFor(?string $role): string
    {
        return match ($role) {
            'student'           => route('mahasiswa.dashboard'),
            'lecturer'          => route('dosen.dashboard'),
            'lecturer_industry' => route('dosen-industri.dashboard'),
            'kaprodi'           => route('kaprodi.dashboard'),
            default             => url('/'),
        };
    }
}
