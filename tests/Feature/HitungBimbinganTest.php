<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use Tests\FeatureTestCase;

/**
 * Angka "mahasiswa bimbingan" di halaman Data Dosen (Kaprodi).
 *
 * Dua bug yang sama-sama menghasilkan angka nol:
 *  1. Tab Pembimbing Industri SELALU 0 — hitungannya menumpuk kondisi
 *     lecturer_industry_id di atas relasi internships() yang foreign key-nya
 *     lecturer_id, sehingga query menuntut satu dosen menjadi pembimbing
 *     kampus sekaligus pembimbing industri pada magang yang sama.
 *  2. Tab Dosen Kampus tidak berubah saat Kaprodi memplot dospem, karena
 *     yang dihitung magang, bukan mahasiswa yang diplot.
 */
class HitungBimbinganTest extends FeatureTestCase
{
    public function test_hitungan_dosen_kampus_naik_begitu_kaprodi_memplot(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');
        $dosen   = $this->userByUsername('dosen1')->lecturer;

        // Fixture: mhs1 & mhs2 sudah diplot ke dosen1.
        $this->actingAs($kaprodi)->get('/kaprodi/dosen')
            ->assertOk()
            ->assertSee('2/20');

        // mhs3 belum punya dospem dan belum punya magang sama sekali.
        $mhs3 = $this->userByUsername('3.34.23.2.03')->student;
        $this->assertNull($mhs3->lecturer_id);

        $this->actingAs($kaprodi)
            ->post("/kaprodi/mahasiswa/{$mhs3->id}/assign-lecturer", ['lecturer_id' => $dosen->id]);

        $this->assertSame($dosen->id, $mhs3->fresh()->lecturer_id);

        // Inti bug: angkanya harus langsung ikut naik, walau mhs3 belum magang.
        $this->actingAs($kaprodi)->get('/kaprodi/dosen')
            ->assertOk()
            ->assertSee('3/20');
    }

    public function test_hitungan_pembimbing_industri_tidak_selalu_nol(): void
    {
        $industri = $this->userByUsername('industri1')->lecturer;

        // Fixture: 1 magang memakai industri1 sebagai pembimbing industri.
        $this->assertSame(1, $industri->industryInternships()->distinct()->count('student_id'));

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get('/kaprodi/dosen?tab=industri')
            ->assertOk()
            ->assertSee('1/20')
            ->assertDontSee('0/20');
    }

    public function test_detail_dosen_memuat_mahasiswa_yang_diplot_walau_belum_magang(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');
        $dosen   = $this->userByUsername('dosen1')->lecturer;
        $mhs3    = $this->userByUsername('3.34.23.2.03');

        $mhs3->student->update(['lecturer_id' => $dosen->id, 'status' => 'active']);

        // Daftar di halaman detail harus konsisten dengan angka di kartu:
        // mahasiswa yang diplot tapi belum magang tetap muncul.
        $this->actingAs($kaprodi)->get("/kaprodi/dosen/{$dosen->id}")
            ->assertOk()
            ->assertSee($mhs3->name);
    }

    /** Relasi industri memakai kolom yang benar (bukan lecturer_id). */
    public function test_relasi_industry_internships_pakai_kolom_yang_benar(): void
    {
        $this->assertSame(
            'lecturer_industry_id',
            (new Lecturer())->industryInternships()->getForeignKeyName()
        );
    }
}
