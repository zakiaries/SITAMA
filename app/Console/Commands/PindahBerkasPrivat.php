<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Pindahkan dokumen mahasiswa dari disk `public` ke disk `local`.
 *
 * Sebelum BerkasController ada, semua unggahan disimpan di storage/app/public
 * yang di-symlink ke public/storage dan dilayani langsung oleh web server —
 * bisa diunduh siapa pun yang tahu URL-nya, tanpa login. Berkas BARU sudah
 * masuk ke disk privat; perintah ini memindahkan yang lanjur ada.
 *
 * Avatar sengaja ditinggal: dipakai di tag <img> lintas halaman dan tak
 * memuat data pribadi selain wajah yang memang diunggah untuk ditampilkan.
 *
 * Aman diulang: berkas yang sudah pindah dilewati.
 */
class PindahBerkasPrivat extends Command
{
    protected $signature = 'simama:pindah-berkas
        {--pratinjau : Tampilkan rencananya saja, tanpa memindahkan}';

    protected $description = 'Pindahkan dokumen (bimbingan, laporan, sertifikat, bukti magang) ke penyimpanan privat';

    /** Folder dokumen yang wajib privat. Avatar tidak termasuk. */
    private const FOLDER = ['guidances', 'reports', 'certificates', 'magang-proofs'];

    public function handle(): int
    {
        $pratinjau = (bool) $this->option('pratinjau');

        $publik = Storage::disk('public');
        $privat = Storage::disk('local');

        $baris  = [];
        $pindah = 0;
        $lewat  = 0;
        $gagal  = [];

        foreach (self::FOLDER as $folder) {
            $berkas = $publik->exists($folder) ? $publik->files($folder) : [];
            $n = $s = 0;

            foreach ($berkas as $path) {
                if ($privat->exists($path)) {
                    $s++;
                    $lewat++;
                    continue;
                }

                if ($pratinjau) {
                    $n++;
                    $pindah++;
                    continue;
                }

                // Salin dulu, hapus asal hanya bila salinan benar-benar jadi —
                // kegagalan di tengah jangan sampai menghilangkan dokumen.
                $isi = $publik->get($path);

                if ($isi === null || ! $privat->put($path, $isi)) {
                    $gagal[] = $path;
                    continue;
                }

                $publik->delete($path);
                $n++;
                $pindah++;
            }

            $baris[] = [$folder, count($berkas), $n, $s];
        }

        $this->newLine();
        $this->table(
            ['Folder', 'Ditemukan', $pratinjau ? 'Akan pindah' : 'Dipindah', 'Sudah privat'],
            $baris
        );
        $this->newLine();

        if ($gagal !== []) {
            $this->error('Gagal dipindah (dibiarkan di tempat asal):');
            foreach ($gagal as $g) {
                $this->line('  - ' . $g);
            }
            $this->newLine();
        }

        if ($pratinjau) {
            $this->info("Mode pratinjau — {$pindah} berkas akan dipindah. Jalankan tanpa --pratinjau untuk menerapkan.");

            return self::SUCCESS;
        }

        $this->info("Selesai. {$pindah} berkas dipindah, {$lewat} dilewati karena sudah privat.");
        $this->line('Aman diulang. Avatar sengaja tetap di penyimpanan publik.');

        return $gagal === [] ? self::SUCCESS : self::FAILURE;
    }
}
