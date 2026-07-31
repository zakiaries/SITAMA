<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\InternshipReport;
use App\Models\LogBook;
use Tests\FeatureTestCase;

/**
 * Penanda "menunggu tanggapan" untuk dosen & pembimbing industri.
 *
 * Sebelumnya keduanya tak punya cara tahu ada unggahan baru selain membuka
 * satu per satu mahasiswanya. Penanda muncul di dua tempat: angka di menu
 * sidebar (total) dan titik merah + label di kartu mahasiswa.
 */
class PenandaBaruTest extends FeatureTestCase
{
    private function studentSatu()
    {
        return $this->userByUsername('3.34.23.2.01')->student;
    }

    public function test_dosen_melihat_penanda_untuk_logbook_bimbingan_dan_laporan(): void
    {
        $student = $this->studentSatu();

        LogBook::create(['student_id' => $student->id, 'title' => 'L1', 'activity' => 'a', 'date' => '2024-02-01']);
        LogBook::create(['student_id' => $student->id, 'title' => 'L2', 'activity' => 'b', 'date' => '2024-02-02']);
        Guidance::create(['student_id' => $student->id, 'title' => 'B1', 'activity' => 'a', 'date' => '2024-02-01', 'status' => 'pending']);
        InternshipReport::create(['student_id' => $student->id, 'title' => 'Laporan', 'file_path' => 'reports/x.pdf', 'status' => 'pending']);

        $this->actingAs($this->userByUsername('dosen1'))
            ->get('/dosen/dashboard')
            ->assertOk()
            ->assertSee('2 logbook belum dikomentari')
            ->assertSee('1 bimbingan belum di-ACC')
            ->assertSee('Laporan menunggu review')
            ->assertSee('nav-badge', false)   // badge sidebar muncul
            ->assertSee('dot-baru', false);   // titik merah di kartu
    }

    /** Inti penanda: begitu ditanggapi, penandanya harus hilang. */
    public function test_penanda_dosen_hilang_setelah_semua_ditanggapi(): void
    {
        $student = $this->studentSatu();

        $lb = LogBook::create(['student_id' => $student->id, 'title' => 'L1', 'activity' => 'a', 'date' => '2024-02-01']);
        $bim = Guidance::create(['student_id' => $student->id, 'title' => 'B1', 'activity' => 'a', 'date' => '2024-02-01', 'status' => 'pending']);

        $dosen = $this->userByUsername('dosen1');
        $this->actingAs($dosen)->get('/dosen/dashboard')->assertSee('dot-baru', false);

        $lb->update(['lecturer_note' => 'Sudah saya periksa.']);
        $bim->update(['status' => 'approved']);

        $this->actingAs($dosen)->get('/dosen/dashboard')
            ->assertOk()
            ->assertDontSee('dot-baru', false)
            ->assertDontSee('nav-badge', false);
    }

    public function test_pembimbing_industri_melihat_penanda_logbook(): void
    {
        $student = $this->studentSatu();

        $lb = LogBook::create(['student_id' => $student->id, 'title' => 'L1', 'activity' => 'a', 'date' => '2024-02-01']);

        $industri = $this->userByUsername('industri1');
        $this->actingAs($industri)->get('/dosen-industri/dashboard')
            ->assertOk()
            ->assertSee('1 logbook belum dikomentari')
            ->assertSee('dot-baru', false);

        $lb->update(['industry_note' => 'Bagus.']);

        $this->actingAs($industri)->get('/dosen-industri/dashboard')
            ->assertOk()
            ->assertDontSee('dot-baru', false)
            ->assertDontSee('nav-badge', false);
    }

    /**
     * Komentar dosen dan komentar industri disimpan di kolom berbeda —
     * penanda satu peran tak boleh ikut hilang karena peran lain menanggapi.
     */
    public function test_penanda_dosen_dan_industri_saling_bebas(): void
    {
        $student = $this->studentSatu();

        $lb = LogBook::create(['student_id' => $student->id, 'title' => 'L1', 'activity' => 'a', 'date' => '2024-02-01']);
        $lb->update(['industry_note' => 'Sudah dikomentari industri.']);

        // Industri sudah menanggapi → penandanya hilang.
        $this->actingAs($this->userByUsername('industri1'))->get('/dosen-industri/dashboard')
            ->assertOk()
            ->assertDontSee('dot-baru', false);

        // Dosen belum → penandanya harus TETAP ada.
        $this->actingAs($this->userByUsername('dosen1'))->get('/dosen/dashboard')
            ->assertOk()
            ->assertSee('1 logbook belum dikomentari');
    }

    /** Model: hitungan dipakai badge sidebar. */
    public function test_hitungan_model_akurat(): void
    {
        $student  = $this->studentSatu();
        $dosen    = $this->userByUsername('dosen1')->lecturer;
        $industri = $this->userByUsername('industri1')->lecturer;

        LogBook::create(['student_id' => $student->id, 'title' => 'L1', 'activity' => 'a', 'date' => '2024-02-01']);
        LogBook::create(['student_id' => $student->id, 'title' => 'L2', 'activity' => 'b', 'date' => '2024-02-02', 'lecturer_note' => 'ok', 'industry_note' => 'ok']);
        Guidance::create(['student_id' => $student->id, 'title' => 'B1', 'activity' => 'a', 'date' => '2024-02-01', 'status' => 'pending']);

        $kampus = $dosen->menungguTanggapanKampus();

        $this->assertSame(1, $kampus['logbook']);
        $this->assertSame(1, $kampus['bimbingan']);
        $this->assertSame(0, $kampus['laporan']);
        $this->assertSame(2, $kampus['total']);

        $this->assertSame(1, $industri->menungguTanggapanIndustri());
    }
}
