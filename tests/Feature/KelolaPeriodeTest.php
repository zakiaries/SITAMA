<?php

namespace Tests\Feature;

use App\Models\Period;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Tests\FeatureTestCase;

/**
 * Kelola periode magang oleh Kaprodi.
 *
 * Tak ada formulir "buat periode": tahun akademik itu fakta kalender, seperti
 * di Simadu yang daftarnya sudah terisi bertahun-tahun ke belakang tanpa ada
 * yang pernah mengetiknya. Yang jadi keputusan Kaprodi hanya dua — periode mana
 * yang berjalan, dan prodi mana yang ikut.
 */
class KelolaPeriodeTest extends FeatureTestCase
{
    private function kaprodi()
    {
        return $this->actingAs($this->userByUsername('kaprodi'));
    }

    public function test_agustus_sampai_januari_adalah_gasal(): void
    {
        $this->assertSame(['2026/2027', 'gasal'], Period::dariTanggal(Carbon::parse('2026-08-01')));
        $this->assertSame(['2026/2027', 'gasal'], Period::dariTanggal(Carbon::parse('2026-12-31')));

        // Januari ekor Gasal tahun sebelumnya, bukan awal sesuatu yang baru —
        // magang yang mulai Agustus baru berakhir di bulan itu.
        $this->assertSame(['2026/2027', 'gasal'], Period::dariTanggal(Carbon::parse('2027-01-15')));
    }

    public function test_februari_sampai_juli_adalah_genap(): void
    {
        $this->assertSame(['2025/2026', 'genap'], Period::dariTanggal(Carbon::parse('2026-02-01')));
        $this->assertSame(['2025/2026', 'genap'], Period::dariTanggal(Carbon::parse('2026-07-31')));
    }

    public function test_jendela_diturunkan_dari_kalender(): void
    {
        $this->assertSame(['2026-08-01', '2027-01-31'], Period::jendela('2026/2027', 'gasal'));
        $this->assertSame(['2026-02-01', '2026-07-31'], Period::jendela('2025/2026', 'genap'));
    }

    public function test_langkah_menyeberang_tahun_akademik(): void
    {
        $this->assertSame(['2026/2027', 'genap'], Period::langkah('2026/2027', 'gasal', 1));
        $this->assertSame(['2027/2028', 'gasal'], Period::langkah('2026/2027', 'genap', 1));
        $this->assertSame(['2026/2027', 'gasal'], Period::langkah('2026/2027', 'genap', -1));
        $this->assertSame(['2025/2026', 'genap'], Period::langkah('2026/2027', 'gasal', -1));
    }

    /** Membuka halaman sudah cukup — tak ada yang perlu mengetik periode. */
    public function test_membuka_halaman_membangkitkan_periode_kalender(): void
    {
        $this->assertSame(0, Period::count());

        $this->kaprodi()->get(route('kaprodi.periode'))->assertOk();

        $this->assertSame(5, Period::count(), 'Dua mundur, sekarang, dua maju.');
        $this->assertNotNull(
            Period::where('academic_year', Period::dariTanggal(now())[0])
                ->where('semester', Period::dariTanggal(now())[1])->first()
        );
    }

    public function test_membuka_dua_kali_tak_menggandakan(): void
    {
        $this->kaprodi()->get(route('kaprodi.periode'))->assertOk();
        $this->kaprodi()->get(route('kaprodi.periode'))->assertOk();

        $this->assertSame(5, Period::count());
    }

    /** Prodi peserta diusulkan bergantian, karena magang berjalan selang-seling. */
    public function test_prodi_periode_baru_diusulkan_bergantian(): void
    {
        $lama = Period::create([
            'academic_year' => '2026/2027', 'semester' => 'gasal',
            'study_programs' => ['Teknologi Rekayasa Komputer'],
        ]);

        $baru = Period::pastikanAda('2026/2027', 'genap');

        $this->assertSame(['Teknik Informatika'], $baru->study_programs);
        $this->assertNotSame($lama->study_programs, $baru->study_programs);
    }

    /** Usulan boleh diubah — polanya membantu, bukan mengunci. */
    public function test_kaprodi_mengubah_prodi_peserta(): void
    {
        $periode = Period::pastikanAda('2026/2027', 'gasal');

        $this->kaprodi()->post(route('kaprodi.periode.prodi', $periode), [
            'study_programs' => ['Teknik Informatika', 'Teknologi Rekayasa Komputer'],
        ])->assertRedirect();

        $this->assertSame(
            ['Teknik Informatika', 'Teknologi Rekayasa Komputer'],
            $periode->fresh()->study_programs
        );
    }

    public function test_prodi_di_luar_daftar_ditolak(): void
    {
        $periode = Period::pastikanAda('2026/2027', 'gasal');

        $this->kaprodi()->post(route('kaprodi.periode.prodi', $periode), [
            'study_programs' => ['Sastra Jawa'],
        ])->assertSessionHasErrors();
    }

    public function test_mengaktifkan_menonaktifkan_yang_lain(): void
    {
        $lama = Period::pastikanAda('2025/2026', 'genap');
        $lama->update(['study_programs' => Student::PRODI]);
        $lama->aktifkan();

        $baru = Period::pastikanAda('2026/2027', 'gasal');
        $baru->update(['study_programs' => ['Teknologi Rekayasa Komputer']]);

        $this->kaprodi()->post(route('kaprodi.periode.aktifkan', $baru))->assertRedirect();

        $this->assertTrue($baru->fresh()->is_active);
        $this->assertFalse($lama->fresh()->is_active);
    }

    /**
     * Periode tanpa prodi peserta menerima SEMUA prodi (lihat menerimaProdi),
     * jadi mengaktifkannya tanpa sengaja membuka magang untuk semua orang.
     */
    public function test_tak_bisa_mengaktifkan_periode_tanpa_prodi(): void
    {
        $periode = Period::pastikanAda('2026/2027', 'gasal');
        $periode->update(['study_programs' => []]);

        $this->kaprodi()->post(route('kaprodi.periode.aktifkan', $periode))
            ->assertSessionHas('error');

        $this->assertFalse($periode->fresh()->is_active);
    }

    public function test_peran_lain_tak_boleh_masuk(): void
    {
        $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('kaprodi.periode'))->assertForbidden();

        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get(route('kaprodi.periode'))->assertForbidden();
    }
}
