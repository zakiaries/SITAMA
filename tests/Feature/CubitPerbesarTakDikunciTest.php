<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Tak ada halaman yang boleh mengunci cubit-perbesar.
 *
 * `user-scalable=no` / `maximum-scale=1.0` mematikan satu-satunya cara orang
 * memperbesar tulisan di ponsel. Yang dirugikan justru yang paling butuh:
 * audiens seminar yang matanya kurang awas tak bisa memastikan namanya benar
 * sebelum menekan Hadir, dan tak ada jalan lain karena ukuran teksnya tetap.
 *
 * Dulu halaman daftar hadir memang punya alasan memakainya — ada kanvas tanda
 * tangan di sana, dan gerakan mencubit beradu dengan gerakan menggores. Kanvas
 * itu sudah dibongkar saat kehadiran beralih ke akun yang login, tapi kuncinya
 * ikut tertinggal selama berbulan-bulan. Penjaga ini menyapu semua blade supaya
 * sisa serupa tak menumpuk lagi tanpa ada yang menyadarinya.
 */
class CubitPerbesarTakDikunciTest extends FeatureTestCase
{
    public function test_tak_ada_halaman_yang_mengunci_cubit_perbesar(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        $diperiksa = 0;

        foreach ($iterator as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            // Komentar blade dibuang supaya penjelasan yang justru MENYEBUT
            // aturan terlarang tak terbaca sebagai pelanggaran.
            $isi = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($file->getPathname()));

            if (! str_contains($isi, 'name="viewport"')) {
                continue;
            }

            $this->assertStringNotContainsString('user-scalable=no', $isi,
                $file->getFilename() . ': mengunci cubit-perbesar. Pengguna yang perlu '
                . 'memperbesar tulisan kehilangan satu-satunya caranya.');

            $this->assertStringNotContainsString('maximum-scale=1', $isi,
                $file->getFilename() . ': membatasi perbesaran maksimum, yang efeknya '
                . 'sama dengan mengunci cubit-perbesar.');

            $diperiksa++;
        }

        $this->assertGreaterThan(10, $diperiksa,
            'Penyapunya tak menemukan cukup halaman ber-viewport — polanya kemungkinan sudah tak cocok.');
    }

    /** Halaman daftar hadir tetap harus punya viewport-nya, bukan kehilangan barisnya. */
    public function test_halaman_daftar_hadir_tetap_menyesuaikan_lebar_layar(): void
    {
        $isi = file_get_contents(resource_path('views/public/berita-acara.blade.php'));

        $this->assertStringContainsString('width=device-width', $isi,
            'Tanpa width=device-width halaman dirender selebar desktop lalu dikecilkan — '
            . 'tulisannya jadi jauh lebih kecil daripada sebelum perubahan ini.');
    }
}
