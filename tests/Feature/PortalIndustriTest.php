<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\LogBook;
use Tests\FeatureTestCase;

/**
 * Detail mahasiswa di portal pembimbing industri dulu "terlalu sepi": hanya
 * nama, NIM, posisi, tanggal, dan jumlah logbook. Pembimbing industri butuh
 * konteks untuk bekerja — kontak mahasiswa, data akademik, siapa dosen kampus
 * (untuk koordinasi), progres logbook terhadap minimum, dan status penilaian
 * yang jadi tugasnya sendiri.
 */
class PortalIndustriTest extends FeatureTestCase
{
    private function detailUrl(): string
    {
        $studentId = $this->userByUsername('3.34.23.2.01')->student->id;

        return "/dosen-industri/mahasiswa/{$studentId}";
    }

    public function test_detail_menampilkan_kontak_dan_data_akademik_mahasiswa(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($this->userByUsername('industri1'))
            ->get($this->detailUrl())
            ->assertOk()
            ->assertSee($mhs->email)                 // kontak mahasiswa
            ->assertSee('Teknik Informatika')        // program studi
            ->assertSee('TI-1A')                     // kelas
            ->assertSee('2023/2024');                // tahun akademik
    }

    public function test_detail_menampilkan_dosen_pembimbing_kampus(): void
    {
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($this->userByUsername('industri1'))
            ->get($this->detailUrl())
            ->assertOk()
            ->assertSee('Pembimbing Kampus')
            ->assertSee($dosen->name)
            ->assertSee($dosen->email);
    }

    public function test_detail_menampilkan_progres_logbook_terhadap_minimum(): void
    {
        $student = $this->userByUsername('3.34.23.2.01')->student;

        LogBook::create(['student_id' => $student->id, 'title' => 'Hari 1', 'activity' => 'a', 'date' => '2024-02-01']);
        LogBook::create(['student_id' => $student->id, 'title' => 'Hari 2', 'activity' => 'b', 'date' => '2024-02-02']);

        $this->actingAs($this->userByUsername('industri1'))
            ->get($this->detailUrl())
            ->assertOk()
            ->assertSee('2 / ' . Internship::MIN_LOGBOOK . ' minimum')
            ->assertSee('Logbook terakhir');
    }

    public function test_detail_menampilkan_status_penilaian_industri(): void
    {
        $this->actingAs($this->userByUsername('industri1'))
            ->get($this->detailUrl())
            ->assertOk()
            ->assertSee('Penilaian Anda')
            ->assertSee('Belum dinilai')
            ->assertSee('Beri Penilaian Akhir');
    }

    /** Pembimbing industri lain tidak boleh melihat mahasiswa yang bukan asuhannya. */
    public function test_industri_lain_tak_bisa_lihat_detail(): void
    {
        $lain = $this->userByUsername('industri1');
        $mhs2 = $this->userByUsername('3.34.23.2.02'); // tak punya magang

        $this->actingAs($lain)
            ->get("/dosen-industri/mahasiswa/{$mhs2->student->id}")
            ->assertNotFound();
    }
}
