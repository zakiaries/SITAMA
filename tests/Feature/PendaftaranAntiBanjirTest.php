<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Registrasi adalah satu-satunya jalur publik yang membuat data, dan sebelumnya
 * sama sekali tanpa pembatasan — login dan reset kata sandi sudah dibatasi,
 * tapi registrasi terlewat.
 *
 * Akun spam sebenarnya tak berdaya: semuanya berstatus menunggu dan tak bisa
 * login sampai Kaprodi menyetujui. Yang merugikan adalah banjirnya — tab
 * "Menunggu" dan lonceng notifikasi Kaprodi jadi tak terpakai.
 *
 * Dipilih throttle + honeypot alih-alih CAPTCHA: menutup risiko yang sama tanpa
 * pihak ketiga, tanpa kunci API, tanpa melonggarkan CSP, dan tanpa gesekan bagi
 * pendaftar sungguhan.
 */
class PendaftaranAntiBanjirTest extends FeatureTestCase
{
    private function isian(string $nim, array $ganti = []): array
    {
        return array_merge([
            'name'                  => 'Calon ' . $nim,
            'username'              => $nim,
            'email'                 => str_replace('.', '', $nim) . '@test.ac.id',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'the_class'             => 'IK-3C',
            'study_program'         => 'Teknik Informatika',
            'major'                 => 'Teknik Elektro',
            'academic_year'         => '2023/2024',
        ], $ganti);
    }

    // ── Honeypot ────────────────────────────────────────────────────────────

    public function test_pendaftaran_ditolak_bila_perangkap_terisi(): void
    {
        $sebelum = User::count();

        $this->post('/register', $this->isian('3.34.23.2.71', [
            'catatan_tambahan' => 'http://spam.example.com',
        ]))->assertRedirect(route('login'));

        $this->assertSame($sebelum, User::count(), 'Akun tetap dibuat padahal perangkap terisi.');
        $this->assertNull(User::where('username', '3.34.23.2.71')->first());
    }

    /** Bot tak boleh tahu bahwa ia tertangkap — jawabannya seolah berhasil. */
    public function test_bot_tidak_diberi_tahu_bahwa_tertangkap(): void
    {
        $this->post('/register', $this->isian('3.34.23.2.72', [
            'catatan_tambahan' => 'diisi bot',
        ]))->assertSessionHasNoErrors()
          ->assertSessionHas('success');
    }

    /** Pendaftar sungguhan tak terpengaruh: field itu tak pernah ia isi. */
    public function test_pendaftaran_normal_tetap_berhasil(): void
    {
        $this->post('/register', $this->isian('3.34.23.2.73'))
            ->assertSessionHasNoErrors();

        $this->assertNotNull(User::where('username', '3.34.23.2.73')->first());
    }

    /** Field perangkap harus benar-benar ada di formulir dan tersembunyi. */
    public function test_perangkap_ada_di_formulir_dan_tersembunyi(): void
    {
        $html = $this->get('/register')->assertOk()->getContent();

        $this->assertStringContainsString('name="catatan_tambahan"', $html,
            'Perangkap tidak dirender, jadi bot tak akan pernah mengisinya.');
        $this->assertStringContainsString('aria-hidden="true"', $html,
            'Perangkap harus diabaikan pembaca layar.');
        $this->assertStringContainsString('tabindex="-1"', $html,
            'Perangkap harus dilewati saat navigasi keyboard.');
    }

    // ── Throttle ────────────────────────────────────────────────────────────

    public function test_pendaftaran_beruntun_dibatasi(): void
    {
        // Lima percobaan pertama diproses seperti biasa.
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/register', $this->isian("3.34.23.2.8{$i}"))
                ->assertRedirect(route('login'));
        }

        // Yang keenam ditolak pembatas laju.
        $this->post('/register', $this->isian('3.34.23.2.86'))
            ->assertStatus(429);

        $this->assertNull(User::where('username', '3.34.23.2.86')->first(),
            'Pendaftaran keenam tetap tersimpan padahal sudah melewati batas.');
    }

    /** Halaman formulirnya sendiri tidak ikut dibatasi. */
    public function test_membuka_halaman_daftar_tidak_dibatasi(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $this->get('/register')->assertOk();
        }
    }
}
