<?php

namespace Tests\Feature;

use App\Models\AssessmentComponent;
use App\Models\StudentScore;
use Tests\FeatureTestCase;

/**
 * Keterangan penilaian pembimbing industri, satu per komponen.
 *
 * Kolom KETERANGAN ada di form resmi dan di form pembimbing industri ia
 * benar-benar dipakai — contoh dari lapangan berisi "Sangat baik", "Cukup baik
 * dalam sikap kerja". Sistem tak punya tempat menyimpannya, sehingga kolom itu
 * di lembar cetak terpaksa dibiarkan kosong untuk diisi tangan.
 */
class KeteranganNilaiIndustriTest extends FeatureTestCase
{
    private function industri()
    {
        return $this->actingAs($this->userByUsername('industri1'));
    }

    /** Komponen industri beserta butir-butirnya (satu butir per komponen). */
    private function butirIndustri()
    {
        return AssessmentComponent::forScorer('lecturer_industry')
            ->with('detailedComponents')->get()->flatMap->detailedComponents;
    }

    private function siapkan(): array
    {
        $mahasiswa = $this->siapDinilai($this->userByUsername('3.34.23.2.01'))->student;
        $internship = $mahasiswa->activeInternship()->first();
        $internship->update(['is_finished' => false]);

        return [$mahasiswa, $internship->fresh()];
    }

    public function test_keterangan_tersimpan_bersama_nilainya(): void
    {
        [$mahasiswa, $internship] = $this->siapkan();
        $butir = $this->butirIndustri();

        $this->industri()->post(route('dosen-industri.mahasiswa.penilaian.simpan', $mahasiswa), [
            'scores' => $butir->mapWithKeys(fn ($d) => [$d->id => 9])->all(),
            'notes'  => [$butir->first()->id => 'Sangat baik dalam menjalani tugas'],
        ])->assertRedirect();

        $baris = StudentScore::where('internship_id', $internship->id)
            ->where('detailed_assessment_component_id', $butir->first()->id)
            ->where('scorer_type', 'lecturer_industry')
            ->first();

        $this->assertSame('Sangat baik dalam menjalani tugas', $baris->note);
        $this->assertSame(9.0, $baris->score);
    }

    /** Menghapus isinya berarti mengosongkan, bukan mempertahankan diam-diam. */
    public function test_keterangan_bisa_dikosongkan_kembali(): void
    {
        [$mahasiswa, $internship] = $this->siapkan();
        $butir = $this->butirIndustri();
        $satu  = $butir->first();

        $kirim = fn ($catatan) => $this->industri()
            ->post(route('dosen-industri.mahasiswa.penilaian.simpan', $mahasiswa), [
                'scores' => $butir->mapWithKeys(fn ($d) => [$d->id => 8])->all(),
                'notes'  => [$satu->id => $catatan],
            ]);

        $kirim('Cukup baik');
        $kirim('   ');

        $baris = StudentScore::where('internship_id', $internship->id)
            ->where('detailed_assessment_component_id', $satu->id)
            ->where('scorer_type', 'lecturer_industry')->first();

        $this->assertNull($baris->note);
    }

    public function test_keterangan_tampil_kembali_di_layar_penilaian(): void
    {
        [$mahasiswa] = $this->siapkan();
        $butir = $this->butirIndustri();

        $this->industri()->post(route('dosen-industri.mahasiswa.penilaian.simpan', $mahasiswa), [
            'scores' => $butir->mapWithKeys(fn ($d) => [$d->id => 9])->all(),
            'notes'  => [$butir->first()->id => 'Sangat baik'],
        ]);

        $this->industri()->get(route('dosen-industri.mahasiswa.penilaian', $mahasiswa))
            ->assertOk()
            ->assertSee('Sangat baik');
    }

    /** Inti gunanya: keterangan itu ikut tercetak di lembar yang dibawa mahasiswa. */
    public function test_keterangan_tercetak_di_lembar_nilai(): void
    {
        [$mahasiswa, $internship] = $this->siapkan();
        $butir = $this->butirIndustri();

        $this->industri()->post(route('dosen-industri.mahasiswa.penilaian.simpan', $mahasiswa), [
            'scores' => $butir->mapWithKeys(fn ($d) => [$d->id => 9])->all(),
            'notes'  => [$butir->first()->id => 'Sangat baik beradaptasi'],
        ]);

        $internship = $internship->fresh()
            ->load(['company', 'lecturer.user', 'lecturerIndustry.user', 'student.user', 'student.period']);

        $html = view('mahasiswa.nilai.pdf-industri', [
            'internship' => $internship,
            'nilai'      => $internship->nilaiSummary(),
            'dosen'      => $internship->rincianNilai('lecturer'),
            'industri'   => $internship->rincianNilai('lecturer_industry'),
        ])->render();

        $this->assertStringContainsString('Sangat baik beradaptasi', $html);
    }

    public function test_keterangan_terlalu_panjang_ditolak(): void
    {
        [$mahasiswa] = $this->siapkan();
        $butir = $this->butirIndustri();

        $this->industri()->post(route('dosen-industri.mahasiswa.penilaian.simpan', $mahasiswa), [
            'scores' => $butir->mapWithKeys(fn ($d) => [$d->id => 9])->all(),
            'notes'  => [$butir->first()->id => str_repeat('a', 256)],
        ])->assertSessionHasErrors('notes.' . $butir->first()->id);
    }

    /** Label skala sempat tertinggal "/100" sejak rubrik pindah ke 1–10. */
    public function test_layar_penilaian_menyebut_skala_sepuluh(): void
    {
        [$mahasiswa] = $this->siapkan();

        $this->industri()->get(route('dosen-industri.mahasiswa.penilaian', $mahasiswa))
            ->assertOk()
            ->assertSee('/10')
            ->assertDontSee('/100');
    }
}
