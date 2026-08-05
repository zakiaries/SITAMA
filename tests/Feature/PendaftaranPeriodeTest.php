<?php

namespace Tests\Feature;

use App\Models\Period;
use App\Models\Student;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Menutup lubang di sumbernya: mahasiswa tak lagi mengetik tahun akademik &
 * prodinya sendiri.
 *
 * Dua nilai itulah yang dulu merusak penyaring Kaprodi — "2023/2026" sebagai
 * tahun akademik, dan satu prodi dengan dua ejaan. Yang diuji di sini: setiap
 * jalur pembuatan mahasiswa menempelkan periode berjalan, dan tak satu pun
 * jalur melahirkan mahasiswa tanpa periode saat periodenya ada.
 */
class PendaftaranPeriodeTest extends FeatureTestCase
{
    private Period $berjalan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->berjalan = Period::create([
            'academic_year' => '2026/2027', 'semester' => 'gasal',
            'start_date' => '2026-08-01', 'end_date' => '2027-01-31',
            'duration_months' => 5, 'study_programs' => ['Teknologi Rekayasa Komputer'],
            'is_active' => true,
        ]);
    }

    private function isian(array $ganti = []): array
    {
        return array_merge([
            'name'                  => 'Pendaftar Baru',
            'username'              => '4.33.24.1.07',
            'email'                 => 'pendaftar.baru@mhs.polines.ac.id',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'the_class'             => 'TRK-1A',
            'study_program'         => 'Teknologi Rekayasa Komputer',
            'major'                 => 'Teknik Elektro',
        ], $ganti);
    }

    public function test_pendaftar_web_langsung_masuk_periode_berjalan(): void
    {
        $this->post(route('register'), $this->isian())->assertRedirect();

        $mahasiswa = User::where('username', '4.33.24.1.07')->firstOrFail()->student;

        $this->assertSame($this->berjalan->id, $mahasiswa->period_id);
        $this->assertSame('2026/2027', $mahasiswa->academic_year, 'Tahun akademik ikut periode, bukan ketikan.');
    }

    public function test_prodi_di_luar_daftar_ditolak(): void
    {
        $this->post(route('register'), $this->isian(['study_program' => 'Teknik Rekayasa Komputer']))
            ->assertSessionHasErrors('study_program');

        $this->assertNull(User::where('username', '4.33.24.1.07')->first());
    }

    /** Tahun akademik yang dikirim manual harus diabaikan, bukan dipercaya. */
    public function test_tahun_akademik_kiriman_pendaftar_diabaikan(): void
    {
        $this->post(route('register'), $this->isian(['academic_year' => '2023/2026']))
            ->assertRedirect();

        $mahasiswa = User::where('username', '4.33.24.1.07')->firstOrFail()->student;

        $this->assertSame('2026/2027', $mahasiswa->academic_year);
    }

    public function test_formulir_menampilkan_periode_yang_akan_dimasuki(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('2026/2027 Gasal')
            ->assertDontSee('name="academic_year"', false);
    }

    public function test_pendaftar_mobile_juga_masuk_periode_berjalan(): void
    {
        $this->postJson('/api/register', $this->isian())->assertCreated();

        $mahasiswa = User::where('username', '4.33.24.1.07')->firstOrFail()->student;

        $this->assertSame($this->berjalan->id, $mahasiswa->period_id);
        $this->assertSame('2026/2027', $mahasiswa->academic_year);
    }

    public function test_mobile_menolak_prodi_di_luar_daftar(): void
    {
        $this->postJson('/api/register', $this->isian(['study_program' => 'Sastra Jawa']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('study_program');
    }

    public function test_perintah_dummy_ikut_periode_berjalan(): void
    {
        $this->artisan('simama:mahasiswa-dummy', ['--tahap' => 'sedang-magang'])->assertSuccessful();

        $tanpa = Student::whereNull('period_id')->whereHas('user', fn($q) => $q->where('role', 'student'))->count();

        $this->assertSame(
            3,
            $tanpa,
            'Hanya tiga fixture bawaan yang boleh tanpa periode; dummy baru harus ikut periode berjalan.'
        );
    }

    /**
     * Tanpa periode aktif, pendaftaran tetap harus jalan — menolak orang
     * mendaftar karena Kaprodi belum membuka periode akan menutup pintu masuk
     * sistem sepenuhnya.
     */
    public function test_tanpa_periode_aktif_pendaftaran_tetap_diterima(): void
    {
        $this->berjalan->update(['is_active' => false]);

        $this->post(route('register'), $this->isian())->assertRedirect();

        $mahasiswa = User::where('username', '4.33.24.1.07')->firstOrFail()->student;

        $this->assertNull($mahasiswa->period_id);
        $this->assertNull($mahasiswa->academic_year);
    }
}
