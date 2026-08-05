<?php

namespace Tests\Feature;

use App\Models\Seminar;
use App\Models\SeminarAttendance;
use App\Models\SeminarPresenter;
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

/**
 * Jumlah audiens minimal ditentukan per sesi, bukan tetap 15 untuk semua.
 *
 * Diminta partner: tiap dosen punya pertimbangan berbeda. Sesi dengan satu
 * penyaji tak menuntut audiens sebanyak sesi dengan enam penyaji.
 *
 * Isiannya menyatu dengan form jadwal — bukan layar terpisah — sehingga ia
 * ikut terlindungi aturan jadwal: form itu menuntut tanggal hari ini atau
 * setelahnya, jadi angka sesi yang tertinggal di masa lalu tak bisa diutak-atik
 * tanpa sekalian memindahkannya.
 */
class JumlahAudiensSeminarTest extends FeatureTestCase
{
    private function sesi(array $ganti = []): Seminar
    {
        $dosen = $this->userByUsername('dosen1')->lecturer;

        $seminar = Seminar::create(array_merge([
            'lecturer_id'  => $dosen->id,
            'title'        => 'Seminar Hasil Magang',
            'program'      => 'Teknik Informatika',
            'organizer'    => 'Dosen Satu',
            'status'       => 'scheduled',
            'date'         => today()->toDateString(),
            'time'         => '09.00 - 11.00 WIB',
            'location'     => 'Ruang TI-01',
            'access_token' => Str::random(48),
        ], $ganti));

        SeminarPresenter::create([
            'seminar_id' => $seminar->id,
            'student_id' => $this->userByUsername('3.34.23.2.01')->student->id,
        ]);

        return $seminar;
    }

    private function dosen()
    {
        return $this->actingAs($this->userByUsername('dosen1'));
    }

    private function hadir(Seminar $seminar, int $jumlah): void
    {
        foreach (range(1, $jumlah) as $i) {
            SeminarAttendance::create([
                'seminar_id' => $seminar->id,
                'name'       => "Audiens {$i}",
                'nim'        => "3.34.23.9.{$i}",
            ]);
        }
    }

    /** Sesi lama tanpa kolom terisi tetap memakai angka yang berlaku dulu. */
    public function test_bawaannya_lima_belas(): void
    {
        $this->assertSame(15, $this->sesi()->minGuests());
        $this->assertSame(15, Seminar::MIN_GUESTS);
    }

    public function test_dosen_menentukan_jumlahnya_bersama_jadwal(): void
    {
        $seminar = $this->sesi();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'       => today()->addDays(3)->toDateString(),
            'time'       => '09.00 - 11.00 WIB',
            'location'   => 'Ruang TI-01',
            'min_guests' => 5,
        ])->assertRedirect();

        $this->assertSame(5, $seminar->fresh()->minGuests());
    }

    /** Gerbang pengesahan mengikuti angka sesi itu, bukan tetapan lama. */
    public function test_pengesahan_mengikuti_angka_sesi(): void
    {
        $seminar = $this->sesi(['min_guests' => 3]);
        $this->hadir($seminar, 3);

        $this->assertTrue($seminar->fresh()->guestMet(),
            'Tiga audiens sudah cukup untuk sesi yang mensyaratkan tiga.');

        $this->dosen()->post(route('dosen.seminar.sahkan', $seminar))->assertRedirect();

        $this->assertSame('completed', $seminar->fresh()->status);
    }

    public function test_kurang_dari_angka_sesi_ditolak(): void
    {
        $seminar = $this->sesi(['min_guests' => 8]);
        $this->hadir($seminar, 7);

        $this->dosen()->post(route('dosen.seminar.sahkan', $seminar))
            ->assertSessionHas('error');

        $this->assertSame('scheduled', $seminar->fresh()->status);
    }

    /**
     * Nol berarti sesi bisa disahkan tanpa seorang pun hadir, dan syarat
     * audiens kehilangan seluruh gunanya — padahal daftar hadir itulah bukti
     * seminarnya benar berlangsung.
     */
    public function test_nol_ditolak(): void
    {
        $seminar = $this->sesi();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'       => today()->toDateString(),
            'location'   => 'Ruang TI-01',
            'min_guests' => 0,
        ])->assertSessionHasErrors('min_guests');
    }

    public function test_jumlah_wajib_diisi(): void
    {
        $seminar = $this->sesi();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'     => today()->toDateString(),
            'location' => 'Ruang TI-01',
        ])->assertSessionHasErrors('min_guests');
    }

    public function test_angka_sesi_tampil_di_halaman_dosen(): void
    {
        $this->sesi(['min_guests' => 7]);

        $this->dosen()->get(route('dosen.seminar.index'))
            ->assertOk()
            ->assertSee('0/7');
    }

    /** Mahasiswa penyaji melihat angka sesinya sendiri, bukan 15. */
    public function test_angka_sesi_tampil_untuk_penyaji(): void
    {
        $this->sesi(['min_guests' => 4]);

        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get(route('mahasiswa.seminar'))
            ->assertOk()
            ->assertSee('0/4');
    }

    public function test_api_mengirim_angka_sesi(): void
    {
        $seminar = $this->sesi(['min_guests' => 6]);

        $this->actingAs($this->userByUsername('dosen1'), 'sanctum')
            ->getJson('/api/dosen/seminar')
            ->assertOk()
            ->assertJsonPath('sessions.0.min_guests', 6);
    }
}
