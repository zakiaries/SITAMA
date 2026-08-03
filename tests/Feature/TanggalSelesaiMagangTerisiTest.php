<?php

namespace Tests\Feature;

use App\Models\CompanyRequest;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\FeatureTestCase;

/**
 * internships.end_date ada sejak awal tapi tak pernah terisi di data sungguhan.
 * Ketiga jalur yang membuat magang — pengajuan mahasiswa yang disetujui Kaprodi
 * (dua cabang: pembimbing industri sudah terdaftar / dibuatkan baru) dan Catat
 * Magang manual — semuanya hanya menuliskan start_date. Hanya perintah simulasi
 * & akun dummy yang mengisinya.
 *
 * Akibatnya lima halaman (profil mahasiswa, dashboard, detail dosen, detail
 * industri, detail kaprodi) menampilkan "Belum selesai" selamanya, bahkan untuk
 * magang yang periodenya sudah lewat.
 *
 * Tanggal selesainya kini diminta sejak pengajuan dan ikut terbawa ke magang.
 */
class TanggalSelesaiMagangTerisiTest extends FeatureTestCase
{
    private const MULAI   = '2026-09-01';
    private const SELESAI = '2026-12-01';

    private function pengaju(): Student
    {
        return $this->userByUsername('3.34.23.2.02')->student;
    }

    private function isian(array $ganti = []): array
    {
        return array_merge([
            'company_name' => 'PT Baru Sejahtera',
            'pic_name'     => 'Budi Pembimbing',
            'start_date'   => self::MULAI,
            'end_date'     => self::SELESAI,
            'proof_file'   => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ], $ganti);
    }

    // ── Pengajuan menyimpan tanggal selesai ─────────────────────────────────

    public function test_pengajuan_menyimpan_tanggal_selesai(): void
    {
        Storage::fake('local');

        $this->actingAs($this->pengaju()->user)
            ->post('/mahasiswa/ajukan-magang', $this->isian())
            ->assertSessionHasNoErrors();

        $pengajuan = CompanyRequest::where('student_id', $this->pengaju()->id)->firstOrFail();

        $this->assertSame(self::SELESAI, $pengajuan->end_date->toDateString());
    }

    public function test_tanggal_selesai_wajib_diisi(): void
    {
        Storage::fake('local');

        $this->actingAs($this->pengaju()->user)
            ->post('/mahasiswa/ajukan-magang', $this->isian(['end_date' => null]))
            ->assertSessionHasErrors('end_date');
    }

    public function test_tanggal_selesai_harus_setelah_tanggal_mulai(): void
    {
        Storage::fake('local');

        $this->actingAs($this->pengaju()->user)
            ->post('/mahasiswa/ajukan-magang', $this->isian(['end_date' => '2026-08-01']))
            ->assertSessionHasErrors('end_date');
    }

    public function test_formulir_menampilkan_isian_tanggal_selesai(): void
    {
        $this->actingAs($this->pengaju()->user)
            ->get('/mahasiswa/ajukan-magang')->assertOk()
            ->assertSee('name="end_date"', false)
            ->assertSee('Tanggal Selesai');
    }

    // ── Terbawa ke magang saat Kaprodi menyetujui ───────────────────────────

    /** Cabang 1: pembimbing industri dipilih dari yang sudah terdaftar. */
    public function test_terbawa_ke_magang_lewat_pembimbing_terdaftar(): void
    {
        Storage::fake('local');

        $industri = Lecturer::whereHas('user', fn ($u) => $u->where('role', 'lecturer_industry'))->firstOrFail();

        $this->actingAs($this->pengaju()->user)->post('/mahasiswa/ajukan-magang', $this->isian([
            'lecturer_industry_id' => $industri->id,
            'pic_name'             => null,
        ]))->assertSessionHasNoErrors();

        $pengajuan = CompanyRequest::where('student_id', $this->pengaju()->id)->firstOrFail();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/pengajuan-magang/{$pengajuan->id}/approve")
            ->assertSessionHas('success');

        $magang = $this->pengaju()->internships()->latest('id')->firstOrFail();

        $this->assertSame(self::SELESAI, $magang->end_date?->toDateString(),
            'Tanggal selesai tidak ikut terbawa ke data magang.');
    }

    /** Cabang 2: pembimbing industri dibuatkan akun baru. */
    public function test_terbawa_ke_magang_lewat_pembimbing_baru(): void
    {
        Storage::fake('local');

        $this->actingAs($this->pengaju()->user)
            ->post('/mahasiswa/ajukan-magang', $this->isian(['pic_email' => 'pic.baru@contoh.com']))
            ->assertSessionHasNoErrors();

        $pengajuan = CompanyRequest::where('student_id', $this->pengaju()->id)->firstOrFail();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/pengajuan-magang/{$pengajuan->id}/approve")
            ->assertSessionHas('success');

        $magang = $this->pengaju()->internships()->latest('id')->firstOrFail();

        $this->assertSame(self::SELESAI, $magang->end_date?->toDateString());
    }

    /** Cabang 3: Kaprodi mencatat magang manual. */
    public function test_catat_magang_kaprodi_menyimpan_tanggal_selesai(): void
    {
        $student  = $this->pengaju();
        $industri = Lecturer::whereHas('user', fn ($u) => $u->where('role', 'lecturer_industry'))->firstOrFail();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$student->id}/internship", [
                'company_name'         => 'PT Catat Manual',
                'lecturer_industry_id' => $industri->id,
                'start_date'           => self::MULAI,
                'end_date'             => self::SELESAI,
            ])->assertSessionHas('success');

        $magang = $student->internships()->latest('id')->firstOrFail();

        $this->assertSame(self::SELESAI, $magang->end_date?->toDateString());
    }

    public function test_catat_magang_kaprodi_mewajibkan_tanggal_selesai(): void
    {
        $student = $this->pengaju();

        $this->actingAs($this->userByUsername('kaprodi'))
            ->post("/kaprodi/mahasiswa/{$student->id}/internship", [
                'company_name' => 'PT Catat Manual',
                'pic_name'     => 'Budi', 'pic_username' => 'budi.pic', 'pic_password' => 'rahasia123',
                'start_date'   => self::MULAI,
            ])->assertSessionHasErrors('end_date');
    }

    // ── Halaman tak lagi menulis "Belum selesai" ────────────────────────────

    public function test_profil_menampilkan_tanggal_bukan_belum_selesai(): void
    {
        $mahasiswa = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));
        $mahasiswa->student->internships()->latest('id')->first()->update(['end_date' => self::SELESAI]);

        $this->actingAs($mahasiswa)->get('/mahasiswa/profile')->assertOk()
            // Bulan dirender Carbon tanpa pelokalan ('Dec', bukan 'Des') — itu
            // ketidakkonsistenan tampilan tersendiri, di luar lingkup perbaikan ini.
            ->assertSee('01 Dec 2026')
            ->assertDontSee('Belum selesai');
    }

    // ── Paritas API mobile ──────────────────────────────────────────────────

    public function test_api_mewajibkan_tanggal_selesai(): void
    {
        Sanctum::actingAs($this->pengaju()->user);

        $this->postJson('/api/mahasiswa/ajukan-magang', [
            'company_name' => 'PT Baru Sejahtera',
            'pic_name'     => 'Budi Pembimbing',
            'start_date'   => self::MULAI,
            'proof_file'   => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertStatus(422)->assertJsonValidationErrors('end_date');
    }

    public function test_api_menyimpan_tanggal_selesai(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->pengaju()->user);

        $this->postJson('/api/mahasiswa/ajukan-magang', [
            'company_name' => 'PT Baru Sejahtera',
            'pic_name'     => 'Budi Pembimbing',
            'start_date'   => self::MULAI,
            'end_date'     => self::SELESAI,
            'proof_file'   => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertSuccessful();

        $this->assertSame(
            self::SELESAI,
            CompanyRequest::where('student_id', $this->pengaju()->id)->firstOrFail()->end_date->toDateString()
        );
    }
}
