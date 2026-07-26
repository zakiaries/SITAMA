<?php

namespace Tests\Feature;

use App\Models\{Lecturer, Seminar, SeminarAttendance, Student};
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

class KaprodiSeminarTest extends FeatureTestCase
{
    public function test_kaprodi_approve_and_reject_and_recover(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');
        $pending = Student::whereHas('user', fn ($q) => $q->where('username', '3.34.23.2.03'))->first();

        // approve
        $this->actingAs($kaprodi)->post("/kaprodi/mahasiswa/{$pending->id}/approve");
        $this->assertSame('active', $pending->fresh()->status);

        // reject
        $this->actingAs($kaprodi)->post("/kaprodi/mahasiswa/{$pending->id}/reject");
        $this->assertSame('rejected', $pending->fresh()->status);

        // tab "Ditolak" menampilkan + pulihkan (approve)
        $this->actingAs($kaprodi)->get('/kaprodi/mahasiswa?status=rejected')->assertOk()->assertSee('Pulihkan');
        $this->actingAs($kaprodi)->post("/kaprodi/mahasiswa/{$pending->id}/approve");
        $this->assertSame('active', $pending->fresh()->status);
    }

    public function test_dosen_edit_seminar_rules(): void
    {
        $dosen  = Lecturer::whereHas('user', fn ($q) => $q->where('username', 'dosen1'))->first();
        $draft  = Seminar::create(['lecturer_id' => $dosen->id, 'title' => 'ASLI', 'program' => 'TI', 'status' => 'draft']);

        $this->actingAs($dosen->user)->put("/dosen/seminar/{$draft->id}", ['title' => 'BARU', 'description' => 'desk']);
        $this->assertSame('BARU', $draft->fresh()->title);

        $done = Seminar::create(['lecturer_id' => $dosen->id, 'title' => 'DONE', 'program' => 'TI', 'status' => 'completed']);
        $this->from('/dosen/seminar')->actingAs($dosen->user)->put("/dosen/seminar/{$done->id}", ['title' => 'UBAH']);
        $this->assertSame('DONE', $done->fresh()->title); // completed diblokir
    }

    public function test_rotating_qr_attendance(): void
    {
        $dosen = Lecturer::whereHas('user', fn ($q) => $q->where('username', 'dosen1'))->first();
        $sem   = Seminar::create(['lecturer_id' => $dosen->id, 'title' => 'S', 'program' => 'TI', 'status' => 'scheduled', 'access_token' => Str::random(48)]);
        $stu   = $this->userByUsername('3.34.23.2.01');
        $rt    = $sem->rotatingToken();

        // rt valid -> tercatat
        $this->actingAs($stu)->post("/seminar/hadir/{$sem->access_token}", ['rt' => $rt]);
        $this->assertTrue(SeminarAttendance::where('seminar_id', $sem->id)->where('student_id', $stu->student->id)->exists());

        // rt salah -> ditolak
        $stu2 = $this->userByUsername('3.34.23.2.02');
        $this->from('/x')->actingAs($stu2)->post("/seminar/hadir/{$sem->access_token}", ['rt' => 'salah'])
            ->assertSessionHasErrors('hadir');
        $this->assertFalse(SeminarAttendance::where('seminar_id', $sem->id)->where('student_id', $stu2->student->id)->exists());

        // halaman QR dosen
        $this->actingAs($dosen->user)->get("/dosen/seminar/{$sem->id}/qr")->assertOk();
    }
}
