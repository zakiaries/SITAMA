<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

/**
 * Pembimbing industri yang dipilih di dropdown masih terpasang setelah
 * pengajuan magang terkirim, padahal seharusnya kembali ke "— Pembimbing baru —".
 *
 * Penyebabnya bukan di sisi server: form hanya dirender saat mahasiswa memang
 * boleh mengajukan, dan old() cuma terisi bila validasi gagal. Yang memasang
 * ulang pilihannya adalah peramban sendiri — Chrome/Edge/Firefox memulihkan isi
 * kontrol form saat halaman dimuat ulang atau dibuka lewat tombol Kembali,
 * kecuali form-nya memakai autocomplete="off".
 *
 * Tes ini menjaga keduanya: atribut yang mematikan pemulihan itu, dan perilaku
 * server yang memang sudah benar.
 */
class FormMagangTakMenyisakanPilihanTest extends FeatureTestCase
{
    /** Mahasiswa aktif, sudah punya dospem, belum punya magang. */
    private function pengaju()
    {
        return $this->userByUsername('3.34.23.2.02');
    }

    private function isian(array $ganti = []): array
    {
        return array_merge([
            'company_name' => 'PT Baru Sejahtera',
            'pic_name'     => 'Budi Pembimbing',
            'start_date'   => now()->toDateString(),
            'end_date'     => now()->addMonths(3)->toDateString(),
            'proof_file'   => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ], $ganti);
    }

    // ── Pemulihan isian oleh peramban dimatikan ─────────────────────────────

    public function test_form_ajukan_magang_mematikan_pemulihan_isian_peramban(): void
    {
        $html = $this->actingAs($this->pengaju())->get('/mahasiswa/ajukan-magang')
            ->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<form[^>]*ajukan-magang[^>]*autocomplete="off"/',
            $html,
            'Form Ajukan Magang tak memakai autocomplete="off", peramban akan memasang ulang pilihan lama.'
        );
    }

    public function test_form_catat_magang_kaprodi_mematikan_pemulihan_isian_peramban(): void
    {
        $student = $this->pengaju()->student;

        $html = $this->actingAs($this->userByUsername('kaprodi'))
            ->get("/kaprodi/mahasiswa/{$student->id}")
            ->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<form[^>]*internship[^>]*autocomplete="off"/',
            $html,
            'Form Catat Magang Kaprodi tak memakai autocomplete="off".'
        );
    }

    // ── Perilaku server: dropdown tak pernah membawa pilihan lama ───────────

    public function test_dropdown_kosong_saat_form_pertama_kali_dibuka(): void
    {
        // Pastikan dropdown-nya memang ada isinya, supaya tesnya bermakna.
        $this->assertTrue(
            Lecturer::whereHas('user', fn ($u) => $u->where('role', 'lecturer_industry'))->exists(),
            'Fixture tak punya pembimbing industri, dropdown-nya tak akan dirender.'
        );

        $html = $this->actingAs($this->pengaju())->get('/mahasiswa/ajukan-magang')
            ->assertOk()->getContent();

        $this->assertStringContainsString('name="lecturer_industry_id"', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/<option value="\d+"[^>]*selected/',
            $html,
            'Ada pembimbing yang sudah terpilih padahal form baru dibuka.'
        );
    }

    public function test_form_hilang_setelah_pengajuan_terkirim(): void
    {
        Storage::fake('local');

        $mahasiswa = $this->pengaju();
        $industri  = Lecturer::whereHas('user', fn ($u) => $u->where('role', 'lecturer_industry'))->first();

        $this->actingAs($mahasiswa)->post('/mahasiswa/ajukan-magang', $this->isian([
            'lecturer_industry_id' => $industri->id,
            'pic_name'             => null,
        ]))->assertRedirect();

        $this->actingAs($mahasiswa)->get('/mahasiswa/ajukan-magang')->assertOk()
            ->assertDontSee('name="lecturer_industry_id"', false)
            ->assertSee('Pengajuan sedang diproses');
    }

    /** Setelah pengajuan dibatalkan, form terbuka lagi dalam keadaan bersih. */
    public function test_dropdown_kembali_kosong_setelah_pengajuan_dibatalkan(): void
    {
        Storage::fake('local');

        $mahasiswa = $this->pengaju();
        $industri  = Lecturer::whereHas('user', fn ($u) => $u->where('role', 'lecturer_industry'))->first();

        $this->actingAs($mahasiswa)->post('/mahasiswa/ajukan-magang', $this->isian([
            'lecturer_industry_id' => $industri->id,
            'pic_name'             => null,
        ]))->assertRedirect();

        $pengajuan = \App\Models\CompanyRequest::where('student_id', $mahasiswa->student->id)->firstOrFail();

        $this->actingAs($mahasiswa)
            ->delete(route('mahasiswa.ajukan-magang.cancel', $pengajuan->id))
            ->assertRedirect(route('mahasiswa.ajukan-magang'));

        $html = $this->actingAs($mahasiswa)->get('/mahasiswa/ajukan-magang')
            ->assertOk()->getContent();

        $this->assertStringContainsString('name="lecturer_industry_id"', $html,
            'Form tak muncul kembali setelah pengajuan dibatalkan.');
        $this->assertDoesNotMatchRegularExpression(
            '/<option value="\d+"[^>]*selected/',
            $html,
            'Pembimbing yang dipilih di pengajuan sebelumnya masih terpasang di dropdown.'
        );
    }

    /** Saat validasi gagal, pilihan justru HARUS dipertahankan. */
    public function test_pilihan_dipertahankan_saat_validasi_gagal(): void
    {
        $mahasiswa = $this->pengaju();
        $industri  = Lecturer::whereHas('user', fn ($u) => $u->where('role', 'lecturer_industry'))->first();

        // Tanpa bukti penerimaan → validasi gagal.
        $this->actingAs($mahasiswa)
            ->from('/mahasiswa/ajukan-magang')
            ->post('/mahasiswa/ajukan-magang', [
                'company_name'         => 'PT Baru Sejahtera',
                'lecturer_industry_id' => $industri->id,
                'start_date'           => now()->toDateString(),
                'end_date'             => now()->addMonths(3)->toDateString(),
            ])
            ->assertSessionHasErrors('proof_file');

        $html = $this->actingAs($mahasiswa)->get('/mahasiswa/ajukan-magang')
            ->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<option value="' . $industri->id . '"[^>]*selected/',
            $html,
            'Pilihan pembimbing hilang setelah validasi gagal — mahasiswa harus memilih ulang.'
        );
    }
}
