<?php

namespace Tests\Feature;

use App\Models\AssessmentComponent;
use App\Models\Period;
use App\Models\StudentScore;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Lembar nilai magang untuk dicetak dan ditandatangani.
 *
 * Diminta partner: mahasiswa perlu membawa nilai dari dosen pembimbing DAN
 * pembimbing industri dalam bentuk kertas, untuk dimintakan tanda tangan basah.
 * Sebelumnya nilai hanya bisa dilihat di layar.
 */
class LembarNilaiPdfTest extends FeatureTestCase
{
    private function mahasiswa(): User
    {
        return $this->userByUsername('3.34.23.2.01');
    }

    /** Isi nilai lengkap dari kedua penilai, skala 1–10. */
    private function nilaiLengkap(int $internshipId, int $skorDosen = 8, int $skorIndustri = 9): void
    {
        foreach (['lecturer' => $skorDosen, 'lecturer_industry' => $skorIndustri] as $penilai => $skor) {
            AssessmentComponent::forScorer($penilai)->with('detailedComponents')->get()
                ->flatMap->detailedComponents
                ->each(fn ($detail) => StudentScore::updateOrCreate([
                    'internship_id'                    => $internshipId,
                    'detailed_assessment_component_id' => $detail->id,
                    'scorer_type'                      => $penilai,
                ], ['score' => $skor]));
        }
    }

    public function test_belum_lengkap_tak_bisa_diunduh(): void
    {
        $this->actingAs($this->mahasiswa())
            ->get(route('mahasiswa.nilai.pdf'))
            ->assertRedirect();

        $this->assertNotNull(session('error'));
    }

    public function test_tombol_unduh_tersembunyi_sebelum_nilai_lengkap(): void
    {
        $this->actingAs($this->mahasiswa())
            ->get(route('mahasiswa.nilai'))
            ->assertOk()
            ->assertDontSee('Unduh Lembar Nilai (PDF)');
    }

    public function test_tombol_unduh_muncul_setelah_kedua_penilai_selesai(): void
    {
        $mahasiswa  = $this->mahasiswa();
        $internship = $mahasiswa->student->activeInternship()->first();
        $this->nilaiLengkap($internship->id);

        $this->actingAs($mahasiswa)
            ->get(route('mahasiswa.nilai'))
            ->assertOk()
            ->assertSee('Unduh Lembar Nilai (PDF)');
    }

    public function test_lembar_memuat_kedua_penilai_dan_nilai_akhir(): void
    {
        $mahasiswa  = $this->mahasiswa();
        $internship = $mahasiswa->student->activeInternship()->first();
        $this->nilaiLengkap($internship->id, 8, 9);

        $internship->refresh()->load(['company', 'lecturer.user', 'lecturerIndustry.user', 'student.user', 'student.period']);
        $nilai = $internship->nilaiSummary();

        // Blade dirender langsung: isi PDF dari dompdf termampatkan sehingga
        // teksnya tak bisa dicari, sedangkan yang perlu dibuktikan justru
        // angka-angka yang tercetak.
        $html = view('mahasiswa.nilai.pdf', [
            'internship' => $internship,
            'nilai'      => $nilai,
            'dosen'      => $internship->rincianNilai('lecturer'),
            'industri'   => $internship->rincianNilai('lecturer_industry'),
        ])->render();

        $this->assertStringContainsString('Penilaian Dosen Pembimbing', $html);
        $this->assertStringContainsString('Penilaian Pembimbing Industri', $html);

        // Rata dosen 8 + rata industri 9 = 17.
        $this->assertSame(17.0, $nilai['final']);
        $this->assertStringContainsString('17', $html);

        // Butir penilaian ikut tercetak, bukan cuma rata-ratanya.
        $this->assertStringContainsString('Sistematika penulisan', $html);
        $this->assertStringContainsString('Kedisiplinan', $html);

        // Ruang tanda tangan kedua penilai.
        $this->assertStringContainsString('Dosen Pembimbing,', $html);
        $this->assertStringContainsString('Pembimbing Industri,', $html);
    }

    /** Periode diambil dari periode magang, bukan tanggal dokumen dicetak. */
    public function test_lembar_menyebut_periode_magangnya(): void
    {
        $mahasiswa  = $this->mahasiswa();
        $internship = $mahasiswa->student->activeInternship()->first();
        $this->nilaiLengkap($internship->id);

        $periode = Period::create([
            'academic_year' => '2025/2026',
            'semester'      => 'genap',
            'start_date'    => '2026-02-01',
            'end_date'      => '2026-07-31',
        ]);
        $mahasiswa->student->update(['period_id' => $periode->id]);

        $internship->refresh()->load(['company', 'lecturer.user', 'lecturerIndustry.user', 'student.user', 'student.period']);

        $html = view('mahasiswa.nilai.pdf', [
            'internship' => $internship,
            'nilai'      => $internship->nilaiSummary(),
            'dosen'      => $internship->rincianNilai('lecturer'),
            'industri'   => $internship->rincianNilai('lecturer_industry'),
        ])->render();

        $this->assertStringContainsString('Semester Genap', $html);
        $this->assertStringContainsString('2025/2026', $html);
    }

    public function test_rute_mengembalikan_berkas_pdf(): void
    {
        $mahasiswa  = $this->mahasiswa();
        $internship = $mahasiswa->student->activeInternship()->first();
        $this->nilaiLengkap($internship->id);

        $this->actingAs($mahasiswa)
            ->get(route('mahasiswa.nilai.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_mahasiswa_lain_dapat_lembarnya_sendiri(): void
    {
        $internship = $this->mahasiswa()->student->activeInternship()->first();
        $this->nilaiLengkap($internship->id);

        // Mahasiswa kedua belum punya magang bernilai — tak boleh kebagian
        // lembar milik orang lain, melainkan ditolak dengan penjelasan.
        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get(route('mahasiswa.nilai.pdf'))
            ->assertRedirect();

        $this->assertNotNull(session('error'));
    }
}
