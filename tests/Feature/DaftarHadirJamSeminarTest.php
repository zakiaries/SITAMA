<?php

namespace Tests\Feature;

use App\Models\Seminar;
use Illuminate\Support\Carbon;
use Tests\FeatureTestCase;

/**
 * Daftar hadir seminar hanya terbuka pada hari-H, dan pada jamnya.
 *
 * Dulu terbuka sejak detik dosen menetapkan jadwal: audiens bisa mengisi daftar
 * hadir berminggu-minggu sebelum seminarnya berlangsung. Itu membuat syarat
 * minimal audiens kehilangan artinya — ia mestinya bukti orang benar-benar
 * datang, bukan bukti orang pernah membuka tautan.
 */
class DaftarHadirJamSeminarTest extends FeatureTestCase
{
    private function seminar(array $ganti = []): Seminar
    {
        $dosen = $this->userByUsername('dosen1')->lecturer;

        return Seminar::create(array_merge([
            'lecturer_id'  => $dosen->id,
            'title'        => 'Seminar Hasil Magang',
            'program'      => 'Teknik Informatika',
            'organizer'    => 'Dosen Satu',
            'status'       => 'scheduled',
            'date'         => today()->toDateString(),
            'time'         => '09.00 - 11.00 WIB',
            'access_token' => 'token-uji-' . uniqid(),
        ], $ganti));
    }

    public function test_jauh_hari_sebelum_seminar_tertutup(): void
    {
        $seminar = $this->seminar(['date' => today()->addDays(20)->toDateString()]);

        $this->assertFalse($seminar->daftarHadirTerbuka());
        $this->assertStringContainsString('baru dibuka pada hari seminar', $seminar->alasanHadirTertutup());
    }

    public function test_setelah_hari_seminar_tertutup(): void
    {
        $seminar = $this->seminar(['date' => today()->subDay()->toDateString()]);

        $this->assertFalse($seminar->daftarHadirTerbuka());
        $this->assertStringContainsString('sudah berlangsung', $seminar->alasanHadirTertutup());
    }

    public function test_pada_jam_seminar_terbuka(): void
    {
        Carbon::setTestNow(today()->setTime(10, 0));

        $this->assertTrue($this->seminar()->daftarHadirTerbuka());

        Carbon::setTestNow();
    }

    /** Datang 30 menit lebih awal masih dilayani. */
    public function test_toleransi_datang_lebih_awal(): void
    {
        Carbon::setTestNow(today()->setTime(8, 40));

        $this->assertTrue($this->seminar()->daftarHadirTerbuka());

        Carbon::setTestNow();
    }

    public function test_pagi_buta_di_hari_yang_sama_masih_tertutup(): void
    {
        Carbon::setTestNow(today()->setTime(6, 0));

        $seminar = $this->seminar();

        $this->assertFalse($seminar->daftarHadirTerbuka());
        $this->assertStringContainsString('jam seminar', $seminar->alasanHadirTertutup());

        Carbon::setTestNow();
    }

    /** Sesi molor satu jam masih dilayani; lewat itu ditutup. */
    public function test_toleransi_sesi_molor(): void
    {
        Carbon::setTestNow(today()->setTime(11, 45));
        $this->assertTrue($this->seminar()->daftarHadirTerbuka());

        Carbon::setTestNow(today()->setTime(13, 0));
        $this->assertFalse($this->seminar()->daftarHadirTerbuka());

        Carbon::setTestNow();
    }

    /**
     * Jam yang tak terbaca tak boleh mengunci siapa pun: kolomnya teks bebas,
     * dan dosen yang menulis "pagi" tak seharusnya membuat audiens gagal absen
     * di tengah seminar yang sedang berlangsung.
     */
    public function test_jam_tak_terbaca_digerbangi_hari_saja(): void
    {
        Carbon::setTestNow(today()->setTime(17, 0));

        $seminar = $this->seminar(['time' => 'pagi']);

        $this->assertNull($seminar->jendelaJam());
        $this->assertTrue($seminar->daftarHadirTerbuka());

        Carbon::setTestNow();
    }

    public function test_seminar_yang_belum_dijadwalkan_tertutup(): void
    {
        $seminar = $this->seminar(['status' => 'draft', 'date' => null]);

        $this->assertFalse($seminar->daftarHadirTerbuka());
    }

    /** Gerbangnya benar-benar berlaku di halaman daftar hadir, bukan cuma di model. */
    public function test_halaman_daftar_hadir_menolak_di_luar_hari_seminar(): void
    {
        $seminar = $this->seminar(['date' => today()->addDays(10)->toDateString()]);

        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get("/seminar/hadir/{$seminar->access_token}")
            ->assertOk()
            ->assertSee('baru dibuka pada hari seminar', false);
    }

    public function test_halaman_daftar_hadir_terbuka_saat_seminar_berlangsung(): void
    {
        Carbon::setTestNow(today()->setTime(10, 0));

        $seminar = $this->seminar();

        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get("/seminar/hadir/{$seminar->access_token}")
            ->assertOk()
            ->assertDontSee('Berita Acara Tidak Aktif');

        Carbon::setTestNow();
    }
}
