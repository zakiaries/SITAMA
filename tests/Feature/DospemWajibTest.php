<?php

namespace Tests\Feature;

use App\Models\CompanyRequest;
use App\Models\Internship;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

/**
 * Magang tak boleh terbentuk tanpa dosen pembimbing.
 *
 * Portal dosen memfilter mahasiswa lewat internships.lecturer_id. Kalau magang
 * lahir dengan kolom itu NULL (mahasiswa belum diplot dospem), mahasiswanya
 * hilang dari portal dosen tanpa error apa pun — logbook & bimbingannya ikut
 * tak terlihat. Di alur nyata Kaprodi memplot dospem sebelum mahasiswa mencari
 * magang, karena dospem itulah yang membimbing proposal.
 */
class DospemWajibTest extends FeatureTestCase
{
    /** Mahasiswa tanpa dospem: form ajukan magang tak muncul & POST ditolak. */
    public function test_mahasiswa_tanpa_dospem_tak_bisa_ajukan_magang(): void
    {
        Storage::fake('public');
        $u = $this->userByUsername('3.34.23.2.02'); // aktif, belum punya magang
        $u->student->update(['lecturer_id' => null]);

        $this->actingAs($u)->get('/mahasiswa/ajukan-magang')
            ->assertOk()
            ->assertSee('Dosen pembimbing belum ditetapkan')
            ->assertDontSee('Kirim Pengajuan');

        $this->from('/mahasiswa/ajukan-magang')->actingAs($u)->post('/mahasiswa/ajukan-magang', [
            'company_name' => 'PT Tanpa Dospem', 'pic_name' => 'Budi', 'pic_phone' => '08123456789',
            'company_mode' => 'new', 'start_date' => '2024-08-01', 'end_date' => '2024-11-01',
            'proof_file' => UploadedFile::fake()->create('b.pdf', 20, 'application/pdf'),
        ])->assertSessionHas('error');

        $this->assertSame(0, CompanyRequest::where('student_id', $u->student->id)->count());
    }

    /** Dengan dospem, pengajuan yang sama berhasil. */
    public function test_mahasiswa_dengan_dospem_bisa_ajukan_magang(): void
    {
        Storage::fake('public');
        $u = $this->userByUsername('3.34.23.2.02');
        $this->assertNotNull($u->student->lecturer_id);

        $this->from('/mahasiswa/ajukan-magang')->actingAs($u)->post('/mahasiswa/ajukan-magang', [
            'company_name' => 'PT Dengan Dospem', 'pic_name' => 'Budi', 'pic_phone' => '08123456789',
            'company_mode' => 'new', 'start_date' => '2024-08-01', 'end_date' => '2024-11-01',
            'proof_file' => UploadedFile::fake()->create('b.pdf', 20, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, CompanyRequest::where('student_id', $u->student->id)->count());
    }

    /** Kaprodi tak bisa menyetujui pengajuan mahasiswa yang belum diplot dospem. */
    public function test_kaprodi_tak_bisa_approve_magang_tanpa_dospem(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.02');
        $student = $mhs->student;

        $req = CompanyRequest::create([
            'student_id' => $student->id, 'company_name' => 'PT Uji', 'pic_name' => 'Budi',
            'pic_phone' => '08123456789', 'start_date' => '2024-08-01', 'status' => 'pending',
        ]);

        $student->update(['lecturer_id' => null]);

        $kaprodi = $this->userByUsername('kaprodi');
        $this->from('/kaprodi/pengajuan-magang')->actingAs($kaprodi)
            ->post("/kaprodi/pengajuan-magang/{$req->id}/approve")
            ->assertSessionHas('error');

        $this->assertSame('pending', $req->fresh()->status);
        $this->assertSame(0, Internship::where('student_id', $student->id)->count());
    }

    /** Kaprodi tak bisa mencatat magang langsung tanpa dospem. */
    public function test_kaprodi_tak_bisa_catat_magang_tanpa_dospem(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.02');
        $student = $mhs->student;
        $student->update(['lecturer_id' => null]);

        $kaprodi = $this->userByUsername('kaprodi');
        $this->from("/kaprodi/mahasiswa/{$student->id}")->actingAs($kaprodi)
            ->post("/kaprodi/mahasiswa/{$student->id}/internship", [
                'company_name' => 'PT Catat', 'pic_name' => 'Budi', 'pic_username' => 'pic_baru',
                'pic_password' => 'rahasia123', 'position' => 'Developer',
                'start_date' => '2024-08-01', 'end_date' => '2024-11-01',
            ])->assertSessionHas('error');

        $this->assertSame(0, Internship::where('student_id', $student->id)->count());
    }

    /** API mobile harus punya gerbang yang sama (kalau tidak, celahnya tetap terbuka). */
    public function test_api_menolak_ajukan_magang_tanpa_dospem(): void
    {
        Storage::fake('public');
        $u = $this->userByUsername('3.34.23.2.02');
        $u->student->update(['lecturer_id' => null]);

        $token = $u->createToken('uji')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mahasiswa/ajukan-magang')
            ->assertOk()
            ->assertJsonPath('has_lecturer', false);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mahasiswa/ajukan-magang', [
                'company_name' => 'PT API', 'pic_name' => 'Budi', 'pic_phone' => '08123456789',
                'start_date' => '2024-08-01', 'end_date' => '2024-11-01',
                'proof_file' => UploadedFile::fake()->create('b.pdf', 20, 'application/pdf'),
            ])->assertStatus(422);

        $this->assertSame(0, CompanyRequest::where('student_id', $u->student->id)->count());
    }
}
