<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\InternshipReport;
use Tests\FeatureTestCase;

/**
 * Berkas unggahan mahasiswa harus BISA DIBUKA dari layar, bukan sekadar
 * diizinkan gerbangnya.
 *
 * Audit menemukan gerbang aksesnya (BerkasController) sudah lama mengizinkan
 * Kaprodi, dosen pembimbing, dan pembimbing industri — tapi beberapa portal tak
 * punya satu pun tautannya, sehingga izin itu tak pernah bisa dipakai. Yang
 * paling parah: Kaprodi meng-ACC selesai magang berdasarkan checklist yang
 * memuat sertifikat dan laporan, tanpa bisa membuka keduanya.
 */
class TautanBerkasTest extends FeatureTestCase
{
    private function siapkanBerkas(): array
    {
        $mahasiswa  = $this->userByUsername('3.34.23.2.01')->student;
        $internship = $mahasiswa->activeInternship()->first();

        $internship->update(['certificate_path' => 'certificates/uji.pdf']);

        $laporan = InternshipReport::updateOrCreate(
            ['student_id' => $mahasiswa->id],
            ['title' => 'Laporan Akhir', 'file_path' => 'reports/uji.pdf', 'status' => 'approved']
        );

        $bimbingan = Guidance::create([
            'student_id' => $mahasiswa->id,
            'title'      => 'Bimbingan Uji',
            'activity'   => 'Konsultasi laporan akhir',
            'date'       => now()->subDays(3)->toDateString(),
            'name_file'  => 'guidances/uji.pdf',
            'status'     => 'approved',
        ]);

        return [$mahasiswa, $internship, $laporan, $bimbingan];
    }

    /** Kaprodi meng-ACC selesai magang — ia harus bisa membuka yang di-ACC-nya. */
    public function test_kaprodi_punya_tautan_sertifikat_laporan_dan_bimbingan(): void
    {
        [$mahasiswa, $internship, $laporan, $bimbingan] = $this->siapkanBerkas();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.mahasiswa.detail', $mahasiswa))
            ->assertOk()
            ->assertSee(route('berkas.sertifikat', $internship), false)
            ->assertSee(route('berkas.laporan', $laporan), false)
            ->assertSee(route('berkas.bimbingan', $bimbingan), false);
    }

    public function test_dosen_punya_tautan_sertifikat(): void
    {
        [$mahasiswa, $internship] = $this->siapkanBerkas();

        $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.mahasiswa.detail', $mahasiswa))
            ->assertOk()
            ->assertSee(route('berkas.sertifikat', $internship), false);
    }

    public function test_pembimbing_industri_punya_tautan_laporan_dan_sertifikat(): void
    {
        [$mahasiswa, $internship, $laporan] = $this->siapkanBerkas();

        $this->actingAs($this->userByUsername('industri1'))
            ->get(route('dosen-industri.mahasiswa.detail', $mahasiswa))
            ->assertOk()
            ->assertSee(route('berkas.laporan', $laporan), false)
            ->assertSee(route('berkas.sertifikat', $internship), false);
    }

    /** Tautan tak boleh muncul untuk berkas yang belum ada. */
    public function test_tanpa_berkas_ditulis_belum_diunggah(): void
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.01')->student;
        $mahasiswa->activeInternship()->first()->update(['certificate_path' => null]);
        InternshipReport::where('student_id', $mahasiswa->id)->delete();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.mahasiswa.detail', $mahasiswa))
            ->assertOk()
            ->assertSee('Belum diunggah');
    }

    /**
     * Tautannya boleh ada, tapi gerbangnya tetap berlaku: peran lain yang
     * kebetulan menebak URL-nya harus tetap ditolak.
     */
    public function test_mahasiswa_lain_tetap_ditolak_membuka_berkas(): void
    {
        [, , $laporan] = $this->siapkanBerkas();

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get(route('berkas.laporan', $laporan))
            ->assertForbidden();
    }
}
