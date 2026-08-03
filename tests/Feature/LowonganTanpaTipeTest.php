<?php

namespace Tests\Feature;

use App\Models\JobListing;
use Tests\FeatureTestCase;

/**
 * Menyimpan lowongan tanpa mengisi "Tipe" membuat Kaprodi kena error 500.
 *
 * Kolom job_listings.job_type dibuat NOT NULL DEFAULT 'On-site', tapi form
 * Kaprodi memperlakukannya sebagai isian opsional dan listingPayload() selalu
 * menuliskan nilainya — sehingga isian kosong dikirim sebagai NULL ke kolom yang
 * menolak NULL. Kena di dua jalur sekaligus: tambah lowongan dan simpan
 * perubahan.
 */
class LowonganTanpaTipeTest extends FeatureTestCase
{
    private function isian(array $ganti = []): array
    {
        return array_merge([
            'company_name' => 'PT Uji Sejahtera',
            'title'        => 'Web Developer Intern',
        ], $ganti);
    }

    public function test_lowongan_bisa_ditambah_tanpa_tipe(): void
    {
        $this->actingAs($this->userByUsername('kaprodi'))
            ->post(route('kaprodi.lowongan.store'), $this->isian())
            ->assertRedirect(route('kaprodi.lowongan.index'));

        $lowongan = JobListing::where('title', 'Web Developer Intern')->firstOrFail();

        $this->assertNull($lowongan->job_type, 'Tipe yang dikosongkan tak boleh diisi sendiri oleh sistem.');
    }

    public function test_lowongan_bisa_disunting_tanpa_tipe(): void
    {
        $lowongan = JobListing::create($this->isian([
            'company_id' => \App\Models\Company::where('name', 'PT Uji Sejahtera')->firstOrFail()->id,
            'job_type'   => 'WFO',
            'status'     => 'active',
        ]));

        $this->actingAs($this->userByUsername('kaprodi'))
            ->put(route('kaprodi.lowongan.update', $lowongan), $this->isian([
                'title' => 'Backend Intern',
            ]))
            ->assertRedirect(route('kaprodi.lowongan.index'));

        $this->assertNull($lowongan->fresh()->job_type);
        $this->assertSame('Backend Intern', $lowongan->fresh()->title);
    }

    /** Yang diisi tetap tersimpan apa adanya. */
    public function test_tipe_yang_diisi_tetap_tersimpan(): void
    {
        $this->actingAs($this->userByUsername('kaprodi'))
            ->post(route('kaprodi.lowongan.store'), $this->isian(['job_type' => 'Hybrid']))
            ->assertRedirect(route('kaprodi.lowongan.index'));

        $this->assertSame('Hybrid', JobListing::where('title', 'Web Developer Intern')->firstOrFail()->job_type);
    }
}
