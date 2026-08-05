<?php

namespace Tests\Feature;

use App\Models\Student;
use Tests\FeatureTestCase;

/**
 * Status "nonaktif" untuk mahasiswa yang sedang tidak menempuh studi.
 *
 * Sebelumnya mahasiswa yang cuti atau gap year tetap terhitung aktif: masuk
 * angka dashboard Kaprodi, masuk daftar bimbingan dosen, dan tetap bisa masuk
 * ke sistem seolah magangnya berjalan.
 */
class StatusNonaktifTest extends FeatureTestCase
{
    private function kaprodi()
    {
        return $this->actingAs($this->userByUsername('kaprodi'));
    }

    private function mahasiswaAktif(): Student
    {
        return $this->userByUsername('3.34.23.2.01')->student;
    }

    public function test_kaprodi_menonaktifkan_dengan_alasan(): void
    {
        $mahasiswa = $this->mahasiswaAktif();

        $this->kaprodi()->post(route('kaprodi.mahasiswa.status', $mahasiswa), [
            'status_note' => 'Cuti akademik semester ini',
        ])->assertRedirect();

        $mahasiswa->refresh();

        $this->assertTrue($mahasiswa->nonaktif());
        $this->assertSame('Cuti akademik semester ini', $mahasiswa->status_note);
        $this->assertNotNull($mahasiswa->status_changed_at);
    }

    /** Inti fiturnya: tanpa alasan, tak ada yang tahu kenapa setahun kemudian. */
    public function test_menonaktifkan_tanpa_alasan_ditolak(): void
    {
        $mahasiswa = $this->mahasiswaAktif();

        $this->kaprodi()->post(route('kaprodi.mahasiswa.status', $mahasiswa), [])
            ->assertSessionHasErrors('status_note');

        $this->assertFalse($mahasiswa->fresh()->nonaktif());
    }

    public function test_mengaktifkan_kembali_tanpa_perlu_alasan(): void
    {
        $mahasiswa = $this->mahasiswaAktif();
        $mahasiswa->update(['status' => Student::NONAKTIF, 'status_note' => 'Cuti']);

        $this->kaprodi()->post(route('kaprodi.mahasiswa.status', $mahasiswa), [])
            ->assertRedirect();

        $this->assertSame('active', $mahasiswa->fresh()->status);
    }

    /** Alasan lama tetap tersimpan sebagai jejak riwayat. */
    public function test_alasan_lama_tidak_terhapus_saat_diaktifkan(): void
    {
        $mahasiswa = $this->mahasiswaAktif();
        $mahasiswa->update(['status' => Student::NONAKTIF, 'status_note' => 'Cuti akademik']);

        $this->kaprodi()->post(route('kaprodi.mahasiswa.status', $mahasiswa), []);

        $this->assertSame('Cuti akademik', $mahasiswa->fresh()->status_note);
    }

    public function test_pendaftar_yang_masih_menunggu_tak_bisa_dinonaktifkan(): void
    {
        $pending = $this->userByUsername('3.34.23.2.03')->student;

        $this->kaprodi()->post(route('kaprodi.mahasiswa.status', $pending), [
            'status_note' => 'Coba-coba',
        ])->assertSessionHas('error');

        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_mahasiswa_nonaktif_tak_bisa_masuk_dan_diberi_alasannya(): void
    {
        $mahasiswa = $this->mahasiswaAktif();
        $mahasiswa->update(['status' => Student::NONAKTIF, 'status_note' => 'Cuti akademik semester ini']);

        $this->post(route('login'), [
            'username' => '3.34.23.2.01',
            'password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertStringContainsString(
            'Cuti akademik semester ini',
            session('errors')->first('username')
        );
    }

    public function test_mahasiswa_nonaktif_berhenti_terhitung_aktif_di_dashboard(): void
    {
        $sebelum = $this->kaprodi()->get(route('kaprodi.dashboard'))->viewData('totalMahasiswa');

        $this->mahasiswaAktif()->update(['status' => Student::NONAKTIF, 'status_note' => 'Cuti']);

        $sesudah = $this->kaprodi()->get(route('kaprodi.dashboard'))->viewData('totalMahasiswa');

        $this->assertSame($sebelum - 1, $sesudah);
    }

    public function test_tab_nonaktif_memuat_mereka(): void
    {
        $mahasiswa = $this->mahasiswaAktif();
        $mahasiswa->update(['status' => Student::NONAKTIF, 'status_note' => 'Cuti']);

        $daftar = $this->kaprodi()
            ->get(route('kaprodi.mahasiswa.index', ['status' => 'nonaktif', 'periode' => 'semua']))
            ->assertOk()
            ->viewData('students');

        $this->assertTrue($daftar->contains($mahasiswa));
    }

    public function test_mahasiswa_nonaktif_hilang_dari_daftar_bimbingan_dosen(): void
    {
        $mahasiswa = $this->mahasiswaAktif();

        $sebelum = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.dashboard', ['periode' => 'semua']))->viewData('students');
        $this->assertTrue($sebelum->contains($mahasiswa));

        $mahasiswa->update(['status' => Student::NONAKTIF, 'status_note' => 'Cuti']);

        $sesudah = $this->actingAs($this->userByUsername('dosen1'))
            ->get(route('dosen.dashboard', ['periode' => 'semua']))->viewData('students');

        $this->assertFalse($sesudah->contains($mahasiswa));
    }
}
