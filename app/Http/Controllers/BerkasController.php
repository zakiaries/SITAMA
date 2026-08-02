<?php

namespace App\Http\Controllers;

use App\Models\CompanyRequest;
use App\Models\Guidance;
use App\Models\Internship;
use App\Models\InternshipReport;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Satu-satunya pintu untuk mengunduh dokumen mahasiswa.
 *
 * Sebelumnya semua unggahan disimpan di disk `public` yang di-symlink ke
 * public/storage dan dilayani langsung oleh web server — Laravel tak pernah
 * dilibatkan, jadi tak ada pemeriksaan hak akses sama sekali. Nama berkasnya
 * memang acak 40 karakter sehingga tak bisa ditebak satu per satu, tapi begitu
 * satu URL bocor (dibagikan, riwayat browser, header Referer, log proxy)
 * berkas itu bisa diunduh siapa pun selamanya — termasuk setelah mahasiswanya
 * lulus atau akunnya dihapus.
 *
 * Sekarang dokumen disimpan di disk `local` (di luar public/) dan hanya bisa
 * diambil lewat kelas ini, yang memeriksa siapa peminta dan apa haknya.
 *
 * Dua cara masuk yang sah:
 *   1. Sesi login web / token API — hak akses diperiksa per peran.
 *   2. URL bertanda tangan berbatas waktu — dipakai aplikasi HP, yang membuka
 *      berkas lewat browser luar sehingga tak membawa token. Pola yang sama
 *      sudah dipakai PDF berita acara seminar (routes/api.php).
 */
class BerkasController extends Controller
{
    /** Masa berlaku URL bertanda tangan untuk aplikasi HP. */
    private const MENIT_TANDA_TANGAN = 30;

    public function bimbingan(Request $request, Guidance $guidance)
    {
        $this->pastikanBoleh($request, $guidance->student);

        return $this->kirim($guidance->name_file, 'bimbingan');
    }

    public function laporan(Request $request, InternshipReport $report)
    {
        $this->pastikanBoleh($request, $report->student);

        return $this->kirim($report->file_path, 'laporan-akhir');
    }

    public function sertifikat(Request $request, Internship $internship)
    {
        $this->pastikanBoleh($request, $internship->student);

        return $this->kirim($internship->certificate_path, 'sertifikat-magang');
    }

    public function bukti(Request $request, CompanyRequest $magangRequest)
    {
        $this->pastikanBoleh($request, $magangRequest->student);

        return $this->kirim($magangRequest->proof_file, 'bukti-penerimaan');
    }

    // ── Pembantu ────────────────────────────────────────────────────────────

    /**
     * URL bertanda tangan berbatas waktu — untuk payload API saja.
     *
     * Hanya dibuat setelah pemintanya lolos pemeriksaan hak akses di controller
     * masing-masing, jadi tanda tangan ini memperpanjang izin yang memang sudah
     * dimiliki, bukan memberi izin baru.
     */
    public static function tautanBertandaTangan(string $rute, $model): ?string
    {
        return $model === null ? null : URL::temporarySignedRoute(
            $rute,
            now()->addMinutes(self::MENIT_TANDA_TANGAN),
            [$model]
        );
    }

    /**
     * URL bertanda tangan hanya sah untuk waktu terbatas; di luar itu wajib
     * login. Tanpa keduanya, tolak.
     */
    private function pastikanBoleh(Request $request, ?Student $student): void
    {
        abort_if($student === null, 404);

        if ($request->hasValidSignature()) {
            return;
        }

        abort_unless(Auth::check(), 403, 'Silakan masuk lebih dulu untuk membuka berkas ini.');
        abort_unless($this->berhakAtas($student), 403, 'Berkas ini bukan milik Anda.');
    }

    /**
     * Siapa yang berhak melihat dokumen seorang mahasiswa.
     *
     * Dosen memakai aturan yang sama dengan daftar bimbingannya
     * (Student::dibimbingOleh) supaya tak ada selisih antara "terlihat di
     * daftar" dan "boleh dibuka berkasnya".
     */
    private function berhakAtas(Student $student): bool
    {
        $user = Auth::user();

        return match ($user->role) {
            'kaprodi' => true,
            'student' => $user->student?->id === $student->id,
            'lecturer' => $user->lecturer !== null
                && Student::whereKey($student->getKey())->dibimbingOleh($user->lecturer->id)->exists(),
            'lecturer_industry' => $user->lecturer !== null
                && $student->internships()
                    ->where('lecturer_industry_id', $user->lecturer->id)->exists(),
            default => false,
        };
    }

    /** Tampilkan berkas dari disk privat; nama unduhannya dibuat mudah dikenali. */
    private function kirim(?string $path, string $label)
    {
        abort_if($path === null || $path === '', 404, 'Berkas tidak ditemukan.');

        $disk = Storage::disk($this->diskBerkas($path));

        abort_unless($disk->exists($path), 404, 'Berkas tidak ditemukan.');

        return response()->file($disk->path($path), [
            'Content-Disposition' => 'inline; filename="'
                . $label . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"',
        ]);
    }

    /**
     * Berkas lama masih berada di disk `public` sampai simama:pindah-berkas
     * dijalankan. Selama masa peralihan keduanya dilayani, supaya memutakhirkan
     * kode tak membuat dokumen lama mendadak hilang.
     */
    private function diskBerkas(string $path): string
    {
        return Storage::disk('local')->exists($path) ? 'local' : 'public';
    }
}
