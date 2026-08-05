<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\Period;
use App\Models\Student;
use Tests\FeatureTestCase;

/**
 * Penyaring periode di portal dosen kampus & pembimbing industri.
 *
 * Bedanya dengan portal Kaprodi: daftar periodenya hanya yang pembimbing itu
 * memang punya bimbingan di dalamnya. Pembimbing industri yang dipakai
 * perusahaan yang sama tiap tahun akan menumpuk bimbingan lintas angkatan,
 * sampai tak jelas lagi siapa yang sedang ia bimbing sekarang.
 */
class PeriodeFilterPembimbingTest extends FeatureTestCase
{
    private Period $baru;
    private Period $lama;

    protected function setUp(): void
    {
        parent::setUp();

        $this->baru = Period::create([
            'academic_year' => '2026/2027', 'semester' => 'gasal',
            'start_date' => '2026-08-01', 'end_date' => '2027-01-31',
            'duration_months' => 5, 'study_programs' => ['Teknologi Rekayasa Komputer'],
            'is_active' => true,
        ]);

        $this->lama = Period::create([
            'academic_year' => '2025/2026', 'semester' => 'genap',
            'start_date' => '2026-02-01', 'end_date' => '2026-07-31',
            'duration_months' => 5, 'study_programs' => ['Teknik Informatika'],
        ]);
    }

    private function taruh(string $nim, Period $periode): Student
    {
        $mahasiswa = $this->userByUsername($nim)->student;
        $mahasiswa->update(['period_id' => $periode->id]);

        return $mahasiswa;
    }

    public function test_dosen_bawaannya_hanya_melihat_periode_berjalan(): void
    {
        $sekarang = $this->taruh('3.34.23.2.01', $this->baru);
        $dulu     = $this->taruh('3.34.23.2.02', $this->lama);

        $daftar = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.dashboard'))
            ->assertOk()
            ->viewData('students');

        $this->assertTrue($daftar->contains($sekarang));
        $this->assertFalse($daftar->contains($dulu), 'Angkatan lama tak boleh ikut tampil.');
    }

    public function test_dosen_bisa_menengok_angkatan_lama(): void
    {
        $this->taruh('3.34.23.2.01', $this->baru);
        $dulu = $this->taruh('3.34.23.2.02', $this->lama);

        $daftar = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.dashboard', ['periode' => $this->lama->id]))
            ->viewData('students');

        $this->assertTrue($daftar->contains($dulu));
    }

    /** Dropdown hanya memuat periode tempat dosen ini benar-benar membimbing. */
    public function test_daftar_periode_dosen_hanya_yang_ada_bimbingannya(): void
    {
        $this->taruh('3.34.23.2.01', $this->lama);
        $this->taruh('3.34.23.2.02', $this->lama);
        $this->taruh('3.34.23.2.03', $this->lama);

        $list = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.dashboard'))
            ->viewData('periodeList');

        $this->assertSame(['2025/2026 Genap'], $list->map->label->all());
    }

    /**
     * Dosen yang giliran prodinya belum tiba tak boleh disuguhi layar kosong —
     * bimbingannya ada, hanya di periode sebelumnya.
     */
    public function test_dosen_tanpa_bimbingan_di_periode_berjalan_dialihkan_ke_periodenya(): void
    {
        $dulu = $this->taruh('3.34.23.2.01', $this->lama);

        $jawaban = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.dashboard'))
            ->assertViewHas('periode', (string) $this->lama->id);

        $this->assertTrue($jawaban->viewData('students')->contains($dulu));
    }

    public function test_angka_tab_dosen_mengikuti_periode(): void
    {
        $this->taruh('3.34.23.2.01', $this->baru);
        $this->taruh('3.34.23.2.02', $this->lama);
        $this->taruh('3.34.23.2.03', $this->lama);

        $counts = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.dashboard', ['periode' => $this->baru->id]))
            ->viewData('counts');

        $this->assertSame(1, $counts['semua']);
    }

    public function test_pembimbing_industri_menyaring_bimbingannya_per_periode(): void
    {
        $sekarang = $this->taruh('3.34.23.2.01', $this->baru);

        // Mahasiswa kedua dibimbing industri yang sama, tapi angkatan lalu.
        $dulu = $this->taruh('3.34.23.2.02', $this->lama);
        Internship::create([
            'student_id'           => $dulu->id,
            'lecturer_id'          => $sekarang->lecturer_id,
            'company_id'           => $sekarang->internships()->first()->company_id,
            'lecturer_industry_id' => $sekarang->internships()->first()->lecturer_industry_id,
            'position'             => 'Developer',
            'start_date'           => '2026-02-01',
            'end_date'             => '2026-07-01',
        ]);

        $industri = $this->userByUsername('industri1');

        $daftar = $this->actingAs($industri)
            ->get(route('dosen-industri.dashboard'))
            ->assertOk()
            ->viewData('students');

        $this->assertTrue($daftar->contains($sekarang));
        $this->assertFalse($daftar->contains($dulu), 'Bimbingan angkatan lalu tak boleh menumpuk di layar.');

        $lama = $this->actingAs($industri)
            ->get(route('dosen-industri.dashboard', ['periode' => $this->lama->id]))
            ->viewData('students');

        $this->assertTrue($lama->contains($dulu), 'Angkatan lalu tetap bisa ditengok.');
    }

    public function test_api_dosen_menyaring_per_periode(): void
    {
        $sekarang = $this->taruh('3.34.23.2.01', $this->baru);
        $this->taruh('3.34.23.2.02', $this->lama);

        $jawaban = $this->actingAs($this->userByUsername('dosen1'), 'sanctum')
            ->getJson('/api/dosen/dashboard')
            ->assertOk();

        $jawaban->assertJsonPath('counts.semua', 1);
        $this->assertSame(
            [$sekarang->user->name],
            collect($jawaban->json('students'))->pluck('name')->all()
        );
    }

    /** APK lama membaca `years`; ia dipertahankan (kosong) supaya tak pecah. */
    public function test_api_dosen_masih_mengirim_kunci_years(): void
    {
        $this->taruh('3.34.23.2.01', $this->baru);

        $this->actingAs($this->userByUsername('dosen1'), 'sanctum')
            ->getJson('/api/dosen/dashboard')
            ->assertOk()
            ->assertJsonStructure(['years', 'periods', 'periode']);
    }
}
