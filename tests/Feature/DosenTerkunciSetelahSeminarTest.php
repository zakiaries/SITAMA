<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\Internship;
use App\Models\InternshipReport;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use App\Models\Student;
use Tests\FeatureTestCase;

/**
 * Bimbingan dan laporan akhir menutup di SEMINAR, bukan di akhir magang.
 *
 * Ini titik yang mudah salah dan pernah salah: sesudah magangnya berakhir,
 * mahasiswa masih berkonsultasi menyiapkan laporan dan seminarnya. Mengunci
 * bimbingan di akhir magang memutus pembimbingan justru pada saat paling
 * dibutuhkan. Yang benar-benar menutup rangkaiannya adalah pengesahan seminar.
 *
 * Bandingkan dengan catatan logbook dan nilai, yang memang ditutup di akhir
 * magang karena keduanya data tentang MASA magang itu sendiri — dijaga di
 * tempat lain dan sengaja tidak diubah di sini.
 *
 * Kunci seminar bersifat FINAL: sesi yang sudah disahkan tak bisa dibatalkan
 * maupun dihapus, tak seperti status selesai magang yang bisa dibuka Kaprodi.
 */
class DosenTerkunciSetelahSeminarTest extends FeatureTestCase
{
    private function magangSelesai(): Internship
    {
        $internship = Internship::firstOrFail();
        $internship->update(['is_finished' => true, 'finish_requested' => true]);

        return $internship->fresh();
    }

    /** Sahkan seminar dengan mahasiswa ini sebagai penyaji. */
    private function seminarDisahkan(Student $student): Seminar
    {
        $seminar = Seminar::create([
            'lecturer_id' => $student->lecturer_id,
            'title'       => 'Seminar Magang',
            'program'     => 'Teknik Informatika',
            'date'        => now()->subDay()->toDateString(),
            'time'        => '09:00',
            'location'    => 'Ruang Sidang',
            'status'      => 'completed',
            'witnessed_at' => now(),
        ]);

        SeminarPresenter::create([
            'seminar_id' => $seminar->id,
            'student_id' => $student->id,
        ]);

        return $seminar;
    }

    private function bimbinganMenunggu(Student $student): Guidance
    {
        return Guidance::create([
            'student_id' => $student->id,
            'title'      => 'Konsultasi persiapan seminar',
            'activity'   => 'Membahas materi presentasi.',
            'date'       => now()->subDays(2)->toDateString(),
            'status'     => 'pending',
        ]);
    }

    private function laporanMenunggu(Student $student): InternshipReport
    {
        return InternshipReport::updateOrCreate(
            ['student_id' => $student->id],
            ['title' => 'Laporan Akhir', 'file_path' => 'reports/uji.pdf', 'status' => 'pending']
        );
    }

    /** Inti koreksinya: magang berakhir TIDAK menutup bimbingan. */
    public function test_bimbingan_tetap_terbuka_setelah_magang_selesai(): void
    {
        $student   = $this->magangSelesai()->student;
        $bimbingan = $this->bimbinganMenunggu($student);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.bimbingan.approve', [$student, $bimbingan]))
            ->assertSessionHas('success');

        $this->assertSame('approved', $bimbingan->fresh()->status,
            'Bimbingan ikut terkunci saat magang berakhir — padahal mahasiswa masih '
            . 'berkonsultasi menyiapkan laporan dan seminarnya.');
    }

    public function test_laporan_tetap_bisa_ditinjau_setelah_magang_selesai(): void
    {
        $student = $this->magangSelesai()->student;
        $laporan = $this->laporanMenunggu($student);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.laporan.approve', [$student, $laporan]))
            ->assertSessionHas('success');

        $this->assertSame('approved', $laporan->fresh()->status,
            'Laporan ikut terkunci saat magang berakhir — padahal justru laporan itulah '
            . 'yang disiapkan untuk seminar.');
    }

    public function test_bimbingan_terkunci_setelah_seminar_disahkan(): void
    {
        $student = $this->magangSelesai()->student;
        $this->seminarDisahkan($student);
        $bimbingan = $this->bimbinganMenunggu($student);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.bimbingan.approve', [$student, $bimbingan]))
            ->assertSessionHas('error');

        $this->assertSame('pending', $bimbingan->fresh()->status,
            'Bimbingan masih bisa diubah padahal seminarnya sudah disahkan.');
    }

    public function test_revisi_bimbingan_terkunci_setelah_seminar_disahkan(): void
    {
        $student = $this->magangSelesai()->student;
        $this->seminarDisahkan($student);
        $bimbingan = $this->bimbinganMenunggu($student);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.bimbingan.revisi', [$student, $bimbingan]),
                ['note' => 'Perbaiki bagian ini.'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $bimbingan->fresh()->status);
    }

    public function test_laporan_terkunci_setelah_seminar_disahkan(): void
    {
        $student = $this->magangSelesai()->student;
        $this->seminarDisahkan($student);
        $laporan = $this->laporanMenunggu($student);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.laporan.approve', [$student, $laporan]))
            ->assertSessionHas('error');

        $this->assertSame('pending', $laporan->fresh()->status);
    }

    /**
     * Seminar milik mahasiswa LAIN tak boleh ikut mengunci. Penjaganya harus
     * menyusuri pivot penyaji, bukan sekadar keberadaan sesi seminar.
     */
    public function test_seminar_mahasiswa_lain_tak_mengunci(): void
    {
        $student = $this->magangSelesai()->student;
        $lain    = Student::where('id', '!=', $student->id)->firstOrFail();

        $this->seminarDisahkan($lain);
        $bimbingan = $this->bimbinganMenunggu($student);

        $this->actingAs($this->userByUsername('dosen1'))
            ->post(route('dosen.mahasiswa.bimbingan.approve', [$student, $bimbingan]))
            ->assertSessionHas('success');

        $this->assertSame('approved', $bimbingan->fresh()->status,
            'Bimbingan terkunci oleh seminar milik mahasiswa lain.');
    }

    public function test_formulir_hilang_hanya_setelah_seminar_disahkan(): void
    {
        $student = $this->magangSelesai()->student;
        $this->bimbinganMenunggu($student);
        $this->laporanMenunggu($student);
        $dosen = $this->userByUsername('dosen1');

        $sebelum = $this->actingAs($dosen)
            ->get(route('dosen.mahasiswa.detail', $student))->assertOk()->getContent();

        $this->assertStringNotContainsString('bimbingan terkunci', $sebelum,
            'Formulir bimbingan sudah hilang padahal seminarnya belum disahkan.');

        $this->seminarDisahkan($student);

        $sesudah = $this->actingAs($dosen)
            ->get(route('dosen.mahasiswa.detail', $student))->assertOk()->getContent();

        $this->assertStringContainsString('bimbingan terkunci', $sesudah,
            'Formulir bimbingan masih terpampang padahal seminarnya sudah disahkan.');
        $this->assertStringContainsString('laporan akhir terkunci', $sesudah,
            'Formulir laporan masih terpampang padahal seminarnya sudah disahkan.');
    }
}
