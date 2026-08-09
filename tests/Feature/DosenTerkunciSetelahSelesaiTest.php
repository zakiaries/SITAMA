<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\Internship;
use App\Models\InternshipReport;
use Tests\FeatureTestCase;

/**
 * Setelah Kaprodi menandai magang selesai, dosen tak boleh lagi mengubah
 * bimbingan maupun laporan akhir.
 *
 * Catatan logbook dan nilai sudah lama dijaga, tetapi bimbingan dan laporan
 * terlewat — endpoint-nya menerima kiriman tanpa pemeriksaan apa pun, dan
 * tombol Setujui/Revisi tetap terpampang tepat di bawah lencana "Selesai".
 *
 * Ini bukan kerapian belaka: status "laporan di-ACC" adalah salah satu syarat
 * yang diperiksa Kaprodi sebelum menutup magang. Bila statusnya masih bisa
 * dibalik sesudah itu, yang disetujui Kaprodi bisa berbeda dari yang tercatat
 * kemudian.
 *
 * Bukan jalan buntu: Kaprodi bisa membuka kembali status selesainya.
 */
class DosenTerkunciSetelahSelesaiTest extends FeatureTestCase
{
    private function magang(bool $selesai): Internship
    {
        $internship = Internship::firstOrFail();
        $internship->update(['is_finished' => $selesai, 'finish_requested' => $selesai]);

        return $internship->fresh();
    }

    private function bimbinganMenunggu(Internship $internship): Guidance
    {
        return Guidance::create([
            'student_id' => $internship->student_id,
            'title'      => 'Konsultasi progres mingguan',
            'activity'   => 'Melaporkan progres dan kendala teknis.',
            'date'       => now()->subDays(3)->toDateString(),
            'status'     => 'pending',
        ]);
    }

    private function laporanMenunggu(Internship $internship): InternshipReport
    {
        return InternshipReport::updateOrCreate(
            ['student_id' => $internship->student_id],
            ['title' => 'Laporan Akhir', 'file_path' => 'reports/uji.pdf', 'status' => 'pending']
        );
    }

    public function test_bimbingan_tak_bisa_disetujui_setelah_magang_selesai(): void
    {
        $internship = $this->magang(true);
        $bimbingan  = $this->bimbinganMenunggu($internship);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.bimbingan.approve', [$internship->student, $bimbingan]))
            ->assertSessionHas('error');

        $this->assertSame('pending', $bimbingan->fresh()->status,
            'Bimbingan tetap tersetujui meski magangnya sudah ditutup Kaprodi.');
    }

    public function test_bimbingan_tak_bisa_direvisi_setelah_magang_selesai(): void
    {
        $internship = $this->magang(true);
        $bimbingan  = $this->bimbinganMenunggu($internship);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.bimbingan.revisi', [$internship->student, $bimbingan]),
                ['note' => 'Perbaiki sistematika.'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $bimbingan->fresh()->status,
            'Bimbingan tetap ditandai revisi meski magangnya sudah ditutup.');
    }

    public function test_laporan_tak_bisa_diubah_setelah_magang_selesai(): void
    {
        $internship = $this->magang(true);
        $laporan    = $this->laporanMenunggu($internship);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.laporan.approve', [$internship->student, $laporan]))
            ->assertSessionHas('error');

        $this->assertSame('pending', $laporan->fresh()->status,
            'Status laporan akhir masih bisa dibalik setelah magang ditutup — padahal '
            . '"laporan di-ACC" adalah syarat yang diperiksa Kaprodi sebelum menutupnya.');
    }

    /** Penjaganya tak boleh kebablasan: selama magang berjalan, semua tetap boleh. */
    public function test_masih_bisa_diubah_selama_magang_berjalan(): void
    {
        $internship = $this->magang(false);
        $bimbingan  = $this->bimbinganMenunggu($internship);
        $laporan    = $this->laporanMenunggu($internship);
        $dosen      = $this->userByUsername('dosen1');

        $this->actingAs($dosen)
            ->post(route('dosen.mahasiswa.bimbingan.approve', [$internship->student, $bimbingan]))
            ->assertSessionHas('success');
        $this->assertSame('approved', $bimbingan->fresh()->status);

        $this->actingAs($dosen)
            ->post(route('dosen.mahasiswa.laporan.approve', [$internship->student, $laporan]))
            ->assertSessionHas('success');
        $this->assertSame('approved', $laporan->fresh()->status);
    }

    /**
     * Bimbingan sengaja boleh masuk sebelum magang terbentuk (dospem membimbing
     * proposal lebih dulu). Penjaganya karena itu harus null-safe: "belum punya
     * magang" berarti belum terkunci, bukan galat.
     */
    public function test_bimbingan_tetap_bisa_disetujui_saat_magang_belum_ada(): void
    {
        $internship = Internship::firstOrFail();
        $student    = $internship->student;
        $bimbingan  = $this->bimbinganMenunggu($internship);

        $internship->delete();

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.bimbingan.approve', [$student, $bimbingan]))
            ->assertSessionHas('success');

        $this->assertSame('approved', $bimbingan->fresh()->status,
            'Bimbingan ikut terkunci padahal mahasiswanya belum punya magang sama sekali.');
    }

    public function test_formulir_bimbingan_dan_laporan_hilang_saat_selesai(): void
    {
        $internship = $this->magang(true);
        $this->bimbinganMenunggu($internship);
        $this->laporanMenunggu($internship);

        $html = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.mahasiswa.detail', $internship->student))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('bimbingan terkunci', $html,
            'Formulir Setujui/Revisi bimbingan masih terpampang padahal magang sudah selesai.');
        $this->assertStringContainsString('laporan akhir terkunci', $html,
            'Formulir Setujui/Revisi laporan masih terpampang padahal magang sudah selesai.');
    }
}
