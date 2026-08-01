<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Field password memakai tombol mata sendiri (.pw-eye) supaya perilakunya sama
 * di semua browser. Masalahnya Edge menyisipkan tombol mata bawaannya sendiri
 * (::-ms-reveal) ke dalam field, jadi penggunanya melihat DUA ikon mata; Chrome
 * tidak punya kontrol itu sehingga bugnya tak terlihat saat dikembangkan.
 *
 * Halaman login & register sudah lama mematikannya di CSS mereka sendiri. Yang
 * terlewat justru komponen bersama .pw-wrap yang datang belakangan — dipakai
 * reset password, aktivasi akun, dan halaman profil semua peran.
 */
class IkonMataPasswordTest extends FeatureTestCase
{
    private function css(): string
    {
        return file_get_contents(public_path('css/simama.css'));
    }

    public function test_kontrol_bawaan_edge_dimatikan_untuk_komponen_bersama(): void
    {
        $css = preg_replace('/\s+/', '', $this->css());

        $this->assertStringContainsString('.pw-wrapinput::-ms-reveal', $css,
            'Tombol mata bawaan Edge harus dimatikan, kalau tidak ikon mata tampil dobel.');
        $this->assertStringContainsString('.pw-wrapinput::-ms-clear', $css,
            'Tombol "x" bawaan Edge harus ikut dimatikan agar tidak menabrak ikon mata.');
    }

    /** Halaman publik lama punya CSS sendiri — jangan sampai ikut hilang. */
    public function test_halaman_login_dan_register_tetap_mematikannya(): void
    {
        foreach (['login', 'register'] as $halaman) {
            $blade = preg_replace('/\s+/', '',
                file_get_contents(resource_path("views/auth/{$halaman}.blade.php")));

            $this->assertStringContainsString('::-ms-reveal', $blade,
                "Halaman {$halaman} kehilangan penonaktif tombol mata bawaan Edge.");
        }
    }

    /**
     * Penyebab "dua mata" yang lain: komponen kita sendiri terpasang dobel dalam
     * satu .pw-wrap. Tiap pembungkus harus punya tepat satu tombol.
     */
    public function test_tiap_pw_wrap_punya_tepat_satu_tombol_mata(): void
    {
        $berkas = glob(resource_path('views/**/*.blade.php'), GLOB_BRACE)
            + glob(resource_path('views/**/**/*.blade.php'));

        $diperiksa = 0;

        foreach (array_unique($berkas) as $path) {
            if (str_ends_with($path, 'password-toggle.blade.php')) {
                continue; // dokumentasi pemakaian di komentar komponennya sendiri
            }

            $isi     = file_get_contents($path);
            $wrap    = substr_count($isi, 'class="pw-wrap"');
            $tombol  = substr_count($isi, '<x-password-toggle');

            if ($wrap === 0 && $tombol === 0) {
                continue;
            }

            $this->assertSame($wrap, $tombol, sprintf(
                '%s: %d .pw-wrap tapi %d tombol mata — jumlahnya harus sama.',
                basename($path), $wrap, $tombol
            ));

            $diperiksa++;
        }

        $this->assertGreaterThan(0, $diperiksa, 'Tidak ada berkas yang terperiksa.');
    }
}
