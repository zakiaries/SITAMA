<?php

namespace Tests\Feature;

use App\Models\LogBook;
use Tests\FeatureTestCase;

/**
 * Magang yang sudah ditandai selesai oleh Kaprodi adalah jejak akademik yang
 * sah. Nilainya sudah dikunci sejak 2df7469, tapi catatan logbook belum —
 * dosen & pembimbing industri masih bisa menambah, mengubah, dan menghapus
 * catatan setelah magang ditutup.
 *
 * Kuncinya punya jalan keluar: Kaprodi bisa membuka kembali status selesai
 * (kaprodi.mahasiswa.buka-finish), jadi koreksi yang sah tetap mungkin.
 */
class CatatanLogbookTerkunciTest extends FeatureTestCase
{
    /** Fixture: mahasiswa 01 magangnya sudah is_finished = true. */
    private function logbookMahasiswaSelesai(): LogBook
    {
        $student = $this->userByUsername('3.34.23.2.01')->student;

        return LogBook::create([
            'student_id' => $student->id, 'title' => 'Hari uji kunci',
            'activity'   => 'kegiatan', 'date' => '2024-02-01',
            'lecturer_note' => 'catatan lama', 'industry_note' => 'komentar lama',
        ]);
    }

    private function bukaKunci(): void
    {
        $this->userByUsername('3.34.23.2.01')->student
            ->internships()->latest('id')->first()->update(['is_finished' => false]);
    }

    // ── Dosen pembimbing ────────────────────────────────────────────────────

    public function test_dosen_tak_bisa_menyimpan_catatan_saat_magang_selesai(): void
    {
        $lb      = $this->logbookMahasiswaSelesai();
        $student = $lb->student_id;

        $this->actingAs($this->userByUsername('dosen1'))
            ->post("/dosen/mahasiswa/{$student}/logbook/{$lb->id}/note", ['note' => 'catatan baru'])
            ->assertSessionHas('error');

        $this->assertSame('catatan lama', $lb->fresh()->lecturer_note);
    }

    public function test_dosen_tak_bisa_menghapus_catatan_saat_magang_selesai(): void
    {
        $lb      = $this->logbookMahasiswaSelesai();
        $student = $lb->student_id;

        $this->actingAs($this->userByUsername('dosen1'))
            ->delete("/dosen/mahasiswa/{$student}/logbook/{$lb->id}/note")
            ->assertSessionHas('error');

        $this->assertSame('catatan lama', $lb->fresh()->lecturer_note);
    }

    public function test_dosen_masih_bisa_mencatat_saat_magang_berjalan(): void
    {
        $lb = $this->logbookMahasiswaSelesai();
        $this->bukaKunci();

        $this->actingAs($this->userByUsername('dosen1'))
            ->post("/dosen/mahasiswa/{$lb->student_id}/logbook/{$lb->id}/note", ['note' => 'catatan baru'])
            ->assertSessionHasNoErrors();

        $this->assertSame('catatan baru', $lb->fresh()->lecturer_note);
    }

    // ── Pembimbing industri ─────────────────────────────────────────────────

    public function test_industri_tak_bisa_menyimpan_komentar_saat_magang_selesai(): void
    {
        $lb = $this->logbookMahasiswaSelesai();

        $this->actingAs($this->userByUsername('industri1'))
            ->post("/dosen-industri/mahasiswa/{$lb->student_id}/logbook/{$lb->id}/komentar",
                ['komentar' => 'komentar baru'])
            ->assertSessionHas('error');

        $this->assertSame('komentar lama', $lb->fresh()->industry_note);
    }

    public function test_industri_tak_bisa_menghapus_komentar_saat_magang_selesai(): void
    {
        $lb = $this->logbookMahasiswaSelesai();

        $this->actingAs($this->userByUsername('industri1'))
            ->delete("/dosen-industri/mahasiswa/{$lb->student_id}/logbook/{$lb->id}/komentar")
            ->assertSessionHas('error');

        $this->assertSame('komentar lama', $lb->fresh()->industry_note);
    }

    public function test_industri_masih_bisa_mengomentari_saat_magang_berjalan(): void
    {
        $lb = $this->logbookMahasiswaSelesai();
        $this->bukaKunci();

        $this->actingAs($this->userByUsername('industri1'))
            ->post("/dosen-industri/mahasiswa/{$lb->student_id}/logbook/{$lb->id}/komentar",
                ['komentar' => 'komentar baru'])
            ->assertSessionHasNoErrors();

        $this->assertSame('komentar baru', $lb->fresh()->industry_note);
    }

    // ── Tampilan: tombolnya jangan ditawarkan ───────────────────────────────

    public function test_tombol_catatan_hilang_dari_halaman_dosen(): void
    {
        $lb = $this->logbookMahasiswaSelesai();

        $this->actingAs($this->userByUsername('dosen1'))
            ->get("/dosen/mahasiswa/{$lb->student_id}")
            ->assertOk()
            ->assertSee('Magang sudah selesai — catatan terkunci.')
            ->assertDontSee('Edit Catatan');
    }

    public function test_tombol_komentar_hilang_dari_halaman_industri(): void
    {
        $lb = $this->logbookMahasiswaSelesai();

        $this->actingAs($this->userByUsername('industri1'))
            ->get("/dosen-industri/mahasiswa/{$lb->student_id}")
            ->assertOk()
            ->assertSee('Magang sudah selesai — komentar terkunci.')
            ->assertDontSee('Edit Komentar');
    }

    /** Setelah Kaprodi membuka kembali, tombolnya harus kembali muncul. */
    public function test_tombol_kembali_setelah_kaprodi_membuka_status_selesai(): void
    {
        $lb = $this->logbookMahasiswaSelesai();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$lb->student_id}/buka-finish")
            ->assertSessionHasNoErrors();

        $this->actingAs($this->userByUsername('dosen1'))
            ->get("/dosen/mahasiswa/{$lb->student_id}")
            ->assertOk()
            ->assertSee('Edit Catatan')
            ->assertDontSee('catatan terkunci');
    }
}
