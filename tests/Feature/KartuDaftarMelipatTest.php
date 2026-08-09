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
        return $this->sumber('kaprodi/mahasiswa/index');
    }

    private function sumber(string $halaman): string
    {
        return preg_replace('/\s+/', '',
            file_get_contents(resource_path("views/{$halaman}.blade.php")));
    }

    /**
     * Kartu mahasiswa bimbingan di kedua dasbor.
     *
     * Yang dosen memuat EMPAT anak dan tiga di antaranya menolak menyusut:
     * avatar 46px, dua angka statistik ±110px, dan lencana status ±80px. Di
     * layar 375px sisanya untuk nama, NIM, tiga keterangan, dan penanda tugas
     * tinggal ±65px, sehingga semuanya terpecah satu kata per baris.
     *
     * Kartu ini sempat saya nilai aman saat menyapu Lapis 4 — penyapuannya
     * berhenti sebelum mencapai .student-stats dan lencananya, sehingga
     * terbaca hanya punya avatar dan satu blok yang bisa menyusut. Karena itu
     * di sini yang diperiksa sumbernya, bukan kesimpulan penyapuan.
     */
    public function test_kartu_dasbor_dosen_dan_industri_melipat(): void
    {
        foreach (['dosen/dashboard/index', 'dosen-industri/dashboard/index'] as $halaman) {
            $blade = $this->sumber($halaman);

            $this->assertStringContainsString('.student-card{flex-wrap:wrap;}', $blade,
                "{$halaman}: kartu mahasiswa tak melipat di layar sempit.");

            $this->assertStringContainsString('.student-info{min-width:150px;}', $blade,
                "{$halaman}: .student-info memakai flex:1 (flex-basis:0), jadi ia tak pernah "
                . 'menuntut ruang dan hanya mengalah sampai nol. Tanpa lebar minimum, '
                . 'flex-wrap sama sekali tak berefek.');

            $this->assertStringContainsString('@media(max-width:760px){.student-card{flex-wrap:wrap;}', $blade,
                "{$halaman}: perbaikannya harus di dalam @media, supaya desktop tak bergeser.");
        }
    }

    /** Kedua dasbor memang masih merender kartunya. */
    public function test_kedua_dasbor_masih_memakai_kartu_itu(): void
    {
        foreach ([
            'dosen'          => ['dosen1',    'dosen.dashboard'],
            'dosen-industri' => ['industri1', 'dosen-industri.dashboard'],
        ] as $peran => [$username, $rute]) {
            $html = $this->actingAs($this->userByUsername($username))
                ->get(route($rute))
                ->assertOk()
                ->getContent();

            $this->assertStringContainsString('student-card', $html,
                "Dasbor {$peran} tak lagi memakai student-card — tes ini perlu disesuaikan.");
        }
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
