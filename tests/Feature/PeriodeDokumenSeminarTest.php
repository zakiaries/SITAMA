<?php

namespace Tests\Feature;

use App\Models\Period;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

/**
 * Berita acara menyebut PERIODE MAGANG-nya, bukan periode saat seminar digelar.
 *
 * Dulu semester & tahun akademik dihitung dari tanggal seminar, dengan logika
 * kalender yang disalin ulang di dalam Blade. Itu keliru: seminar berlangsung
 * sesudah magang dan kadang jatuh di semester berikutnya, sehingga mahasiswa
 * yang magang pada Genap 2025/2026 lalu seminarnya Agustus 2026 menerima
 * dokumen bertuliskan "Gasal 2026/2027" — periode yang bukan miliknya, di
 * berkas yang ia bawa untuk ditandatangani.
 */
class PeriodeDokumenSeminarTest extends FeatureTestCase
{
    private function periode(string $tahun, string $semester): Period
    {
        [$mulai, $selesai] = Period::jendela($tahun, $semester);

        return Period::create([
            'academic_year' => $tahun,
            'semester'      => $semester,
            'start_date'    => $mulai,
            'end_date'      => $selesai,
        ]);
    }

    private function sesi(?Period $periode, string $tanggalSeminar): Seminar
    {
        $dosen     = $this->userByUsername('dosen1')->lecturer;
        $mahasiswa = $this->userByUsername('3.34.23.2.01')->student;

        $mahasiswa->update(['period_id' => $periode?->id]);

        $seminar = Seminar::create([
            'lecturer_id'  => $dosen->id,
            'title'        => 'Seminar Hasil Magang',
            'program'      => 'Teknik Informatika',
            'period_id'    => $periode?->id,
            'organizer'    => 'Dosen Satu',
            'status'       => 'completed',
            'date'         => $tanggalSeminar,
            'time'         => '09.00 - 11.00 WIB',
            'location'     => 'Ruang TI-01',
            'witnessed_at' => now(),
            'access_token' => Str::random(48),
        ]);

        SeminarPresenter::create(['seminar_id' => $seminar->id, 'student_id' => $mahasiswa->id]);

        return $seminar;
    }

    /** Inti bugnya: magang Genap, seminar Agustus (sudah Gasal berikutnya). */
    public function test_dokumen_memakai_periode_magang_bukan_tanggal_seminar(): void
    {
        $genap   = $this->periode('2025/2026', 'genap');
        $seminar = $this->sesi($genap, '2026-08-20');

        $dokumen = $seminar->periodeDokumen();

        $this->assertSame('Genap', $dokumen['semester']);
        $this->assertSame('2025/2026', $dokumen['tahun_akademik'],
            'Tanggal seminar jatuh di Gasal 2026/2027, tapi magangnya Genap 2025/2026.');
    }

    public function test_pdf_mencetak_periode_magangnya(): void
    {
        $genap   = $this->periode('2025/2026', 'genap');
        $seminar = $this->sesi($genap, '2026-08-20');

        // Blade-nya dirender langsung: isi PDF dari dompdf sudah termampatkan
        // sehingga teksnya tak bisa dicari, sedangkan yang ingin dibuktikan
        // justru kalimat yang tercetak di kepala dokumen.
        $html = view('mahasiswa.seminar.berita-acara-pdf', [
            'seminar' => $seminar->load('presenters.student.user'),
        ])->render();

        $this->assertStringContainsString('Semester Genap Tahun Akademik 2025/2026', $html);
        $this->assertStringNotContainsString('2026/2027', $html);

        // Rutenya sendiri tetap harus melayani mahasiswanya.
        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get(route('mahasiswa.seminar.berita-acara', $seminar))
            ->assertOk();
    }

    /** Sesi lama tanpa periode jatuh kembali ke tanggal seminar, bukan kosong. */
    public function test_tanpa_periode_jatuh_ke_tanggal_seminar(): void
    {
        $seminar = $this->sesi(null, '2026-08-20');

        $dokumen = $seminar->periodeDokumen();

        $this->assertSame('Gasal', $dokumen['semester']);
        $this->assertSame('2026/2027', $dokumen['tahun_akademik']);
    }

    /** Sesi baru menyimpan periode penyajinya sejak dibuat. */
    public function test_sesi_baru_mewarisi_periode_penyajinya(): void
    {
        $gasal     = $this->periode('2026/2027', 'gasal');
        $mahasiswa = $this->userByUsername('3.34.23.2.01')->student;
        $mahasiswa->update(['period_id' => $gasal->id]);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.seminar.store'), [
                'title'       => 'Seminar Hasil Magang',
                'student_ids' => [$mahasiswa->id],
            ])->assertRedirect();

        $seminar = Seminar::latest('id')->first();

        $this->assertSame($gasal->id, $seminar->period_id);
    }
}
