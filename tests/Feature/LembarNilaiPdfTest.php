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
 * DUA lembar terpisah, mengikuti form resmi Polines: satu ditandatangani dosen
 * pembimbing, satu ditandatangani pembimbing industri, lalu diserahkan
 * sendiri-sendiri. Susunan barisnya disalin persis dari kedua form itu,
 * termasuk penomoran, baris kepala yang tak dinilai, dan kolom skala 1–10 yang
 * dicentang.
 */
class LembarNilaiPdfTest extends FeatureTestCase
{
    private function mahasiswa(): User
    {
        return $this->userByUsername('3.34.23.2.01');
    }

    /** Isi nilai satu penilai, skala 1–10. */
    private function nilai(int $internshipId, string $penilai, int $skor): void
    {
        AssessmentComponent::forScorer($penilai)->with('detailedComponents')->get()
            ->flatMap->detailedComponents
            ->each(fn ($detail) => StudentScore::updateOrCreate([
                'internship_id'                    => $internshipId,
                'detailed_assessment_component_id' => $detail->id,
                'scorer_type'                      => $penilai,
            ], ['score' => $skor]));
    }

    private function magang()
    {
        return $this->mahasiswa()->student->activeInternship()->first();
    }

    private function render(string $berkas)
    {
        $internship = $this->magang()->fresh()
            ->load(['company', 'lecturer.user', 'lecturerIndustry.user', 'student.user', 'student.period']);

        // Blade dirender langsung: isi PDF dari dompdf termampatkan sehingga
        // teksnya tak bisa dicari, sedangkan yang perlu dibuktikan justru
        // susunan baris dan angkanya.
        return view("mahasiswa.nilai.{$berkas}", [
            'internship' => $internship,
            'nilai'      => $internship->nilaiSummary(),
            'dosen'      => $internship->rincianNilai('lecturer'),
            'industri'   => $internship->rincianNilai('lecturer_industry'),
        ])->render();
    }

    public function test_tiap_lembar_menunggu_penilainya_sendiri(): void
    {
        $this->actingAs($this->mahasiswa())
            ->get(route('mahasiswa.nilai.pdf', 'dosen'))
            ->assertRedirect();
        $this->assertStringContainsString('dosen pembimbing', session('error'));

        $this->actingAs($this->mahasiswa())
            ->get(route('mahasiswa.nilai.pdf', 'industri'))
            ->assertRedirect();
        $this->assertStringContainsString('pembimbing industri', session('error'));
    }

    /** Tanda tangan dosen tak perlu menunggu pihak perusahaan. */
    public function test_lembar_dosen_terbit_walau_industri_belum_menilai(): void
    {
        $this->nilai($this->magang()->id, 'lecturer', 8);

        $this->actingAs($this->mahasiswa())
            ->get(route('mahasiswa.nilai.pdf', 'dosen'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->mahasiswa())
            ->get(route('mahasiswa.nilai.pdf', 'industri'))
            ->assertRedirect();
    }

    public function test_tombol_muncul_terpisah_sesuai_kesiapan(): void
    {
        $this->nilai($this->magang()->id, 'lecturer', 8);

        $this->actingAs($this->mahasiswa())
            ->get(route('mahasiswa.nilai'))
            ->assertOk()
            ->assertSee('Lembar Nilai Dosen Pembimbing')
            ->assertDontSee('Lembar Nilai Pembimbing Industri');

        $this->nilai($this->magang()->id, 'lecturer_industry', 9);

        $this->actingAs($this->mahasiswa())
            ->get(route('mahasiswa.nilai'))
            ->assertSee('Lembar Nilai Pembimbing Industri');
    }

    /** Susunan baris form dosen disalin persis, termasuk baris kepala. */
    public function test_lembar_dosen_mengikuti_susunan_form_resmi(): void
    {
        $this->nilai($this->magang()->id, 'lecturer', 8);
        $html = $this->render('pdf-dosen');

        $this->assertStringContainsString('Daftar Penilaian Magang', $html);
        // Disalin apa adanya, termasuk ketidakkonsistenan form aslinya:
        // "bobot nilai 20 %" untuk Proposal, "bobot 80 %" untuk Laporan.
        $this->assertStringContainsString('Proposal (bobot nilai 20 %)', $html);
        $this->assertStringContainsString('Laporan (bobot 80 %)', $html);

        // Baris kepala yang TIDAK dinilai, ada di form tapi bukan butir rubrik.
        $this->assertStringContainsString('2. Kelengkapan proposal Magang', $html);
        $this->assertStringContainsString('2. Bahasa', $html);
        $this->assertStringContainsString('3. Isi', $html);

        // Penomoran butir persis form.
        $this->assertStringContainsString('a. Kesesuaian antara tujuan dan sasaran', $html);
        $this->assertStringContainsString('e. Kelengkapan lampiran', $html);

        // Petunjuk pengisian & kolom skala.
        $this->assertStringContainsString('Berilah tanda cek', $html);
        $this->assertStringContainsString('Keterangan', $html);
    }

    public function test_lembar_dosen_memuat_total_dan_rata_rata(): void
    {
        $this->nilai($this->magang()->id, 'lecturer', 8);
        $html = $this->render('pdf-dosen');

        // 12 butir bernilai 8 → total 96, rata-rata berbobot tetap 8.
        $this->assertStringContainsString('Total Nilai', $html);
        $this->assertStringContainsString('96', $html);
        $this->assertStringContainsString('Nilai Rata-rata', $html);
    }

    /** Skor tercetak sebagai centang di kolom yang sesuai, seperti diisi tangan. */
    public function test_skor_dicentang_di_kolom_skala(): void
    {
        $this->nilai($this->magang()->id, 'lecturer', 7);
        $html = $this->render('pdf-dosen');

        $this->assertStringContainsString('√', $html);
    }

    /** Sub-butir komponen industri ikut tercetak, seperti di formnya. */
    public function test_lembar_industri_memuat_rincian_sub_butir(): void
    {
        $this->nilai($this->magang()->id, 'lecturer_industry', 9);
        $html = $this->render('pdf-industri');

        $this->assertStringContainsString('Keterampilan dalam Menjalankan Tugas', $html);
        $this->assertStringContainsString('c. Ketepatan waktu', $html);
        $this->assertStringContainsString('d. Hubungan dengan relasi', $html);
        $this->assertStringContainsString('e. Penampilan', $html);

        // 8 komponen bernilai 9 → total 72, rata-rata 9.
        $this->assertStringContainsString('72', $html);
    }

    /** Tiap lembar hanya memuat SATU tanda tangan — itu inti pemisahannya. */
    public function test_tiap_lembar_hanya_satu_penandatangan(): void
    {
        $this->nilai($this->magang()->id, 'lecturer', 8);
        $this->nilai($this->magang()->id, 'lecturer_industry', 9);

        $dosen = $this->render('pdf-dosen');
        $this->assertStringContainsString('Dosen Pembimbing,', $dosen);
        $this->assertStringNotContainsString('Pembimbing Industri,', $dosen);

        $industri = $this->render('pdf-industri');
        $this->assertStringContainsString('Pembimbing Industri,', $industri);
        $this->assertStringNotContainsString('Dosen Pembimbing,', $industri);

        // Pembimbing industri cukup namanya. Yang tersimpan untuk mereka adalah
        // nama pengguna untuk masuk sistem, bukan nomor induk kepegawaian —
        // mencetaknya sebagai NIP akan memalsukan keterangan.
        $this->assertStringNotContainsString('NIP', $industri);
        $this->assertStringContainsString('NIP.', $dosen);
    }

    /** Identitas mengikuti form: nama, NIM, tempat magang, alamat. */
    public function test_identitas_mengikuti_form_resmi(): void
    {
        $this->nilai($this->magang()->id, 'lecturer', 8);
        $html = $this->render('pdf-dosen');

        foreach (['Nama Mahasiswa', 'NIM', 'Tempat Magang', 'Alamat'] as $baris) {
            $this->assertStringContainsString($baris, $html);
        }
    }

    public function test_lembar_menyebut_periode_magangnya(): void
    {
        $this->nilai($this->magang()->id, 'lecturer', 8);

        $periode = Period::create([
            'academic_year' => '2025/2026', 'semester' => 'genap',
            'start_date' => '2026-02-01', 'end_date' => '2026-07-31',
        ]);
        $this->mahasiswa()->student->update(['period_id' => $periode->id]);

        $this->assertStringContainsString('2025/2026 Genap', $this->render('pdf-dosen'));
    }

    public function test_penilai_tak_dikenal_ditolak(): void
    {
        $this->actingAs($this->mahasiswa())
            ->get('/mahasiswa/nilai/pdf/kaprodi')
            ->assertNotFound();
    }
}
