<?php

namespace App\Console\Commands;

use App\Models\Guidance;
use App\Models\LogBook;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Console\Command;

/**
 * Mengisi kolom `link` pada notifikasi yang terlanjur dibuat tanpa tujuan.
 *
 * Kolom `link` baru ditambahkan belakangan, jadi semua notifikasi yang lahir
 * sebelum itu bernilai null. Route notifikasi.open memperlakukan link kosong
 * dengan memulangkan pengguna ke halaman notifikasi — sehingga notifikasi lama
 * terasa "diklik tapi cuma refresh".
 *
 * simama:sinkron-notifikasi TIDAK menolong: perintah itu melewati notifikasi
 * yang sudah ada (anti-duplikat), jadi hanya MEMBUAT yang hilang dan tak pernah
 * MENGISI baris lama.
 *
 * Tujuan ditentukan dari peran penerima + kategori. Bila item persisnya masih
 * bisa ditemukan (judul logbook/bimbingan tercantum di pesan), tujuannya
 * dipertajam sampai anchor item tersebut; kalau tidak, cukup ke halamannya.
 * Mendarat di halaman yang benar sudah jauh lebih baik daripada memantul balik.
 */
class IsiLinkNotifikasi extends Command
{
    protected $signature = 'simama:isi-link-notifikasi
        {--pratinjau : Tampilkan rencananya saja, tanpa menyimpan}';

    protected $description = 'Isi tujuan (link) notifikasi lama yang terlanjur dibuat tanpa link';

    /** Cache nama mahasiswa -> Student, diurutkan nama terpanjang dulu. */
    private ?array $mahasiswa = null;

    public function handle(): int
    {
        $pratinjau = (bool) $this->option('pratinjau');

        $notifications = Notification::whereNull('link')->with('user')->get();

        if ($notifications->isEmpty()) {
            $this->info('Tidak ada notifikasi tanpa tujuan. Semua sudah bisa diklik.');

            return self::SUCCESS;
        }

        $terisi = 0;
        $gagal  = 0;
        $contoh = [];

        foreach ($notifications as $n) {
            $link = $this->tujuan($n);

            if (! $link) {
                $gagal++;
                continue;
            }

            if (count($contoh) < 10) {
                $contoh[] = [
                    $n->user?->role ?? '?',
                    $n->category,
                    mb_strimwidth($n->message, 0, 42, '…'),
                    $link,
                ];
            }

            if (! $pratinjau) {
                $n->update(['link' => $link]);
            }

            $terisi++;
        }

        $this->newLine();
        $this->table(['Peran', 'Kategori', 'Pesan', 'Tujuan'], $contoh);
        $this->newLine();

        $this->line("Notifikasi tanpa tujuan : {$notifications->count()}");
        $this->line(($pratinjau ? 'Akan diisi' : 'Berhasil diisi') . "        : {$terisi}");

        if ($gagal > 0) {
            $this->warn("Tidak dikenali        : {$gagal} (dibiarkan, tetap jatuh ke halaman notifikasi)");
        }

        $this->newLine();

        if ($pratinjau) {
            $this->info('Mode pratinjau — tidak ada yang disimpan. Jalankan tanpa --pratinjau untuk menerapkan.');

            return self::SUCCESS;
        }

        $this->info('Selesai. Perintah ini hanya menyentuh baris yang link-nya masih kosong, jadi aman diulang.');

        return self::SUCCESS;
    }

    /** Tentukan tujuan dari peran penerima + kategori notifikasi. */
    private function tujuan(Notification $n): ?string
    {
        return match ($n->user?->role) {
            'student'           => $this->tujuanMahasiswa($n),
            'lecturer'          => $this->tujuanDosen($n),
            'lecturer_industry' => $this->tujuanIndustri($n),
            'kaprodi'           => $this->tujuanKaprodi($n),
            default             => null,
        };
    }

    private function tujuanMahasiswa(Notification $n): ?string
    {
        return match ($n->category) {
            'bimbingan' => '/mahasiswa/bimbingan',
            'laporan'   => '/mahasiswa/laporan',
            'log_book'  => '/mahasiswa/logbook',
            'seminar'   => '/mahasiswa/seminar',
            // Pengajuan yang ditolak dibuka lagi lewat form pengajuan, bukan
            // halaman magang yang belum tentu terisi.
            'pengajuan_magang' => str_contains(mb_strtolower($n->message), 'ditolak')
                ? '/mahasiswa/ajukan-magang'
                : '/mahasiswa/magang-saya',
            'selesai_magang'   => '/mahasiswa/magang-saya',
            default            => null,
        };
    }

    private function tujuanDosen(Notification $n): ?string
    {
        if ($n->category === 'seminar') {
            return '/dosen/seminar';
        }

        if (! in_array($n->category, ['log_book', 'bimbingan', 'laporan'], true)) {
            return null;
        }

        $student = $this->mahasiswaDariPesan($n->message);

        if (! $student) {
            return '/dosen/dashboard';
        }

        $anchor = match ($n->category) {
            'log_book'  => $this->anchorItem(LogBook::class, $student->id, $n->message, 'logbook'),
            'bimbingan' => $this->anchorItem(Guidance::class, $student->id, $n->message, 'bimbingan'),
            'laporan'   => '#laporan',
            default     => '',
        };

        return "/dosen/mahasiswa/{$student->id}{$anchor}";
    }

    private function tujuanIndustri(Notification $n): ?string
    {
        if ($n->category !== 'log_book') {
            return '/dosen-industri/dashboard';
        }

        $student = $this->mahasiswaDariPesan($n->message);

        if (! $student) {
            return '/dosen-industri/dashboard';
        }

        $anchor = $this->anchorItem(LogBook::class, $student->id, $n->message, 'logbook');

        return "/dosen-industri/mahasiswa/{$student->id}{$anchor}";
    }

    private function tujuanKaprodi(Notification $n): ?string
    {
        if ($n->category === 'pengajuan_magang') {
            return '/kaprodi/pengajuan-magang';
        }

        if ($n->category !== 'selesai_magang') {
            return null;
        }

        // "Pengajuan selesai magang dari {nama}" — ACC-nya ada di detail mahasiswa.
        $student = $this->mahasiswaDariPesan($n->message, sesudahDari: true);

        return $student ? "/kaprodi/mahasiswa/{$student->id}" : '/kaprodi/mahasiswa';
    }

    /**
     * Pesan ke pembimbing selalu diawali nama mahasiswa ("Budi mengisi log
     * book: ..."), sedangkan pesan ke kaprodi memakai pola "... dari Budi".
     * Nama terpanjang dicocokkan lebih dulu supaya "Budi" tidak menang atas
     * "Budi Santoso".
     */
    private function mahasiswaDariPesan(string $message, bool $sesudahDari = false): ?Student
    {
        if ($this->mahasiswa === null) {
            $this->mahasiswa = Student::with('user')->get()
                ->filter(fn ($s) => filled($s->user?->name))
                ->sortByDesc(fn ($s) => mb_strlen($s->user->name))
                ->all();
        }

        $jarum = $message;

        if ($sesudahDari) {
            $pos = mb_strripos($message, ' dari ');
            if ($pos === false) {
                return null;
            }
            $jarum = trim(mb_substr($message, $pos + 6));
        }

        foreach ($this->mahasiswa as $s) {
            if (str_starts_with($jarum, $s->user->name)) {
                return $s;
            }
        }

        return null;
    }

    /**
     * Judul item ada di dalam tanda kutip pada pesannya. Kalau ketemu tepat satu
     * milik mahasiswa itu, tujuannya bisa dipertajam ke anchor item tersebut.
     */
    private function anchorItem(string $model, int $studentId, string $message, string $prefix): string
    {
        if (! preg_match('/"([^"]+)"/u', $message, $cocok)) {
            return '';
        }

        $items = $model::where('student_id', $studentId)->where('title', $cocok[1])->pluck('id');

        return $items->count() === 1 ? "#{$prefix}-{$items->first()}" : '';
    }
}
