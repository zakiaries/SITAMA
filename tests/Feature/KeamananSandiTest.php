<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Panjang minimum kata sandi dulu tidak konsisten: pendaftaran mandiri dan
 * ganti sandi di profil menuntut 8 karakter, tapi akun yang DIBUATKAN Kaprodi
 * (dosen, pembimbing industri, reset sandi mahasiswa) serta aktivasi akun
 * industri masih menerima 6.
 *
 * Justru akun-akun itu yang sandinya dibagikan lewat kertas dan sering dipakai
 * apa adanya tanpa pernah diganti — jadi ambang terendahnya ada di tempat yang
 * paling lama bertahan.
 */
class KeamananSandiTest extends FeatureTestCase
{
    public function test_kaprodi_tak_bisa_membuat_akun_dosen_bersandi_pendek(): void
    {
        $this->actingAs($this->userByUsername('kaprodi'))
            ->post('/kaprodi/dosen', [
                'name'     => 'Dosen Baru',
                'username' => 'dosenbaru',
                'email'    => 'dosenbaru@test.ac.id',
                'password' => 'rahasia',   // 7 karakter
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_reset_sandi_mahasiswa_oleh_kaprodi_menuntut_delapan(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$mhs->student->id}/reset-password", [
                'new_password'              => 'rahasia',
                'new_password_confirmation' => 'rahasia',
            ])
            ->assertSessionHasErrors('new_password');
    }

    public function test_sandi_delapan_karakter_diterima(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$mhs->student->id}/reset-password", [
                'new_password'              => 'rahasia8',
                'new_password_confirmation' => 'rahasia8',
            ])
            ->assertSessionHasNoErrors();
    }

    /** Penjaga: jangan ada aturan min:6 yang tersisa atau muncul lagi. */
    public function test_tak_ada_lagi_aturan_sandi_enam_karakter(): void
    {
        $temuan = [];

        foreach ([app_path(), resource_path('views')] as $dasar) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dasar, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $isi = file_get_contents($file->getPathname());

                if (str_contains($isi, 'min:6') || str_contains($isi, 'minlength="6"')) {
                    $temuan[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        $this->assertSame([], $temuan,
            'Ambang sandi harus 8 karakter di semua jalur, termasuk akun yang dibuatkan Kaprodi.');
    }
}
