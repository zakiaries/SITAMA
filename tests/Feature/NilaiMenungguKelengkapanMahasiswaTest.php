<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\LogBook;
use App\Models\InternshipReport;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Pembimbing bisa mengisi nilai kapan saja, bahkan saat mahasiswanya baru
 * magang seminggu dan belum punya logbook maupun laporan. Padahal nilai itu
 * menilai keseluruhan magang.
 *
 * Gerbangnya SENGAJA bukan is_finished. Checklist selesai magang justru menuntut
 * nilai kedua pembimbing sebagai syarat mahasiswa boleh mengajukan selesai, jadi
 * menggerbangi nilai dengan status selesai membuat keduanya saling menunggu.
 * Yang dipakai adalah kelengkapan mahasiswa: logbook penuh + laporan akhir
 * di-ACC. Urutannya jadi satu arah:
 *
 *   mahasiswa melengkapi → pembimbing menilai → ajukan selesai → Kaprodi ACC
 *
 * Sertifikat tidak ikut jadi syarat meski ada di checklist selesai magang:
 * sebagian perusahaan menerbitkannya jauh setelah magang berakhir.
 */
class NilaiMenungguKelengkapanMahasiswaTest extends FeatureTestCase
{
    private Student $student;
    private Internship $internship;

    protected function setUp(): void
    {
        parent::setUp();

        $mahasiswa        = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));
        $this->student    = $mahasiswa->student;
        $this->internship = $this->student->internships()->latest('id')->firstOrFail();
    }

    private function dosen(): User
    {
        return $this->userByUsername('dosen1');
    }

    private function industri(): User
    {
        return $this->userByUsername('industri1');
    }

    private function lengkapiLogbook(): void
    {
        foreach (range(1, Internship::MIN_LOGBOOK) as $i) {
            LogBook::create([
                'student_id' => $this->student->id,
                'title'      => "Kegiatan hari ke-{$i}",
                'activity'   => 'Mengerjakan tugas magang.',
                'date'       => now()->subDays(Internship::MIN_LOGBOOK - $i)->toDateString(),
            ]);
        }
    }

    private function accLaporan(): void
    {
        InternshipReport::updateOrCreate(
            ['student_id' => $this->student->id],
            ['title' => 'Laporan Akhir', 'file_path' => 'reports/uji.pdf', 'status' => 'approved']
        );
    }

    private function lengkapiSyarat(): void
    {
        $this->lengkapiLogbook();
        $this->accLaporan();
    }

    private function nilaiDosen(): array
    {
        $detail = \App\Models\DetailedAssessmentComponent::whereHas(
            'assessmentComponent', fn ($q) => $q->where('scorer_type', 'lecturer')
        )->firstOrFail();

        return ['scores' => [$detail->id => 8]];
    }

    private function nilaiIndustri(): array
    {
        $detail = \App\Models\DetailedAssessmentComponent::whereHas(
            'assessmentComponent', fn ($q) => $q->where('scorer_type', 'lecturer_industry')
        )->firstOrFail();

        return ['scores' => [$detail->id => 9]];
    }

    // ── Ditolak selagi mahasiswa belum lengkap ──────────────────────────────

    public function test_dosen_tak_bisa_menilai_sebelum_mahasiswa_lengkap(): void
    {
        $this->actingAs($this->dosen())
            ->post(route('dosen.mahasiswa.nilai.update', $this->student), $this->nilaiDosen())
            ->assertSessionHas('error');

        $this->assertSame(0, StudentScore::where('internship_id', $this->internship->id)->count(),
            'Nilai tersimpan padahal mahasiswa belum menyelesaikan magangnya.');
    }

    public function test_industri_tak_bisa_menilai_sebelum_mahasiswa_lengkap(): void
    {
        $this->actingAs($this->industri())
            ->post(route('dosen-industri.mahasiswa.penilaian.simpan', $this->student), $this->nilaiIndustri())
            ->assertSessionHas('error');

        $this->assertSame(0, StudentScore::where('internship_id', $this->internship->id)->count());
    }

    public function test_logbook_lengkap_saja_belum_cukup(): void
    {
        $this->lengkapiLogbook();

        $this->actingAs($this->dosen())
            ->post(route('dosen.mahasiswa.nilai.update', $this->student), $this->nilaiDosen())
            ->assertSessionHas('error');

        $this->assertSame(0, StudentScore::where('internship_id', $this->internship->id)->count());
    }

    public function test_laporan_diacc_saja_belum_cukup(): void
    {
        $this->accLaporan();

        $this->actingAs($this->dosen())
            ->post(route('dosen.mahasiswa.nilai.update', $this->student), $this->nilaiDosen())
            ->assertSessionHas('error');

        $this->assertSame(0, StudentScore::where('internship_id', $this->internship->id)->count());
    }

    /** Laporan yang masih menunggu ACC tak dihitung. */
    public function test_laporan_belum_diacc_tak_membuka_penilaian(): void
    {
        $this->lengkapiLogbook();
        InternshipReport::updateOrCreate(
            ['student_id' => $this->student->id],
            ['title' => 'Laporan Akhir', 'file_path' => 'reports/uji.pdf', 'status' => 'pending']
        );

        $this->assertFalse($this->internship->fresh()->siapDinilai());
    }

    // ── Terbuka setelah lengkap ─────────────────────────────────────────────

    public function test_dosen_bisa_menilai_setelah_mahasiswa_lengkap(): void
    {
        $this->lengkapiSyarat();

        $this->actingAs($this->dosen())
            ->post(route('dosen.mahasiswa.nilai.update', $this->student), $this->nilaiDosen())
            ->assertSessionHas('success');

        $this->assertSame(1, StudentScore::where('internship_id', $this->internship->id)
            ->where('scorer_type', 'lecturer')->count());
    }

    public function test_industri_bisa_menilai_setelah_mahasiswa_lengkap(): void
    {
        $this->lengkapiSyarat();

        $this->actingAs($this->industri())
            ->post(route('dosen-industri.mahasiswa.penilaian.simpan', $this->student), $this->nilaiIndustri())
            ->assertSessionHas('success');

        $this->assertSame(1, StudentScore::where('internship_id', $this->internship->id)
            ->where('scorer_type', 'lecturer_industry')->count());
    }

    /** Sertifikat sengaja tak jadi syarat — perusahaan sering terlambat. */
    public function test_sertifikat_belum_ada_tak_menghalangi_penilaian(): void
    {
        $this->lengkapiSyarat();
        $this->internship->update(['certificate_path' => null]);

        $this->assertTrue($this->internship->fresh()->siapDinilai(),
            'Sertifikat yang belum terbit seharusnya tak menahan penilaian.');

        $this->actingAs($this->dosen())
            ->post(route('dosen.mahasiswa.nilai.update', $this->student), $this->nilaiDosen())
            ->assertSessionHas('success');
    }

    // ── Halaman menjelaskan, bukan sekadar menolak ──────────────────────────

    public function test_halaman_nilai_dosen_menjelaskan_dan_menonaktifkan_form(): void
    {
        $this->actingAs($this->dosen())
            ->get(route('dosen.mahasiswa.nilai', $this->student))->assertOk()
            ->assertSee('Belum bisa dinilai')
            ->assertSee('logbook belum lengkap', false)
            ->assertDontSee('Simpan Nilai');
    }

    public function test_halaman_penilaian_industri_menjelaskan_dan_menonaktifkan_form(): void
    {
        $this->actingAs($this->industri())
            ->get(route('dosen-industri.mahasiswa.penilaian', $this->student))->assertOk()
            ->assertSee('Belum bisa dinilai')
            ->assertDontSee('Simpan &amp; Kirim Penilaian', false);
    }

    public function test_halaman_membuka_form_setelah_mahasiswa_lengkap(): void
    {
        $this->lengkapiSyarat();

        $this->actingAs($this->dosen())
            ->get(route('dosen.mahasiswa.nilai', $this->student))->assertOk()
            ->assertDontSee('Belum bisa dinilai')
            ->assertSee('Simpan Nilai');
    }

    // ── Paritas API mobile ──────────────────────────────────────────────────

    public function test_api_dosen_menolak_nilai_sebelum_lengkap(): void
    {
        $token = $this->dosen()->createToken('uji')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/dosen/mahasiswa/{$this->student->id}/nilai", $this->nilaiDosen())
            ->assertStatus(422);

        $this->assertSame(0, StudentScore::where('internship_id', $this->internship->id)->count());
    }

    public function test_api_industri_menolak_nilai_sebelum_lengkap(): void
    {
        $token = $this->industri()->createToken('uji')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/dosen-industri/mahasiswa/{$this->student->id}/penilaian", $this->nilaiIndustri())
            ->assertStatus(422);
    }

    /** Flutter perlu tahu sebelum mengirim, bukan dari 422. */
    public function test_api_memberi_tahu_form_masih_terkunci(): void
    {
        $token = $this->dosen()->createToken('uji')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/dosen/mahasiswa/{$this->student->id}/nilai")
            ->assertOk()
            ->assertJson(['can_score' => false]);

        $this->lengkapiSyarat();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/dosen/mahasiswa/{$this->student->id}/nilai")
            ->assertOk()
            ->assertJson(['can_score' => true, 'score_locked' => null]);
    }
}
