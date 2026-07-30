<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
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

        // Kegagalan validasi TIDAK dicatat Laravel (hanya redirect back), jadi
        // form yang "gagal senyap" mustahil didiagnosa di server. Catat sebagai
        // warning lalu lanjutkan penanganan default (return null).
        $this->renderable(function (ValidationException $e, $request) {
            Log::warning('Validasi gagal', [
                'url'    => $request->fullUrl(),
                'user'   => Auth::id(),
                'errors' => $e->errors(),
            ]);

            return null;
        });

        // Sesi/token kedaluwarsa (419): jangan tampilkan halaman 419 mentah.
        // Arahkan ke dashboard sesuai role bila masih login, atau ke login.
        $this->renderable(function (TokenMismatchException $e, $request) {
            // Unggahan yang melebihi post_max_size dibuang PHP SEBELUM sampai ke
            // aplikasi: $_POST kosong sehingga _token hilang dan gejalanya sama
            // dengan token kedaluwarsa. Beri pesan yang benar, bukan "sesi habis".
            if ($this->looksLikeOversizedUpload($request)) {
                Log::warning('Unggahan melebihi post_max_size', [
                    'url'            => $request->fullUrl(),
                    'user'           => Auth::id(),
                    'content_length' => $request->server('CONTENT_LENGTH'),
                    'post_max_size'  => ini_get('post_max_size'),
                ]);

                return back()->with('error', 'File yang diunggah terlalu besar sehingga ditolak server (batas '
                    . ini_get('upload_max_filesize') . ' per file). Perkecil file lalu coba lagi.');
            }

            if (Auth::check()) {
                return redirect()->to($this->dashboardFor(Auth::user()->role))
                    ->with('error', 'Sesi sempat kedaluwarsa, silakan coba lagi.');
            }

            return redirect()->route('login')
                ->withErrors(['username' => 'Sesi kedaluwarsa, silakan login kembali.']);
        });
    }

    /**
     * Ciri POST yang body-nya dibuang PHP karena melebihi post_max_size:
     * ada Content-Length besar, tapi $_POST dan $_FILES kosong.
     */
    private function looksLikeOversizedUpload(Request $request): bool
    {
        return $request->isMethod('POST')
            && (int) $request->server('CONTENT_LENGTH', 0) > 0
            && empty($request->post())
            && empty($_FILES);
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
