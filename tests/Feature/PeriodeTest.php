<?php

namespace Tests\Feature;

use App\Models\Period;
use Tests\FeatureTestCase;

/**
 * Periode magang — satu semester dari satu tahun akademik.
 *
 * Tahap ini belum menyentuh tampilan mana pun; yang diuji adalah aturan
 * modelnya, karena itulah yang akan dijadikan sandaran penyaring Kaprodi dan
 * form pendaftaran menyusul.
 */
class PeriodeTest extends FeatureTestCase
{
    private function buatPeriode(string $tahun, string $semester, array $extra = []): Period
    {
        return Period::create(array_merge([
            'academic_year'   => $tahun,
            'semester'        => $semester,
            'start_date'      => '2026-08-01',
            'end_date'        => '2027-01-31',
            'duration_months' => Period::DEFAULT_DURATION_MONTHS,
            'study_programs'  => ['Teknologi Rekayasa Komputer'],
        ], $extra));
    }

    /** Label memakai penulisan Simadu Polines: "Gasal", bukan "Ganjil". */
    public function test_label_mengikuti_penulisan_simadu(): void
    {
        $this->assertSame('2026/2027 Gasal', $this->buatPeriode('2026/2027', 'gasal')->label);
        $this->assertSame('2025/2026 Genap', $this->buatPeriode('2025/2026', 'genap')->label);
    }

    public function test_mengaktifkan_periode_menonaktifkan_yang_lain(): void
    {
        $lama = $this->buatPeriode('2025/2026', 'genap', ['is_active' => true]);
        $baru = $this->buatPeriode('2026/2027', 'gasal');

        $baru->aktifkan();

        $this->assertTrue($baru->fresh()->is_active);
        $this->assertFalse($lama->fresh()->is_active, 'Periode lama harus ikut dimatikan.');
        $this->assertSame($baru->id, Period::sekarang()->id);
    }

    public function test_tanpa_periode_aktif_sekarang_bernilai_null(): void
    {
        $this->buatPeriode('2026/2027', 'gasal');

        $this->assertNull(Period::sekarang());
    }

    /** Terbaru di atas, dan Gasal berjalan lebih dulu daripada Genap. */
    public function test_urutan_terbaru_menempatkan_genap_di_atas_gasal(): void
    {
        $this->buatPeriode('2025/2026', 'gasal');
        $this->buatPeriode('2026/2027', 'gasal');
        $this->buatPeriode('2025/2026', 'genap');

        $this->assertSame(
            ['2026/2027 Gasal', '2025/2026 Genap', '2025/2026 Gasal'],
            Period::terbaru()->get()->map->label->all()
        );
    }

    public function test_prodi_peserta_menentukan_siapa_yang_diterima(): void
    {
        $periode = $this->buatPeriode('2026/2027', 'gasal');

        $this->assertTrue($periode->menerimaProdi('Teknologi Rekayasa Komputer'));
        $this->assertFalse($periode->menerimaProdi('Teknik Informatika'));
        $this->assertFalse($periode->menerimaProdi(null));
    }

    /** Daftar kosong berarti belum dibatasi — bukan mengunci semua orang. */
    public function test_periode_tanpa_daftar_prodi_menerima_semua(): void
    {
        $periode = $this->buatPeriode('2026/2027', 'gasal', ['study_programs' => []]);

        $this->assertTrue($periode->menerimaProdi('Teknik Informatika'));
        $this->assertTrue($periode->menerimaProdi('Teknologi Rekayasa Komputer'));
    }

    public function test_satu_tahun_akademik_tak_boleh_punya_semester_kembar(): void
    {
        $this->buatPeriode('2026/2027', 'gasal');

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->buatPeriode('2026/2027', 'gasal');
    }

    public function test_mahasiswa_terhubung_dua_arah_ke_periodenya(): void
    {
        $periode  = $this->buatPeriode('2026/2027', 'gasal');
        $mahasiswa = $this->userByUsername('3.34.23.2.01')->student;

        $mahasiswa->update(['period_id' => $periode->id]);

        $this->assertSame($periode->id, $mahasiswa->fresh()->period->id);
        $this->assertTrue($periode->students()->whereKey($mahasiswa->id)->exists());
    }

    /**
     * Menghapus periode tak boleh ikut menghapus mahasiswanya — datanya jauh
     * lebih berharga daripada pengelompokannya.
     */
    public function test_menghapus_periode_menyisakan_mahasiswanya(): void
    {
        $periode   = $this->buatPeriode('2026/2027', 'gasal');
        $mahasiswa = $this->userByUsername('3.34.23.2.01')->student;
        $mahasiswa->update(['period_id' => $periode->id]);

        $periode->delete();

        $this->assertNotNull($mahasiswa->fresh(), 'Mahasiswa tidak boleh ikut terhapus.');
        $this->assertNull($mahasiswa->fresh()->period_id);
    }
}
