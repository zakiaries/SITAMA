<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Penolakan validasi harus TERLIHAT di halaman tempat formulirnya berada.
 *
 * Bermula dari laporan "konfirmasi sandi berbeda tetap diterima" di halaman
 * Profil: servernya memang menolak, tapi halamannya tak menampilkan apa pun
 * sehingga tampak diterima. Pindaian menemukan pola yang sama di sepuluh
 * halaman lain — isi nilai, catatan dosen, tolak pengajuan, sesi seminar.
 *
 * Pesannya diambil apa adanya dari server lalu dituntut muncul saat halamannya
 * dibuka lagi, jadi tes ini tak ikut pecah kalau kalimatnya diperbaiki kelak.
 */
class PesanGalatTampilTest extends FeatureTestCase
{
    public function test_penolakan_di_data_mahasiswa_kaprodi_terbaca(): void
    {
        $kaprodi   = $this->userByUsername('kaprodi');
        $mahasiswa = $this->userByUsername('3.34.23.2.01')->student;

        // Menonaktifkan tanpa alasan — ditolak validasi.
        $this->actingAs($kaprodi)
            ->post(route('kaprodi.mahasiswa.status', $mahasiswa), [])
            ->assertSessionHasErrors('status_note');

        $pesan = session('errors')->first('status_note');

        $this->actingAs($kaprodi)->get(route('kaprodi.mahasiswa.index'))
            ->assertOk()
            ->assertSee($pesan);
    }

    /**
     * Halaman-halaman yang dulu diam kini memuat komponen pesannya.
     *
     * Diperiksa dari sumbernya, bukan dari layar: tanpa galat komponen itu
     * memang tak menampilkan apa pun, jadi memuat halamannya takkan
     * membuktikan komponennya terpasang.
     */
    public function test_semua_halaman_berformulir_memasang_komponen_pesan(): void
    {
        $wajib = [
            'dosen/mahasiswa/nilai',
            'dosen/mahasiswa/detail',
            'dosen/seminar/index',
            'dosen/profile/index',
            'dosen-industri/mahasiswa/penilaian',
            'dosen-industri/mahasiswa/detail',
            'dosen-industri/profile/index',
            'kaprodi/mahasiswa/index',
            'kaprodi/pengajuan-magang/index',
            'kaprodi/lowongan/index',
            'kaprodi/chatbot/index',
            'kaprodi/profile/index',
            'mahasiswa/profile/index',
        ];

        foreach ($wajib as $view) {
            $this->assertStringContainsString(
                'x-form-errors',
                file_get_contents(resource_path("views/{$view}.blade.php")),
                "Halaman {$view} akan menolak isian tanpa memberi tahu penggunanya."
            );
        }
    }
}
