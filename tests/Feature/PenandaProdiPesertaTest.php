<?php

namespace Tests\Feature;

use App\Models\CompanyRequest;
use App\Models\Period;
use Tests\FeatureTestCase;

/**
 * Penanda "di luar prodi peserta" saat Kaprodi meninjau pengajuan magang.
 *
 * Magang berjalan selang-seling: saat Teknik Informatika magang, Teknologi
 * Rekayasa Komputer tidak. Pengajuan dari prodi yang belum gilirannya DITANDAI,
 * bukan ditolak — yang terkena justru mahasiswa mengulang, cuti, atau magang
 * mandiri di luar jadwal angkatannya, dan merekalah yang paling butuh ditimbang
 * manusia. Menutup pintunya berarti satu-satunya jalan keluar adalah Kaprodi
 * mengubah periode aktif, yang berdampak ke semua orang demi satu kasus.
 */
class PenandaProdiPesertaTest extends FeatureTestCase
{
    private function periodeBerjalan(array $prodi): Period
    {
        $periode = Period::create([
            'academic_year'  => '2026/2027',
            'semester'       => 'gasal',
            'start_date'     => '2026-08-01',
            'end_date'       => '2027-01-31',
            'study_programs' => $prodi,
            'is_active'      => true,
        ]);

        return $periode;
    }

    private function ajukan(): CompanyRequest
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.02')->student;

        return CompanyRequest::create([
            'student_id'  => $mahasiswa->id,
            'company_name' => 'PT Uji Selang Seling',
            'pic_name'    => 'Budi',
            'pic_email'   => 'budi@uji.test',
            'pic_phone'   => '08123456789',
            'position'    => 'Developer',
            'start_date'  => '2026-09-01',
            'end_date'    => '2027-01-31',
            'status'      => 'pending',
        ]);
    }

    private function kaprodi()
    {
        return $this->actingAs($this->userByUsername('kaprodi'));
    }

    /** Fixture mahasiswa berprodi Teknik Informatika. */
    public function test_prodi_di_luar_giliran_ditandai(): void
    {
        $this->periodeBerjalan(['Teknologi Rekayasa Komputer']);
        $this->ajukan();

        $this->kaprodi()->get(route('kaprodi.pengajuan-magang.index'))
            ->assertOk()
            ->assertSee('Di luar prodi peserta periode ini.');
    }

    public function test_prodi_yang_sedang_giliran_tidak_ditandai(): void
    {
        $this->periodeBerjalan(['Teknik Informatika']);
        $this->ajukan();

        $this->kaprodi()->get(route('kaprodi.pengajuan-magang.index'))
            ->assertOk()
            ->assertDontSee('Di luar prodi peserta periode ini.');
    }

    /** Penanda hanya memberi tahu — tombol Setujui tetap berfungsi. */
    public function test_pengajuan_di_luar_giliran_tetap_bisa_disetujui(): void
    {
        $this->periodeBerjalan(['Teknologi Rekayasa Komputer']);
        $pengajuan = $this->ajukan();

        $this->kaprodi()->post(route('kaprodi.pengajuan-magang.approve', $pengajuan), [
            'pic_username' => 'budi.uji',
            'pic_password' => 'rahasia123',
        ]);

        $this->assertSame('approved', $pengajuan->fresh()->status,
            'Penanda tak boleh berubah jadi gerbang.');
    }

    /** Tanpa periode aktif, tak ada yang bisa disebut "di luar giliran". */
    public function test_tanpa_periode_aktif_tak_ada_penanda(): void
    {
        $this->ajukan();

        $this->kaprodi()->get(route('kaprodi.pengajuan-magang.index'))
            ->assertOk()
            ->assertDontSee('Di luar prodi peserta periode ini.');
    }

    /** Daftar prodi kosong berarti belum dibatasi — semua diterima. */
    public function test_periode_tanpa_daftar_prodi_tak_menandai_siapa_pun(): void
    {
        $this->periodeBerjalan([]);
        $this->ajukan();

        $this->kaprodi()->get(route('kaprodi.pengajuan-magang.index'))
            ->assertOk()
            ->assertDontSee('Di luar prodi peserta periode ini.');
    }
}
