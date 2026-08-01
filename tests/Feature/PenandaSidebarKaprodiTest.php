<?php

namespace Tests\Feature;

use App\Models\CompanyRequest;
use App\Models\Student;
use Tests\FeatureTestCase;

/**
 * Sidebar kaprodi harus memberi tahu apa yang menunggu keputusannya. Sebelumnya
 * hanya "Pengajuan Magang" yang punya penanda, sehingga mahasiswa yang baru
 * mendaftar bisa menunggu berhari-hari tanpa kaprodi sadar — akun mereka belum
 * bisa dipakai masuk sampai disetujui.
 *
 * Angkanya sengaja dihitung sama persis dengan tab tujuannya, supaya tidak
 * membingungkan: klik menunya, jumlah yang tampil harus sama.
 */
class PenandaSidebarKaprodiTest extends FeatureTestCase
{
    /** Fixture punya 1 mahasiswa pending (3.34.23.2.03). */
    public function test_penanda_data_mahasiswa_muncul_saat_ada_pendaftar(): void
    {
        $this->assertSame(1, Student::where('status', 'pending')->count());

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get('/kaprodi/dashboard')
            ->assertOk()
            ->assertSee('mahasiswa baru mendaftar dan menunggu persetujuan akun');
    }

    public function test_penanda_hilang_saat_tak_ada_pendaftar(): void
    {
        Student::where('status', 'pending')->update(['status' => 'active']);

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get('/kaprodi/dashboard')
            ->assertOk()
            ->assertDontSee('mahasiswa baru mendaftar dan menunggu persetujuan akun');
    }

    /** Angka penanda = jumlah di tab "Menunggu" halaman Data Mahasiswa. */
    public function test_angka_penanda_sama_dengan_tab_menunggu(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.02')->student;
        $mhs->update(['status' => 'pending']);

        $kaprodi = $this->userByUsername('kaprodi');

        $this->actingAs($kaprodi)->get('/kaprodi/dashboard')
            ->assertOk()
            ->assertSee('2 mahasiswa baru mendaftar dan menunggu persetujuan akun');

        $this->actingAs($kaprodi)->get('/kaprodi/mahasiswa?status=pending')
            ->assertOk()
            ->assertSee('Menunggu (2)');
    }

    /** Penanda pengajuan akun industri tetap jalan setelah dipindah ke .nav-badge. */
    public function test_penanda_pengajuan_magang_masih_jalan(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');

        $this->actingAs($kaprodi)->get('/kaprodi/dashboard')
            ->assertOk()
            ->assertDontSee('pengajuan akun industri menunggu review');

        CompanyRequest::create([
            'student_id'   => $this->userByUsername('3.34.23.2.01')->student->id,
            'company_name' => 'PT Uji Penanda',
            'pic_name'     => 'Budi',
            'status'       => 'pending',
        ]);

        $this->actingAs($kaprodi)->get('/kaprodi/dashboard')
            ->assertOk()
            ->assertSee('1 pengajuan akun industri menunggu review');
    }
}
