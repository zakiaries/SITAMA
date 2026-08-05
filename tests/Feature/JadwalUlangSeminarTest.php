<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

/**
 * Dosen memindahkan jadwal sesi seminar.
 *
 * Tanggal dulu dikunci sekali tetapkan, dengan alasan mengganti tanggal
 * membatalkan kesiapan penyaji. Justru di situ jebakannya: kalau dosen
 * berhalangan — sakit, tugas luar, ruang dipakai — seminarnya tak berlangsung
 * dan sesinya terkunci selamanya di tanggal yang telanjur lewat. Satu-satunya
 * jalan keluar adalah membatalkan sesi lalu membuat ulang dari nol, yang
 * membuang ketersediaan tanggal yang sudah diisi para penyaji.
 */
class JadwalUlangSeminarTest extends FeatureTestCase
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
            'date'         => today()->addDays(7)->toDateString(),
            'time'         => '09.00 - 11.00 WIB',
            'location'     => 'Ruang TI-01',
            'min_guests' => 15,
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

    public function test_dosen_memindahkan_tanggal_sesi_terjadwal(): void
    {
        $seminar = $this->sesi();
        $baru    = today()->addDays(14)->toDateString();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'     => $baru,
            'time'     => '13.00 - 15.00 WIB',
            'location' => 'Ruang TI-02',
            'min_guests' => 15,
        ])->assertRedirect();

        $seminar->refresh();

        $this->assertSame($baru, $seminar->date->toDateString());
        $this->assertSame('Ruang TI-02', $seminar->location);
    }

    /** Kasus yang memicu fitur ini: dosen berhalangan, tanggalnya telanjur lewat. */
    public function test_sesi_yang_tanggalnya_sudah_lewat_bisa_dijadwalkan_ulang(): void
    {
        $seminar = $this->sesi(['date' => today()->subDays(3)->toDateString()]);
        $baru    = today()->addDays(5)->toDateString();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'     => $baru,
            'time'     => '09.00 - 11.00 WIB',
            'location' => 'Ruang TI-01',
            'min_guests' => 15,
        ])->assertRedirect();

        $this->assertSame($baru, $seminar->fresh()->date->toDateString());
    }

    public function test_penyaji_diberi_tahu_beserta_jadwal_lamanya(): void
    {
        $seminar   = $this->sesi();
        $mahasiswa = $this->userByUsername('3.34.23.2.01');
        $lama      = $seminar->date->format('d M Y');

        Notification::where('user_id', $mahasiswa->id)->delete();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'     => today()->addDays(21)->toDateString(),
            'time'     => '09.00 - 11.00 WIB',
            'location' => 'Ruang TI-01',
            'min_guests' => 15,
        ]);

        $notif = Notification::where('user_id', $mahasiswa->id)->latest('id')->first();

        $this->assertNotNull($notif, 'Penyaji harus diberi tahu saat jadwalnya bergeser.');
        $this->assertStringContainsString('DIUBAH', $notif->message);
        $this->assertStringContainsString($lama, (string) $notif->detail_text,
            'Jadwal lama harus disebut, supaya penyaji tahu apa yang berubah.');
    }

    /** Tanpa perubahan tanggal, pesannya tetap "ditetapkan" — bukan "diubah". */
    public function test_mengubah_jam_saja_tidak_disebut_perubahan_jadwal(): void
    {
        $seminar   = $this->sesi();
        $mahasiswa = $this->userByUsername('3.34.23.2.01');

        Notification::where('user_id', $mahasiswa->id)->delete();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'     => $seminar->date->toDateString(),
            'time'     => '13.00 - 15.00 WIB',
            'location' => 'Ruang TI-01',
            'min_guests' => 15,
        ]);

        $notif = Notification::where('user_id', $mahasiswa->id)->latest('id')->first();

        $this->assertStringNotContainsString('DIUBAH', (string) $notif?->message);
    }

    public function test_tak_bisa_dipindah_ke_tanggal_yang_sudah_lewat(): void
    {
        $seminar = $this->sesi();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'     => today()->subDay()->toDateString(),
            'time'     => '09.00 - 11.00 WIB',
            'location' => 'Ruang TI-01',
            'min_guests' => 15,
        ])->assertSessionHasErrors('date');
    }

    public function test_sesi_yang_sudah_disahkan_tak_bisa_dijadwalkan_ulang(): void
    {
        $seminar = $this->sesi(['status' => 'completed']);
        $semula  = $seminar->date->toDateString();

        $this->dosen()->post(route('dosen.seminar.finalize', $seminar), [
            'date'     => today()->addDays(30)->toDateString(),
            'time'     => '09.00 - 11.00 WIB',
            'location' => 'Ruang TI-01',
            'min_guests' => 15,
        ])->assertSessionHas('error');

        $this->assertSame($semula, $seminar->fresh()->date->toDateString());
    }

    public function test_dosen_lain_tak_bisa_memindahkan(): void
    {
        $seminar = $this->sesi();
        $semula  = $seminar->date->toDateString();

        // industri1 punya baris lecturer sendiri, tapi bukan pemilik sesi ini.
        $this->actingAs($this->userByUsername('industri1'))
            ->post(route('dosen.seminar.finalize', $seminar), [
                'date'     => today()->addDays(30)->toDateString(),
                'location' => 'Ruang Lain',
                'min_guests' => 15,
            ]);

        $this->assertSame($semula, $seminar->fresh()->date->toDateString());
    }

    /** Tombol Sahkan abu-abu selama audiens belum cukup, biru setelahnya. */
    public function test_tombol_sahkan_mati_saat_audiens_belum_cukup(): void
    {
        $this->sesi(['date' => today()->toDateString()]);

        $this->dosen()->get(route('dosen.seminar.index'))
            ->assertOk()
            ->assertSee('belum bisa disahkan', false);
    }
}
