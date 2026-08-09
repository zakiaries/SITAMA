<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Panel notifikasi memakai `right: 0`, yang menempelkan tepi kanannya pada
 * LONCENG — bukan pada tepi layar. Di kanan lonceng masih ada avatar 34px,
 * jarak 8px, dan padding topbar 14px: seluruhnya 56px.
 *
 * Panel selebar 340px karena itu mulai di `lebar_layar − 56 − 340`, yang
 * bernilai negatif pada layar mana pun di bawah 396px. Tepi kirinya terpotong
 * keluar layar sementara kanannya menyisakan 56px kosong, sehingga panelnya
 * terlihat terjeblos ke pojok. Hampir semua ponsel berada di bawah ambang itu.
 *
 * `max-width: calc(100vw - 32px)` yang sudah ada tidak menolong: ia membatasi
 * LEBAR, sedangkan yang keliru adalah titik tumpunya.
 */
class PanelNotifikasiTakTerpotongTest extends FeatureTestCase
{
    /** Peran => [username fixture, rute dashboard]. */
    private const PORTAL = [
        'mahasiswa'      => ['3.34.23.2.01', 'mahasiswa.dashboard'],
        'dosen'          => ['dosen1',       'dosen.dashboard'],
        'dosen-industri' => ['industri1',    'dosen-industri.dashboard'],
        'kaprodi'        => ['kaprodi',      'kaprodi.dashboard'],
    ];

    private function css(): string
    {
        $css = preg_replace('#/\*.*?\*/#s', '', file_get_contents(public_path('css/simama.css')));

        return preg_replace('/\s+/', '', $css);
    }

    public function test_panel_dipatok_ke_layar_di_ponsel(): void
    {
        $css = $this->css();

        $this->assertMatchesRegularExpression(
            '/@media\(max-width:520px\)\{\.notif-panel\{position:fixed;/',
            $css,
            'Panel notifikasi tak lagi dipatok ke layar di ponsel. Dengan position:absolute '
            . 'ia bertumpu pada lonceng, sehingga tepi kirinya terpotong keluar layar.'
        );
    }

    /**
     * Jarak kiri dan kanan harus SAMA. Inilah yang membuat letaknya tak lagi
     * bergantung pada lebar avatar maupun lencana peran di sebelah lonceng —
     * angka yang bisa berubah kapan saja tanpa ada yang teringat panel ini.
     */
    public function test_jarak_kiri_dan_kanan_sama(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('left:12px;right:12px;', $css,
            'Panel notifikasi harus berjarak sama di kiri dan kanan layar.');
    }

    /** Lebar tetap 340px wajib dilepas, kalau tidak ia tetap meluber. */
    public function test_lebar_tetap_dilepas(): void
    {
        $css = $this->css();

        $posMedia = strpos($css, '@media(max-width:520px){.notif-panel{');
        $this->assertNotFalse($posMedia, 'Blok @media panel notifikasi tak ditemukan.');

        $blok = substr($css, $posMedia, 220);

        $this->assertStringContainsString('width:auto;', $blok,
            'Lebar 340px harus dilepas menjadi auto, kalau tidak left/right diabaikan.');
        $this->assertStringContainsString('max-width:none;', $blok,
            'max-width lama harus dilepas agar panel mengikuti lebar layar.');
    }

    /** Keempat portal masih benar-benar merender panelnya. */
    public function test_tiap_portal_masih_punya_panel_notifikasi(): void
    {
        foreach (self::PORTAL as $peran => [$username, $rute]) {
            $html = $this->actingAs($this->userByUsername($username))
                ->get(route($rute))
                ->assertOk()
                ->getContent();

            $this->assertStringContainsString('class="notif-panel"', $html,
                "Portal {$peran} kehilangan panel notifikasinya.");
        }
    }
}
