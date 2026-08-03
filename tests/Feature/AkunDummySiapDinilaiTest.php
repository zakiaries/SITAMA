<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\InternshipReport;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Sejak penilaian digerbangi kelengkapan mahasiswa, tak ada satu pun tahap akun
 * dummy yang bisa dipakai pembimbing untuk MENGISI nilai dari kosong:
 *
 *   sedang-magang  → laporan belum di-ACC, jadi tergerbang.
 *   siap-selesai   → nilainya sudah terisi, tak ada yang perlu diisi.
 *   selesai        → magang ditutup, nilai terkunci.
 *
 * Padahal itu persis yang perlu dicoba dosen dan pembimbing industri saat uji
 * coba. Tahap 'siap-dinilai' menutup celah itu: syarat penilaian terpenuhi,
 * nilai sengaja dibiarkan kosong.
 */
class AkunDummySiapDinilaiTest extends FeatureTestCase
{
    private const NIM = '3.34.23.2.97';

    private function buat(string $tahap): Internship
    {
        $this->artisan('simama:mahasiswa-dummy', [
            '--nim'      => self::NIM,
            '--nama'     => 'Uji Tahap',
            '--tahap'    => $tahap,
            '--password' => 'rahasia123',
        ])->assertSuccessful();

        $student = Student::whereHas('user', fn ($q) => $q->where('username', self::NIM))->firstOrFail();

        return $student->internships()->latest('id')->firstOrFail();
    }

    public function test_tahap_siap_dinilai_memenuhi_syarat_penilaian(): void
    {
        $internship = $this->buat('siap-dinilai');

        $this->assertTrue($internship->siapDinilai(),
            'Tahap siap-dinilai belum memenuhi syarat penilaian: ' . $internship->alasanBelumSiapDinilai());
        $this->assertNull($internship->alasanBelumSiapDinilai());
    }

    public function test_tahap_siap_dinilai_meninggalkan_nilai_kosong(): void
    {
        $internship = $this->buat('siap-dinilai');

        $this->assertSame(0, StudentScore::where('internship_id', $internship->id)->count(),
            'Nilai sudah terisi, jadi pembimbing tak punya apa pun untuk diuji isi.');
    }

    /** Sertifikat bukan syarat menilai — tahap ini sekaligus membuktikannya. */
    public function test_tahap_siap_dinilai_belum_punya_sertifikat(): void
    {
        $internship = $this->buat('siap-dinilai');

        $this->assertNull($internship->certificate_path);
        $this->assertTrue($internship->siapDinilai(),
            'Sertifikat yang belum ada seharusnya tak menahan penilaian.');
    }

    public function test_magangnya_belum_ditutup(): void
    {
        $internship = $this->buat('siap-dinilai');

        $this->assertFalse((bool) $internship->is_finished);
        $this->assertFalse((bool) $internship->finish_requested);
    }

    /** Inti gunanya: dosen benar-benar bisa menyimpan nilai untuk akun ini. */
    public function test_dosen_bisa_mengisi_nilai_untuk_akun_ini(): void
    {
        $internship = $this->buat('siap-dinilai');
        $student    = $internship->student;
        $dosen      = User::find($internship->lecturer->user_id);

        $detail = \App\Models\DetailedAssessmentComponent::whereHas(
            'assessmentComponent', fn ($q) => $q->where('scorer_type', 'lecturer')
        )->firstOrFail();

        $this->actingAs($dosen)
            ->post(route('dosen.mahasiswa.nilai.update', $student), ['scores' => [$detail->id => 8]])
            ->assertSessionHas('success');

        $this->assertSame(1, StudentScore::where('internship_id', $internship->id)
            ->where('scorer_type', 'lecturer')->count());
    }

    // ── Tahap lain tidak boleh ikut berubah ─────────────────────────────────

    public function test_sedang_magang_tetap_tergerbang(): void
    {
        $internship = $this->buat('sedang-magang');

        $this->assertFalse($internship->siapDinilai());
        $this->assertNull(InternshipReport::where('student_id', $internship->student_id)
            ->where('status', 'approved')->first());
    }

    public function test_siap_selesai_tetap_lengkap(): void
    {
        $internship = $this->buat('siap-selesai');

        $this->assertTrue($internship->siapDinilai());
        $this->assertNotNull($internship->certificate_path);
        $this->assertGreaterThan(0, StudentScore::where('internship_id', $internship->id)->count());
    }

    public function test_tahap_asing_ditolak(): void
    {
        $this->artisan('simama:mahasiswa-dummy', [
            '--nim'   => self::NIM,
            '--tahap' => 'entah-apa',
        ])->assertFailed();
    }
}
