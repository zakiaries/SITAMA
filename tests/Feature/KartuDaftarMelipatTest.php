<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Kartu pendaftar di Data Mahasiswa memuat empat hal yang tiga di antaranya
 * menolak menyusut: avatar 46px, lencana status ~94px, dan tombol Setujui +
 * Tolak ~178px. Bersama jarak antar-item dan padding kartu, totalnya ±392px —
 * melewati lebar layar 375px sebelum nama mahasiswa kebagian sepiksel pun.
 *
 * CATATAN untuk yang menambah kartu serupa nanti: tes ini sengaja TIDAK
 * menyapu semua baris flex. Apakah sebuah baris meluber hanya bisa dihitung
 * dari lebar anak-anaknya saat dirender, dan banyak baris memang BENAR bila
 * tak melipat — kotak isian chatbot, misalnya, harus tetap sebaris dengan
 * tombol kirimnya. Penjaga yang menuntut semua baris melipat akan menyuruh
 * orang merusak yang sudah betul. Yang dijaga di sini adalah kartu yang sudah
 * terbukti bermasalah, beserta alasan angkanya.
 */
class KartuDaftarMelipatTest extends FeatureTestCase
{
    private function sumberKartu(): string
    {
        return preg_replace('/\s+/', '',
            file_get_contents(resource_path('views/kaprodi/mahasiswa/index.blade.php')));
    }

    public function test_kartu_pendaftar_melipat_di_layar_sempit(): void
    {
        $blade = $this->sumberKartu();

        $this->assertStringContainsString('.mhs-card{flex-wrap:wrap;}', $blade,
            'Kartu mahasiswa tak boleh dipaksa sebaris di layar sempit — tombol '
            . 'Setujui & Tolak menolak menyusut, jadi barisnya meluber keluar layar.');
    }

    /**
     * Bagian ini yang paling mudah hilang saat orang "merapikan" CSS, padahal
     * tanpanya flex-wrap sama sekali tak berefek.
     */
    public function test_kolom_nama_punya_lebar_minimum(): void
    {
        $blade = $this->sumberKartu();

        $this->assertStringContainsString('.mhs-info{min-width:150px;}', $blade,
            '.mhs-info memakai flex:1 (flex-basis:0), jadi ia tak pernah menuntut ruang '
            . 'dan hanya mengalah sampai nol — barisnya tetap meluber meski sudah '
            . 'flex-wrap. Lebar minimum inilah yang mendorong tombolnya turun ke baris kedua.');
    }

    /** Perbaikannya harus tetap di dalam @media, supaya desktop tak bergeser. */
    public function test_perbaikannya_tak_menyentuh_desktop(): void
    {
        $blade = $this->sumberKartu();

        $posMedia = strpos($blade, '@media(max-width:760px){.mhs-card{flex-wrap:wrap;}');

        $this->assertNotFalse($posMedia,
            'flex-wrap harus berada DI DALAM @media (max-width:760px). Di luar itu ia '
            . 'ikut berlaku di desktop, tempat kartunya justru sudah benar sebaris.');
    }

    /** Kartunya memang masih terender untuk Kaprodi. */
    public function test_halaman_data_mahasiswa_terbuka(): void
    {
        $html = $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.mahasiswa.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('mhs-card', $html,
            'Halaman Data Mahasiswa tak lagi memakai kartu mhs-card — tes ini perlu disesuaikan.');
    }
}
