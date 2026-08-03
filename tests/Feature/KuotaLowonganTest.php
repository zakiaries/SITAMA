<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Internship;
use App\Models\JobListing;
use App\Models\Student;
use Tests\FeatureTestCase;

/**
 * Kolom quota sudah ada di tabel job_listings sejak awal dan terdaftar di
 * $fillable, tapi tak pernah punya jalan masuk: form Kaprodi tak memuatnya dan
 * listingPayload() tak pernah menulisnya. Jadi angkanya selalu 1 — nilai default
 * basis data, bukan keputusan siapa pun.
 *
 * Sekarang Kaprodi mengisinya sendiri, dan sistem menampilkan berapa tempat yang
 * terpakai beserta penanda "Penuh". SENGAJA tidak memblokir apa pun: SIMAMA tak
 * punya alur melamar lowongan — mahasiswa mencari magang sendiri lalu mengunggah
 * bukti penerimaan — sehingga sistem tak berhak memutuskan sebuah tempat sudah
 * habis. Terisi pun hanya bisa dihitung per perusahaan, bukan per lowongan.
 */
class KuotaLowonganTest extends FeatureTestCase
{
    private function perusahaan(): Company
    {
        return Company::where('name', 'PT Uji Sejahtera')->firstOrFail();
    }

    private function lowongan(array $ganti = []): JobListing
    {
        return JobListing::create(array_merge([
            'company_id'   => $this->perusahaan()->id,
            'company_name' => 'PT Uji Sejahtera',
            'title'        => 'Web Developer Intern',
            'status'       => 'active',
        ], $ganti));
    }

    /** Tambah magang BERJALAN di perusahaan fixture. */
    private function isiMagangBerjalan(int $jumlah): void
    {
        foreach (range(1, $jumlah) as $i) {
            $student = Student::create([
                'user_id' => \App\Models\User::create([
                    'name' => "Peserta {$i}", 'username' => "9.99.99.9.0{$i}",
                    'email' => "peserta{$i}@test.ac.id", 'password' => bcrypt('rahasia123'),
                    'role' => 'student',
                ])->id,
                'the_class' => 'TI-1A', 'study_program' => 'Teknik Informatika',
                'major' => 'Informatika', 'academic_year' => '2023/2024', 'status' => 'active',
            ]);

            Internship::create([
                'student_id' => $student->id,
                'company_id' => $this->perusahaan()->id,
                'start_date' => now()->subMonth(),
                'is_finished' => false,
            ]);
        }
    }

    // ── Kaprodi mengisi kuota ───────────────────────────────────────────────

    public function test_form_kaprodi_punya_isian_kuota(): void
    {
        $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.lowongan.create'))->assertOk()
            ->assertSee('name="quota"', false)
            ->assertSee('Kuota');
    }

    public function test_kaprodi_bisa_menyimpan_kuota(): void
    {
        $this->actingAs($this->userByUsername('kaprodi'))
            ->post(route('kaprodi.lowongan.store'), [
                'company_name' => 'PT Uji Sejahtera',
                'title'        => 'Backend Intern',
                'quota'        => 3,
            ])->assertRedirect(route('kaprodi.lowongan.index'));

        $this->assertSame(3, JobListing::where('title', 'Backend Intern')->firstOrFail()->quota);
    }

    public function test_kuota_boleh_dikosongkan(): void
    {
        $this->actingAs($this->userByUsername('kaprodi'))
            ->post(route('kaprodi.lowongan.store'), [
                'company_name' => 'PT Uji Sejahtera',
                'title'        => 'Tanpa Batas',
            ])->assertRedirect(route('kaprodi.lowongan.index'));

        $lowongan = JobListing::where('title', 'Tanpa Batas')->firstOrFail();

        $this->assertNull($lowongan->quota, 'Kuota kosong tak boleh jatuh ke default lama (1).');
        $this->assertFalse($lowongan->punyaKuota());
        $this->assertNull($lowongan->ringkasanKuota());
    }

    public function test_kuota_tak_masuk_akal_ditolak(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');

        foreach ([0, -2, 1000, 'tiga'] as $nilai) {
            $this->actingAs($kaprodi)
                ->post(route('kaprodi.lowongan.store'), [
                    'company_name' => 'PT Uji Sejahtera',
                    'title'        => 'Uji Kuota',
                    'quota'        => $nilai,
                ])->assertSessionHasErrors('quota');
        }

        $this->assertSame(0, JobListing::where('title', 'Uji Kuota')->count());
    }

    public function test_kaprodi_bisa_mengubah_kuota(): void
    {
        $lowongan = $this->lowongan(['quota' => 2]);

        $this->actingAs($this->userByUsername('kaprodi'))
            ->put(route('kaprodi.lowongan.update', $lowongan), [
                'company_name' => 'PT Uji Sejahtera',
                'title'        => 'Web Developer Intern',
                'quota'        => 5,
            ])->assertRedirect(route('kaprodi.lowongan.index'));

        $this->assertSame(5, $lowongan->fresh()->quota);
    }

    // ── Hitungan terisi ─────────────────────────────────────────────────────

    public function test_terisi_menghitung_magang_yang_sedang_berjalan(): void
    {
        $lowongan = $this->lowongan(['quota' => 3]);

        // Fixture sudah punya satu magang di perusahaan ini, tapi SUDAH SELESAI —
        // tempatnya kembali kosong, jadi tak ikut dihitung.
        $this->assertSame(0, $lowongan->jumlahTerisi());

        $this->isiMagangBerjalan(2);

        $this->assertSame(2, $lowongan->fresh()->jumlahTerisi());
        $this->assertFalse($lowongan->fresh()->penuh());
        $this->assertSame('2 dari 3 terisi', $lowongan->fresh()->ringkasanKuota());
    }

    public function test_penuh_saat_terisi_mencapai_kuota(): void
    {
        $lowongan = $this->lowongan(['quota' => 2]);
        $this->isiMagangBerjalan(2);

        $this->assertTrue($lowongan->fresh()->penuh());
    }

    public function test_tanpa_kuota_tak_pernah_dianggap_penuh(): void
    {
        $lowongan = $this->lowongan(['quota' => null]);
        $this->isiMagangBerjalan(3);

        $this->assertFalse($lowongan->fresh()->penuh());
    }

    // ── Tampilan ────────────────────────────────────────────────────────────

    public function test_daftar_kaprodi_menampilkan_terisi_dan_penanda_penuh(): void
    {
        $this->lowongan(['quota' => 2]);
        $this->isiMagangBerjalan(2);

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.lowongan.index'))->assertOk()
            ->assertSee('2 / 2')
            ->assertSee('Penuh');
    }

    public function test_daftar_kaprodi_menampilkan_strip_bila_kuota_kosong(): void
    {
        $this->lowongan(['quota' => null]);

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.lowongan.index'))->assertOk()
            ->assertSee('Kuota tidak dibatasi')
            ->assertDontSee('Penuh');
    }

    public function test_mahasiswa_melihat_ringkasan_kuota(): void
    {
        $this->lowongan(['quota' => 3]);
        $this->isiMagangBerjalan(1);

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get(route('mahasiswa.lowongan'))->assertOk()
            ->assertSee('1 dari 3 terisi');
    }

    public function test_mahasiswa_melihat_penanda_penuh(): void
    {
        $lowongan = $this->lowongan(['quota' => 1]);
        $this->isiMagangBerjalan(1);

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get(route('mahasiswa.lowongan.detail', $lowongan))->assertOk()
            ->assertSee('Penuh');
    }

    /** Inti keputusannya: penuh hanya penanda, bukan pembatas. */
    public function test_lowongan_penuh_tetap_bisa_diajukan(): void
    {
        $lowongan = $this->lowongan(['quota' => 1]);
        $this->isiMagangBerjalan(1);

        // Tombol pengajuan tetap ada di halaman detail.
        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get(route('mahasiswa.lowongan.detail', $lowongan))->assertOk()
            ->assertSee('Ajukan Magang di Perusahaan Ini');

        // Dan pengajuannya benar-benar diterima.
        \Illuminate\Support\Facades\Storage::fake('local');

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->post('/mahasiswa/ajukan-magang', [
                'company_id'  => $this->perusahaan()->id,
                'pic_name'    => 'Budi Pembimbing',
                'start_date'  => now()->toDateString(),
                'end_date'    => now()->addMonths(3)->toDateString(),
                'proof_file'  => \Illuminate\Http\UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('company_requests', ['company_id' => $this->perusahaan()->id]);
    }
}
