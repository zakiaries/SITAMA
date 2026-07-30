<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Bug: foto profil hanya tampil di halaman profil sendiri. Di tempat user LAIN
 * melihat (portal dosen, portal industri, daftar kaprodi) selalu inisial, karena
 * setiap blade merender inisial manual dan tak pernah memanggil photoUrl().
 * Dijaga lewat komponen bersama <x-avatar>.
 */
class AvatarTest extends FeatureTestCase
{
    private const FOTO = 'photos/foto-uji.jpg';

    public function test_foto_mahasiswa_tampil_di_portal_dosen_dan_industri(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');
        $mhs->update(['photo_profile' => self::FOTO]);
        $studentId = $mhs->student->id;

        // Dosen pembimbing: daftar mahasiswa (dashboard) + detail mahasiswa.
        $dosen = $this->userByUsername('dosen1');
        $this->actingAs($dosen)->get('/dosen/dashboard')->assertOk()->assertSee(self::FOTO);
        $this->actingAs($dosen)->get("/dosen/mahasiswa/{$studentId}")->assertOk()->assertSee(self::FOTO);

        // Pembimbing industri: daftar + detail.
        $industri = $this->userByUsername('industri1');
        $this->actingAs($industri)->get('/dosen-industri/dashboard')->assertOk()->assertSee(self::FOTO);
        $this->actingAs($industri)->get("/dosen-industri/mahasiswa/{$studentId}")->assertOk()->assertSee(self::FOTO);

        // Kaprodi: daftar + detail mahasiswa. Tab default = 'pending', jadi
        // fixture yang berstatus active harus diminta lewat ?status=semua.
        $kaprodi = $this->userByUsername('kaprodi');
        $this->actingAs($kaprodi)->get('/kaprodi/mahasiswa?status=semua')->assertOk()->assertSee(self::FOTO);
        $this->actingAs($kaprodi)->get("/kaprodi/mahasiswa/{$studentId}")->assertOk()->assertSee(self::FOTO);
    }

    public function test_foto_dosen_tampil_di_sisi_mahasiswa_dan_kaprodi(): void
    {
        $dosen = $this->userByUsername('dosen1');
        $dosen->update(['photo_profile' => self::FOTO]);

        // Kartu "Dosen Pembimbing" di halaman bimbingan & laporan mahasiswa.
        $mhs = $this->userByUsername('3.34.23.2.01');
        $this->actingAs($mhs)->get('/mahasiswa/bimbingan')->assertOk()->assertSee(self::FOTO);
        $this->actingAs($mhs)->get('/mahasiswa/laporan')->assertOk()->assertSee(self::FOTO);

        // Daftar dosen di portal kaprodi.
        $kaprodi = $this->userByUsername('kaprodi');
        $this->actingAs($kaprodi)->get('/kaprodi/dosen')->assertOk()->assertSee(self::FOTO);
    }

    /** Tanpa foto, tetap inisial — bukan gambar rusak. */
    public function test_tanpa_foto_tampilkan_inisial(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');
        $this->assertNull($mhs->photo_profile);

        $dosen = $this->userByUsername('dosen1');
        $this->actingAs($dosen)->get('/dosen/dashboard')
            ->assertOk()
            ->assertDontSee('<img src="/storage/photos', false)
            ->assertSee('MS'); // inisial "Mahasiswa Satu"
    }

    /** Sidebar & topbar sendiri juga ikut menampilkan foto (dulu selalu inisial). */
    public function test_foto_sendiri_tampil_di_sidebar_topbar(): void
    {
        foreach ([['kaprodi', '/kaprodi/dashboard'], ['dosen1', '/dosen/dashboard'], ['industri1', '/dosen-industri/dashboard'], ['3.34.23.2.01', '/mahasiswa/dashboard']] as [$username, $url]) {
            $user = $this->userByUsername($username);
            $user->update(['photo_profile' => self::FOTO]);

            $html = $this->actingAs($user)->get($url)->assertOk()->getContent();

            // Minimal 2 kemunculan: sidebar + topbar (hero bila ada = lebih).
            $this->assertGreaterThanOrEqual(2, substr_count($html, self::FOTO),
                "Foto profil kurang dari 2 kali di {$url} (sidebar+topbar)");
        }
    }
}
