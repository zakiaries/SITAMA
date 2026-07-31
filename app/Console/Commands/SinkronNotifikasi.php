<?php

namespace App\Console\Commands;

use App\Models\Guidance;
use App\Models\InternshipReport;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Console\Command;

/**
 * Menyelaraskan lonceng notifikasi dengan penanda "menunggu tanggapan" di sidebar.
 *
 * Keduanya membaca sumber berbeda: penanda sidebar dihitung langsung dari data
 * (logbook belum dikomentari, bimbingan pending, laporan pending), sedangkan
 * lonceng membaca tabel notifications yang hanya terisi saat ada KEJADIAN.
 * Akibatnya data yang lahir sebelum notifikasi dipasang — termasuk data yang
 * dibuat command simulasi lewat model langsung — tak punya notifikasi sama
 * sekali, sehingga lonceng kosong padahal penanda menyala.
 *
 * Perintah ini membuatkan notifikasi untuk hal-hal yang MASIH menunggu
 * tanggapan. Idempoten: notifikasi yang sudah ada tidak digandakan.
 */
class SinkronNotifikasi extends Command
{
    protected $signature = 'simama:sinkron-notifikasi
        {--pratinjau : Tampilkan jumlahnya saja, tanpa membuat notifikasi}';

    protected $description = 'Isi notifikasi untuk hal yang masih menunggu tanggapan (selaraskan lonceng dengan penanda sidebar)';

    public function handle(): int
    {
        $pratinjau = (bool) $this->option('pratinjau');
        $baris     = [];
        $total     = 0;

        // ── Logbook yang belum dikomentari ──
        $logbooks = LogBook::with(['student.user', 'student.internships'])->get();

        $lbDosen = $lbIndustri = 0;

        foreach ($logbooks as $lb) {
            $internship = $lb->student?->internships->sortByDesc('id')->first();

            if (! $internship) {
                continue;
            }

            $nama = $lb->student->user->name ?? 'Mahasiswa';

            if (! $lb->lecturer_note && $internship->lecturer) {
                $lbDosen += $this->buat(
                    $internship->lecturer->user_id,
                    "{$nama} mengisi log book: \"{$lb->title}\".",
                    'log_book',
                    $lb->activity,
                    $pratinjau,
                    "/dosen/mahasiswa/{$lb->student_id}#logbook-{$lb->id}"
                );
            }

            if (! $lb->industry_note && $internship->lecturerIndustry) {
                $lbIndustri += $this->buat(
                    $internship->lecturerIndustry->user_id,
                    "{$nama} mengisi log book: \"{$lb->title}\".",
                    'log_book',
                    $lb->activity,
                    $pratinjau,
                    "/dosen-industri/mahasiswa/{$lb->student_id}#logbook-{$lb->id}"
                );
            }
        }

        $baris[] = ['Logbook belum dikomentari dosen', $lbDosen];
        $baris[] = ['Logbook belum dikomentari industri', $lbIndustri];
        $total  += $lbDosen + $lbIndustri;

        // ── Bimbingan yang masih pending ──
        $bim = 0;

        foreach (Guidance::where('status', 'pending')->with(['student.user', 'student.lecturer', 'student.internships'])->get() as $g) {
            $lecturer = $g->student?->lecturer ?? $g->student?->internships->sortByDesc('id')->first()?->lecturer;
            $nama     = $g->student->user->name ?? 'Mahasiswa';

            $bim += $this->buat(
                $lecturer?->user_id,
                "{$nama} mengajukan bimbingan baru: \"{$g->title}\".",
                'bimbingan',
                $g->activity,
                $pratinjau,
                "/dosen/mahasiswa/{$g->student_id}#bimbingan-{$g->id}"
            );
        }

        $baris[] = ['Bimbingan menunggu ACC', $bim];
        $total  += $bim;

        // ── Laporan akhir yang masih pending ──
        $lap = 0;

        foreach (InternshipReport::where('status', 'pending')->with(['student.user', 'student.lecturer', 'student.internships'])->get() as $r) {
            $lecturer = $r->student?->lecturer ?? $r->student?->internships->sortByDesc('id')->first()?->lecturer;
            $nama     = $r->student->user->name ?? 'Mahasiswa';

            $lap += $this->buat(
                $lecturer?->user_id,
                "{$nama} mengunggah laporan akhir magang.",
                'laporan',
                'Laporan menunggu review dan persetujuan dosen pembimbing.',
                $pratinjau,
                "/dosen/mahasiswa/{$r->student_id}#laporan"
            );
        }

        $baris[] = ['Laporan menunggu review', $lap];
        $total  += $lap;

        $this->newLine();
        $this->table(['Jenis', $pratinjau ? 'Akan dibuat' : 'Dibuat'], $baris);
        $this->newLine();

        if ($pratinjau) {
            $this->info("Mode pratinjau — {$total} notifikasi akan dibuat. Jalankan tanpa --pratinjau untuk menerapkan.");

            return self::SUCCESS;
        }

        $this->info("Selesai. {$total} notifikasi dibuat.");
        $this->line('Notifikasi yang sudah ada tidak digandakan, jadi perintah ini aman diulang.');

        return self::SUCCESS;
    }

    /** Kunci yang sudah diproses di proses ini, agar pratinjau tak menghitung duplikat. */
    private array $sudahDiproses = [];

    /** @return int 1 bila dibuat/akan dibuat, 0 bila dilewati. */
    private function buat(?int $userId, string $message, string $category, ?string $detail, bool $pratinjau, ?string $link = null): int
    {
        if (! $userId) {
            return 0;
        }

        $kunci = $userId . '|' . $category . '|' . $message;

        if (isset($this->sudahDiproses[$kunci])) {
            return 0;
        }

        $this->sudahDiproses[$kunci] = true;

        $sudahAda = Notification::where('user_id', $userId)
            ->where('category', $category)
            ->where('message', $message)
            ->exists();

        if ($sudahAda) {
            return 0;
        }

        if (! $pratinjau) {
            Notification::kirim($userId, $message, $category, $detail, $link);
        }

        return 1;
    }
}
