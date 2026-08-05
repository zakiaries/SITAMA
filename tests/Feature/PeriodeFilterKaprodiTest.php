<?php

namespace Tests\Feature;

use App\Models\Period;
use App\Models\Student;
use Tests\FeatureTestCase;

/**
 * Penyaring periode di portal Kaprodi.
 *
 * Keluhan yang memicunya: dashboard dan Data Mahasiswa menumpuk semua angkatan
 * jadi satu, dan penyaring tahunnya membaca teks bebas yang diketik mahasiswa
 * sehingga menyodorkan nilai seperti "2023/2026". Yang diuji di sini adalah
 * bahwa Kaprodi kini melihat SATU angkatan pada satu waktu, dan tak ada
 * mahasiswa yang lenyap gara-gara penyaring itu.
 */
class PeriodeFilterKaprodiTest extends FeatureTestCase
{
    private Period $gasal;
    private Period $genap;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gasal = Period::create([
            'academic_year' => '2026/2027', 'semester' => 'gasal',
            'start_date' => '2026-08-01', 'end_date' => '2027-01-31',
            'duration_months' => 5, 'study_programs' => ['Teknologi Rekayasa Komputer'],
            'is_active' => true,
        ]);

        $this->genap = Period::create([
            'academic_year' => '2025/2026', 'semester' => 'genap',
            'start_date' => '2026-02-01', 'end_date' => '2026-07-31',
            'duration_months' => 5, 'study_programs' => ['Teknik Informatika'],
        ]);
    }

    private function taruh(string $nim, ?Period $periode): Student
    {
        $mahasiswa = $this->userByUsername($nim)->student;
        $mahasiswa->update(['period_id' => $periode?->id]);

        return $mahasiswa;
    }

    private function kaprodi()
    {
        return $this->actingAs($this->userByUsername('kaprodi'));
    }

    public function test_dashboard_bawaannya_hanya_periode_berjalan(): void
    {
        $this->taruh('3.34.23.2.01', $this->gasal);
        $this->taruh('3.34.23.2.02', $this->genap);

        $this->kaprodi()->get(route('kaprodi.dashboard'))
            ->assertOk()
            ->assertViewHas('periode', (string) $this->gasal->id)
            ->assertViewHas('totalMahasiswa', 1);
    }

    public function test_dashboard_semua_periode_menghitung_seluruhnya(): void
    {
        $this->taruh('3.34.23.2.01', $this->gasal);
        $this->taruh('3.34.23.2.02', $this->genap);

        $this->kaprodi()->get(route('kaprodi.dashboard', ['periode' => 'semua']))
            ->assertOk()
            ->assertViewHas('totalMahasiswa', 2);
    }

    public function test_daftar_mahasiswa_bawaannya_hanya_periode_berjalan(): void
    {
        $diGasal = $this->taruh('3.34.23.2.01', $this->gasal);
        $diGenap = $this->taruh('3.34.23.2.02', $this->genap);

        $daftar = $this->kaprodi()
            ->get(route('kaprodi.mahasiswa.index', ['status' => 'semua']))
            ->assertOk()
            ->viewData('students');

        $this->assertTrue($daftar->contains($diGasal));
        $this->assertFalse($daftar->contains($diGenap), 'Angkatan lain tak boleh ikut tampil.');
    }

    public function test_memilih_periode_lain_menampilkan_angkatan_itu(): void
    {
        $diGenap = $this->taruh('3.34.23.2.02', $this->genap);
        $this->taruh('3.34.23.2.01', $this->gasal);

        $daftar = $this->kaprodi()
            ->get(route('kaprodi.mahasiswa.index', ['status' => 'semua', 'periode' => $this->genap->id]))
            ->viewData('students');

        $this->assertTrue($daftar->contains($diGenap));
        $this->assertCount(1, $daftar);
    }

    /**
     * Mahasiswa yang lahir dari jalur yang belum menetapkan periode berkumpul di
     * "Tanpa periode". Tanpa wadah ini mereka hilang dari pandangan Kaprodi.
     */
    public function test_tanpa_periode_menampung_yang_belum_ditempatkan(): void
    {
        $lepas = $this->taruh('3.34.23.2.01', null);
        $this->taruh('3.34.23.2.02', $this->gasal);

        $daftar = $this->kaprodi()
            ->get(route('kaprodi.mahasiswa.index', ['status' => 'semua', 'periode' => 'tanpa']))
            ->viewData('students');

        $this->assertTrue($daftar->contains($lepas));
        $this->assertCount(1, $daftar);
    }

    public function test_jumlah_tanpa_periode_dilaporkan_ke_tampilan(): void
    {
        // Ketiga fixture ditempatkan eksplisit supaya angkanya pasti — fixture
        // lahir tanpa periode, jadi yang tak disentuh ikut terhitung.
        $this->taruh('3.34.23.2.01', null);
        $this->taruh('3.34.23.2.02', $this->gasal);
        $this->taruh('3.34.23.2.03', $this->genap);

        $this->kaprodi()->get(route('kaprodi.mahasiswa.index'))
            ->assertViewHas('tanpaPeriode', 1);
    }

    /**
     * Pendaftar baru belum diterima ke angkatan mana pun, jadi menyaringnya akan
     * menyembunyikan justru orang yang menunggu ditindak Kaprodi.
     */
    public function test_tab_menunggu_tidak_ikut_disaring_periode(): void
    {
        $pending = $this->taruh('3.34.23.2.03', $this->genap);

        $daftar = $this->kaprodi()
            ->get(route('kaprodi.mahasiswa.index', ['status' => 'pending']))
            ->assertViewHas('disaring', false)
            ->viewData('students');

        $this->assertTrue(
            $daftar->contains($pending),
            'Pendaftar dari periode lain tetap harus terlihat.'
        );
    }

    public function test_angka_tab_mengikuti_periode_terpilih(): void
    {
        $this->taruh('3.34.23.2.01', $this->gasal);
        $this->taruh('3.34.23.2.02', $this->genap);

        $counts = $this->kaprodi()
            ->get(route('kaprodi.mahasiswa.index', ['status' => 'semua']))
            ->viewData('counts');

        $this->assertSame(1, $counts['semua'], 'Angka tab harus menjawab pertanyaan yang sama dengan daftarnya.');
    }

    public function test_dropdown_memuat_periode_terbaru_lebih_dulu(): void
    {
        $list = $this->kaprodi()->get(route('kaprodi.dashboard'))->viewData('periodeList');

        $this->assertSame(
            ['2026/2027 Gasal', '2025/2026 Genap'],
            $list->map->label->all()
        );
    }

    public function test_ekspor_excel_mengikuti_periode_terpilih(): void
    {
        $this->taruh('3.34.23.2.01', $this->gasal);

        $this->kaprodi()
            ->get(route('kaprodi.dashboard.export-excel', ['periode' => $this->gasal->id]))
            ->assertOk()
            ->assertDownload();
    }

    /** Tanpa periode aktif, jangan menyaring apa pun — halaman kosong tanpa penjelasan lebih buruk. */
    public function test_tanpa_periode_aktif_daftar_tidak_dipersempit(): void
    {
        $this->gasal->update(['is_active' => false]);

        $this->taruh('3.34.23.2.01', $this->gasal);
        $this->taruh('3.34.23.2.02', $this->genap);

        $this->kaprodi()->get(route('kaprodi.dashboard'))
            ->assertViewHas('periode', Period::PILIHAN_SEMUA)
            ->assertViewHas('totalMahasiswa', 2);
    }
}
