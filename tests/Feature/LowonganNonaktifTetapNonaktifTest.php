<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobListing;
use Tests\FeatureTestCase;

/**
 * Menyunting lowongan yang sedang dinonaktifkan diam-diam menghidupkannya lagi.
 *
 * listingPayload() selalu menuliskan 'status' => 'active' karena dipakai
 * bersama store(), dan update() memakai muatan yang sama apa adanya. Jadi
 * Kaprodi yang cuma memperbaiki satu salah ketik pada lowongan nonaktif
 * mendapati lowongan itu kembali tampil ke mahasiswa — tanpa peringatan, tanpa
 * ada yang memintanya.
 *
 * Menyalakan dan mematikan lowongan sudah punya tombolnya sendiri (toggle);
 * form sunting tak berhak ikut memutuskan.
 */
class LowonganNonaktifTetapNonaktifTest extends FeatureTestCase
{
    private function lowongan(string $status): JobListing
    {
        return JobListing::create([
            'company_id'   => Company::firstOrCreate(
                ['name' => 'PT Uji Sejahtera'], ['verification_status' => 'verified']
            )->id,
            'company_name' => 'PT Uji Sejahtera',
            'title'        => 'Web Developer Intern',
            'status'       => $status,
        ]);
    }

    private function sunting(JobListing $lowongan, array $ganti = [])
    {
        return $this->actingAs($this->userByUsername('kaprodi'))
            ->put(route('kaprodi.lowongan.update', $lowongan), array_merge([
                'company_name' => 'PT Uji Sejahtera',
                'title'        => 'Web Developer Intern (revisi)',
            ], $ganti));
    }

    public function test_lowongan_nonaktif_tetap_nonaktif_setelah_disunting(): void
    {
        $lowongan = $this->lowongan('closed');

        $this->sunting($lowongan)->assertRedirect(route('kaprodi.lowongan.index'));

        $this->assertSame('closed', $lowongan->fresh()->status,
            'Lowongan nonaktif hidup lagi hanya karena disunting.');
    }

    public function test_suntingannya_tetap_tersimpan(): void
    {
        $lowongan = $this->lowongan('closed');

        $this->sunting($lowongan, ['location' => 'Semarang']);

        $segar = $lowongan->fresh();

        $this->assertSame('Web Developer Intern (revisi)', $segar->title);
        $this->assertSame('Semarang', $segar->location);
    }

    public function test_lowongan_aktif_tetap_aktif_setelah_disunting(): void
    {
        $lowongan = $this->lowongan('active');

        $this->sunting($lowongan);

        $this->assertSame('active', $lowongan->fresh()->status);
    }

    /** Lowongan baru tetap lahir dalam keadaan aktif. */
    public function test_lowongan_baru_tetap_aktif(): void
    {
        $this->actingAs($this->userByUsername('kaprodi'))
            ->post(route('kaprodi.lowongan.store'), [
                'company_name' => 'PT Uji Sejahtera',
                'title'        => 'Lowongan Baru',
            ])->assertRedirect(route('kaprodi.lowongan.index'));

        $this->assertSame('active', JobListing::where('title', 'Lowongan Baru')->firstOrFail()->status);
    }

    /** Tombol yang memang bertugas menyalakan/mematikan tetap bekerja. */
    public function test_tombol_toggle_tetap_bisa_mengubah_status(): void
    {
        $lowongan = $this->lowongan('closed');
        $kaprodi  = $this->userByUsername('kaprodi');

        $this->actingAs($kaprodi)->post(route('kaprodi.lowongan.toggle', $lowongan));
        $this->assertSame('active', $lowongan->fresh()->status);

        $this->actingAs($kaprodi)->post(route('kaprodi.lowongan.toggle', $lowongan));
        $this->assertSame('closed', $lowongan->fresh()->status);
    }

    /** Yang nonaktif tetap tak terlihat mahasiswa, juga setelah disunting. */
    public function test_lowongan_nonaktif_tak_muncul_di_daftar_mahasiswa(): void
    {
        $lowongan = $this->lowongan('closed');

        $this->sunting($lowongan);

        $this->actingAs($this->userByUsername('3.34.23.2.02'))
            ->get(route('mahasiswa.lowongan'))->assertOk()
            ->assertDontSee('Web Developer Intern (revisi)');
    }
}
