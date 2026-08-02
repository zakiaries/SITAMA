<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

/**
 * Mahasiswa melihat dospem dari students.lecturer_id (hasil plot Kaprodi),
 * sedangkan portal dosen dulu menyaring lewat internships.lecturer_id. Dua
 * sumber berbeda, jadi mahasiswa yang sudah diplot tapi magangnya belum
 * terbentuk tak pernah muncul di dosennya.
 *
 * Yang membuatnya berbahaya: mahasiswa dalam keadaan itu SUDAH boleh mengajukan
 * bimbingan (gerbangnya memang dospem, bukan magang), sehingga bimbingannya
 * masuk ke sistem tapi tak pernah sampai ke siapa pun.
 */
class MahasiswaDiplotTerlihatDosenTest extends FeatureTestCase
{
    /** Mahasiswa 02 punya dospem tapi belum punya magang sama sekali. */
    private function mahasiswaDiplotTanpaMagang(): Student
    {
        $student = $this->userByUsername('3.34.23.2.02')->student;

        $this->assertSame(0, $student->internships()->count(), 'Fixture berubah: seharusnya belum punya magang.');
        $this->assertNotNull($student->lecturer_id, 'Fixture berubah: seharusnya sudah diplot.');

        return $student;
    }

    public function test_muncul_di_dashboard_dosen(): void
    {
        $student = $this->mahasiswaDiplotTanpaMagang();

        $this->actingAs($this->userByUsername('dosen1'))
            ->get('/dosen/dashboard')
            ->assertOk()
            ->assertSee($student->user->name);
    }

    public function test_ikut_terhitung_di_tab_belum_dinilai(): void
    {
        $this->mahasiswaDiplotTanpaMagang();

        $this->actingAs($this->userByUsername('dosen1'))
            ->get('/dosen/dashboard?status=belum')
            ->assertOk()
            ->assertSee('Mahasiswa Dua');
    }

    /** Tautannya harus hidup: sebelumnya detail 404 karena mensyaratkan magang. */
    public function test_detail_bisa_dibuka_meski_belum_ada_magang(): void
    {
        $student = $this->mahasiswaDiplotTanpaMagang();

        $this->actingAs($this->userByUsername('dosen1'))
            ->get("/dosen/mahasiswa/{$student->id}")
            ->assertOk()
            ->assertSee('Belum magang')
            ->assertSee('data magangnya belum dibuat Kaprodi', false);
    }

    /** Inti masalahnya: bimbingan sebelum magang harus bisa ditanggapi. */
    public function test_dosen_bisa_menanggapi_bimbingan_sebelum_magang_ada(): void
    {
        Storage::fake('public');
        $mhs     = $this->userByUsername('3.34.23.2.02');
        $student = $this->mahasiswaDiplotTanpaMagang();

        $this->actingAs($mhs)->post('/mahasiswa/bimbingan', [
            'title' => 'Konsultasi awal', 'activity' => 'bahas rencana', 'date' => '2024-07-01',
        ])->assertSessionHasNoErrors();

        $g = Guidance::where('title', 'Konsultasi awal')->firstOrFail();

        $this->actingAs($this->userByUsername('dosen1'))
            ->post("/dosen/mahasiswa/{$student->id}/bimbingan/{$g->id}/approve", ['note' => 'Lanjutkan.'])
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $g->fresh()->status);
    }

    /** API mobile ikut, supaya dosen tak melihat daftar berbeda di HP. */
    public function test_api_dashboard_dosen_ikut_menampilkan(): void
    {
        $this->mahasiswaDiplotTanpaMagang();

        $data = $this->actingAs($this->userByUsername('dosen1'), 'sanctum')
            ->getJson('/api/dosen/dashboard')->assertOk()->json();

        $nama = collect($data['students'])->pluck('name');
        $this->assertContains('Mahasiswa Dua', $nama->all());
    }

    /**
     * Batasnya tetap. Mahasiswa 03 belum punya dospem maupun magang, jadi ia
     * bukan urusan dosen mana pun — pelonggaran ini tak boleh membuka semuanya.
     */
    public function test_mahasiswa_tanpa_dospem_tetap_tak_terlihat(): void
    {
        $lain = $this->userByUsername('3.34.23.2.03')->student;

        $this->assertNull($lain->lecturer_id, 'Fixture berubah: seharusnya belum punya dospem.');

        $this->actingAs($this->userByUsername('dosen1'))
            ->get('/dosen/dashboard')
            ->assertOk()
            ->assertDontSee($lain->user->name);

        $this->actingAs($this->userByUsername('dosen1'))
            ->get("/dosen/mahasiswa/{$lain->id}")
            ->assertNotFound();
    }
}
