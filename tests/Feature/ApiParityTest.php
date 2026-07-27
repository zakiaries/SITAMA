<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\{CompanyRequest, Guidance, Lecturer, LogBook, Seminar};
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\FeatureTestCase;

/** Parity endpoint API mobile dengan web (Fase 1). */
class ApiParityTest extends FeatureTestCase
{
    private function actingApi(string $username)
    {
        $u = $this->userByUsername($username);
        Sanctum::actingAs($u, ['*']);
        return $u;
    }

    public function test_logbook_update(): void
    {
        $u  = $this->actingApi('3.34.23.2.01');
        $lb = LogBook::create(['student_id' => $u->student->id, 'title' => 'A', 'activity' => 'x', 'date' => '2024-07-01']);

        $this->putJson("/api/mahasiswa/logbook/{$lb->id}", ['title' => 'B', 'activity' => 'y', 'date' => '2024-07-02'])->assertOk();
        $this->assertSame('B', $lb->fresh()->title);

        // tanggal masa depan ditolak
        $this->putJson("/api/mahasiswa/logbook/{$lb->id}", ['title' => 'C', 'activity' => 'y', 'date' => now()->addDay()->toDateString()])
            ->assertStatus(422);
    }

    public function test_bimbingan_destroy_rules(): void
    {
        $u = $this->actingApi('3.34.23.2.01');
        $g = Guidance::create(['student_id' => $u->student->id, 'title' => 'P', 'activity' => 'a', 'date' => '2024-07-01', 'status' => 'pending']);
        $this->deleteJson("/api/mahasiswa/bimbingan/{$g->id}")->assertOk();
        $this->assertNull(Guidance::find($g->id));

        $appr = Guidance::create(['student_id' => $u->student->id, 'title' => 'A', 'activity' => 'a', 'date' => '2024-07-01', 'status' => 'approved']);
        $this->deleteJson("/api/mahasiswa/bimbingan/{$appr->id}")->assertStatus(422);
        $this->assertNotNull(Guidance::find($appr->id));
    }

    public function test_cancel_magang(): void
    {
        $u   = $this->actingApi('3.34.23.2.01');
        $req = CompanyRequest::create(['student_id' => $u->student->id, 'company_name' => 'X', 'pic_name' => 'A', 'position' => 'D', 'start_date' => '2024-08-01', 'status' => 'pending']);
        $this->deleteJson("/api/mahasiswa/ajukan-magang/{$req->id}")->assertOk();
        $this->assertNull(CompanyRequest::find($req->id));
    }

    public function test_dosen_seminar_update_and_qr(): void
    {
        $dosen = Lecturer::whereHas('user', fn ($q) => $q->where('username', 'dosen1'))->first();
        Sanctum::actingAs($dosen->user, ['*']);

        $draft = Seminar::create(['lecturer_id' => $dosen->id, 'title' => 'A', 'program' => 'TI', 'status' => 'draft']);
        $this->putJson("/api/dosen/seminar/{$draft->id}", ['title' => 'B', 'description' => 'd'])->assertOk();
        $this->assertSame('B', $draft->fresh()->title);

        $sched = Seminar::create(['lecturer_id' => $dosen->id, 'title' => 'S', 'program' => 'TI', 'status' => 'scheduled', 'access_token' => Str::random(48)]);
        $this->getJson("/api/dosen/seminar/{$sched->id}/qr")->assertOk()->assertJsonStructure(['url', 'rt', 'interval']);
    }

    public function test_register_nim_validation_and_reset_link(): void
    {
        Mail::fake();
        // NIM ngawur ditolak
        $this->postJson('/api/register', [
            'name' => 'X', 'username' => 'asdf', 'email' => 'a@x.ac.id',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'the_class' => 'A', 'study_program' => 'TI', 'major' => 'I', 'academic_year' => '2023',
        ])->assertStatus(422);

        // reset link ke email terdaftar
        $this->postJson('/api/lupa-password', ['username' => '3.34.23.2.01'])->assertOk();
        Mail::assertSent(ResetPasswordMail::class);
    }
}
