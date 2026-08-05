<?php

namespace Tests\Feature;

use App\Models\Seminar;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

/**
 * Sesi seminar yang tanggalnya sudah lewat harus TERKUNCI bagi dosen.
 *
 * Sebelumnya dosen masih bisa menggeser jam, ruang, judul, dan deskripsi kapan
 * pun selama sesi belum disahkan. Padahal setelah hari-H seminarnya sudah
 * berlangsung: mengubahnya membuat catatan tak cocok dengan yang benar-benar
 * terjadi, dan penyaji ikut menerima notifikasi perubahan yang menyesatkan.
 * Yang tersisa setelah hari-H hanyalah pengesahan.
 */
class SeminarTerkunciTest extends FeatureTestCase
{
    private function dosen(): User
    {
        return $this->userByUsername('dosen1');
    }

    private function seminar(string $tanggal): Seminar
    {
        return Seminar::create([
            'lecturer_id'  => $this->dosen()->lecturer->id,
            'title'        => 'Seminar Hasil Magang',
            'program'      => 'TI',
            'status'       => 'scheduled',
            'date'         => $tanggal,
            'time'         => '09.00 - 11.00 WIB',
            'location'     => 'Ruang Seminar TI-01',
            'access_token' => Str::random(48),
        ]);
    }

    /**
     * Jam & ruang sesi yang tanggalnya lewat tak bisa digeser sendirian.
     *
     * Sejak dosen boleh menjadwalkan ulang sesi yang batal, satu-satunya cara
     * menyentuh sesi lewat adalah MEMINDAHKANNYA ke tanggal baru. Menggeser jam
     * saja, sambil membiarkan tanggalnya tetap di masa lalu, tetap ditolak —
     * itu menyunting catatan seminar yang sudah berlangsung, bukan menjadwalkan
     * ulang yang batal.
     */
    public function test_jam_dan_ruang_tak_bisa_digeser_tanpa_memindahkan_tanggal(): void
    {
        $s = $this->seminar(now()->subDay()->toDateString());

        $this->from('/dosen/seminar')->actingAs($this->dosen())
            ->post("/dosen/seminar/{$s->id}/finalize", [
                'time'     => '13.00 - 15.00 WIB',
                'location' => 'Ruang Lain',
            ])
            ->assertSessionHasErrors('date');

        $s->refresh();
        $this->assertSame('09.00 - 11.00 WIB', $s->time);
        $this->assertSame('Ruang Seminar TI-01', $s->location);
    }

    /** Tanggal lama tak bisa dipertahankan: yang lewat harus benar-benar pindah. */
    public function test_tanggal_lewat_tak_bisa_dipertahankan(): void
    {
        $s = $this->seminar(now()->subDay()->toDateString());

        $this->from('/dosen/seminar')->actingAs($this->dosen())
            ->post("/dosen/seminar/{$s->id}/finalize", [
                'date'     => $s->date->toDateString(),
                'time'     => '13.00 - 15.00 WIB',
                'location' => 'Ruang Lain',
            ])
            ->assertSessionHasErrors('date');
    }

    public function test_detail_tak_bisa_diubah_setelah_tanggal_lewat(): void
    {
        $s = $this->seminar(now()->subDays(3)->toDateString());

        $this->from('/dosen/seminar')->actingAs($this->dosen())
            ->put("/dosen/seminar/{$s->id}", ['title' => 'Judul Baru', 'description' => 'Ubahan'])
            ->assertSessionHas('error');

        $this->assertSame('Seminar Hasil Magang', $s->fresh()->title);
    }

    public function test_masih_bisa_diubah_pada_hari_H_dan_sebelumnya(): void
    {
        $hariIni = $this->seminar(now()->toDateString());

        $this->from('/dosen/seminar')->actingAs($this->dosen())
            ->post("/dosen/seminar/{$hariIni->id}/finalize", [
                'date'     => $hariIni->date->toDateString(),
                'time'     => '13.00 - 15.00 WIB',
                'location' => 'Ruang Seminar TI-02',
            ])
            ->assertSessionHas('success');

        $this->assertSame('Ruang Seminar TI-02', $hariIni->fresh()->location);

        $besok = $this->seminar(now()->addDay()->toDateString());

        $this->from('/dosen/seminar')->actingAs($this->dosen())
            ->put("/dosen/seminar/{$besok->id}", ['title' => 'Judul Baru', 'description' => null])
            ->assertSessionHas('success');

        $this->assertSame('Judul Baru', $besok->fresh()->title);
    }

    /** Form ubah hilang dari halaman, bukan cuma ditolak saat disubmit. */
    public function test_halaman_menyembunyikan_form_ubah_untuk_sesi_yang_lewat(): void
    {
        $s = $this->seminar(now()->subDay()->toDateString());

        $this->actingAs($this->dosen())->get('/dosen/seminar')
            ->assertOk()
            ->assertSee('Terkunci (tanggal sudah lewat)')
            ->assertDontSee('Ubah jam / lokasi')
            // Tombol "Ubah Detail" tak boleh terpasang untuk sesi ini (judul modalnya
            // sendiri selalu ada di markup, jadi yang dicek pemicunya).
            ->assertDontSee("openEditSeminar({$s->id},", false);
    }

    /** QR daftar hadir ikut ditutup: absensi tak masuk akal setelah seminarnya usai. */
    public function test_qr_daftar_hadir_ditutup_setelah_tanggal_lewat(): void
    {
        $s = $this->seminar(now()->subDay()->toDateString());

        $this->from('/dosen/seminar')->actingAs($this->dosen())
            ->get("/dosen/seminar/{$s->id}/qr")
            ->assertRedirect('/dosen/seminar');
        $this->assertNotNull(session('error'));

        $this->actingAs($this->dosen())->get('/dosen/seminar')
            ->assertSee('Daftar hadir ditutup')
            ->assertDontSee('Tampilkan QR Daftar Hadir');
    }

    /** Tautan hadir yang sudah terlanjur dibuka pun tak bisa dipakai lagi. */
    public function test_audiens_tak_bisa_absen_lewat_tautan_lama(): void
    {
        $s = $this->seminar(now()->subDay()->toDateString());
        $mhs = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($mhs)->get("/seminar/hadir/{$s->access_token}")
            ->assertOk()
            ->assertSee('Berita Acara Tidak Aktif');

        $this->actingAs($mhs)->post("/seminar/hadir/{$s->access_token}", [
            'rt' => $s->rotatingToken(),
        ])->assertOk()->assertSee('Berita Acara Tidak Aktif');

        $this->assertSame(0, $s->attendances()->count());

        // Pintu mobile-nya juga tertutup.
        $token = $mhs->createToken('uji')->plainTextToken;
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mahasiswa/seminar/attend', [
                'token' => $s->access_token, 'rt' => $s->rotatingToken(),
            ])->assertStatus(422);

        $this->assertSame(0, $s->attendances()->count());
    }

    /** API mobile memakai endpoint yang sama — gerbangnya harus ikut tertutup. */
    public function test_api_menolak_ubah_sesi_yang_tanggalnya_lewat(): void
    {
        $s = $this->seminar(now()->subDay()->toDateString());

        $token = $this->dosen()->createToken('uji')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/dosen/seminar/{$s->id}/finalize", [
                'time' => '13.00', 'location' => 'Ruang Lain',
            ])->assertStatus(422);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/dosen/seminar/{$s->id}", ['title' => 'Judul Baru'])
            ->assertStatus(422);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/dosen/seminar/{$s->id}/qr")
            ->assertStatus(422);

        $s->refresh();
        $this->assertSame('Ruang Seminar TI-01', $s->location);
        $this->assertSame('Seminar Hasil Magang', $s->title);
    }
}
