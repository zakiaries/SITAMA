<?php

namespace Tests\Feature;

use App\Models\AssessmentComponent;
use App\Models\StudentScore;
use Tests\FeatureTestCase;

/**
 * Nilai magang yang sudah ditandai selesai Kaprodi harus TERKUNCI.
 *
 * Sebelumnya dosen pembimbing dan pembimbing industri masih bisa mengubah nilai
 * setelah magang ditutup, sehingga jejak akademiknya tak bisa dipegang. Karena
 * mengunci tanpa jalan keluar berarti nilai keliru mustahil dikoreksi, Kaprodi
 * diberi wewenang membuka kembali status selesainya.
 */
class NilaiTerkunciTest extends FeatureTestCase
{
    /** Fixture mahasiswa 1 memang sudah is_finished = true. */
    private function student()
    {
        return $this->userByUsername('3.34.23.2.01')->student;
    }

    private function detailId(string $scorerType): int
    {
        return AssessmentComponent::forScorer($scorerType)
            ->with('detailedComponents')->first()->detailedComponents->first()->id;
    }

    public function test_dosen_tak_bisa_ubah_nilai_setelah_magang_selesai(): void
    {
        $student = $this->student();
        $this->assertTrue((bool) $student->internships()->latest()->first()->is_finished);

        $detailId = $this->detailId('lecturer');

        $this->from("/dosen/mahasiswa/{$student->id}/nilai")
            ->actingAs($this->userByUsername('dosen1'))
            ->post("/dosen/mahasiswa/{$student->id}/nilai", ['scores' => [$detailId => 9]])
            ->assertSessionHas('error');

        $this->assertSame(0, StudentScore::where('scorer_type', 'lecturer')->count());
    }

    public function test_industri_tak_bisa_ubah_nilai_setelah_magang_selesai(): void
    {
        $student  = $this->student();
        $detailId = $this->detailId('lecturer_industry');

        $this->from("/dosen-industri/mahasiswa/{$student->id}/penilaian")
            ->actingAs($this->userByUsername('industri1'))
            ->post("/dosen-industri/mahasiswa/{$student->id}/penilaian", [
                'scores' => [$detailId => 9],
                'performance_notes' => 'Coba ubah setelah selesai.',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, StudentScore::where('scorer_type', 'lecturer_industry')->count());
        $this->assertNull($student->internships()->latest()->first()->performance_notes);
    }

    /** Form nilai ikut dinonaktifkan, bukan cuma ditolak di server. */
    public function test_form_nilai_dinonaktifkan_di_halaman(): void
    {
        $student = $this->student();

        $this->actingAs($this->userByUsername('dosen1'))
            ->get("/dosen/mahasiswa/{$student->id}/nilai")
            ->assertOk()
            ->assertSee('Nilai terkunci')
            ->assertDontSee('Simpan Nilai');
    }

    /** API mobile harus punya gerbang yang sama. */
    public function test_api_menolak_ubah_nilai_setelah_selesai(): void
    {
        $student = $this->student();
        $token   = $this->userByUsername('dosen1')->createToken('uji')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/dosen/mahasiswa/{$student->id}/nilai", [
                'scores' => [$this->detailId('lecturer') => 9],
            ])->assertStatus(422);

        $this->assertSame(0, StudentScore::where('scorer_type', 'lecturer')->count());
    }

    /** Jalan keluarnya: Kaprodi membuka kembali, lalu nilai bisa diperbaiki. */
    public function test_kaprodi_buka_kembali_lalu_nilai_bisa_dikoreksi(): void
    {
        $student  = $this->student();
        $detailId = $this->detailId('lecturer');

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$student->id}/buka-finish")
            ->assertSessionHas('success');

        $this->assertFalse((bool) $student->internships()->latest()->first()->is_finished);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post("/dosen/mahasiswa/{$student->id}/nilai", ['scores' => [$detailId => 9]])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, StudentScore::where('scorer_type', 'lecturer')->count());
    }

    public function test_buka_kembali_ditolak_bila_belum_selesai(): void
    {
        $student = $this->userByUsername('3.34.23.2.02')->student; // tanpa magang

        $this->from('/kaprodi/mahasiswa')
            ->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$student->id}/buka-finish")
            ->assertSessionHas('error');
    }
}
