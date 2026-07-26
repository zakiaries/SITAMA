<?php

namespace Tests\Feature;

use App\Models\{CompanyRequest, Guidance, LogBook};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

class StudentFeaturesTest extends FeatureTestCase
{
    public function test_ajukan_magang_phone_format(): void
    {
        Storage::fake('public');
        $u = $this->userByUsername('3.34.23.2.01'); // magang selesai -> boleh ajukan lagi
        $base = fn ($phone) => [
            'company_name' => 'PT X', 'pic_name' => 'Budi', 'pic_phone' => $phone,
            'start_date' => '2024-08-01', 'company_mode' => 'new',
            'proof_file' => UploadedFile::fake()->create('b.pdf', 20, 'application/pdf'),
        ];
        $this->from('/mahasiswa/ajukan-magang')->actingAs($u)->post('/mahasiswa/ajukan-magang', $base('abcxyz'))
            ->assertSessionHasErrors('pic_phone');
        $this->from('/mahasiswa/ajukan-magang')->actingAs($u)->post('/mahasiswa/ajukan-magang', $base('0812-3456-7890'))
            ->assertSessionHasNoErrors();
    }

    public function test_bimbingan_file_type_validated(): void
    {
        $u = $this->userByUsername('3.34.23.2.01');
        $this->from('/mahasiswa/bimbingan')->actingAs($u)->post('/mahasiswa/bimbingan', [
            'title' => 'B', 'activity' => 'a', 'date' => '2024-07-01',
            'file' => UploadedFile::fake()->create('x.exe', 20),
        ])->assertSessionHasErrors('file');
    }

    public function test_activity_date_cannot_be_future(): void
    {
        $u = $this->userByUsername('3.34.23.2.01');
        $besok = now()->addDay()->toDateString();
        $this->from('/mahasiswa/logbook')->actingAs($u)->post('/mahasiswa/logbook',
            ['title' => 'L', 'activity' => 'a', 'date' => $besok])->assertSessionHasErrors('date');
        $this->from('/mahasiswa/bimbingan')->actingAs($u)->post('/mahasiswa/bimbingan',
            ['title' => 'B', 'activity' => 'a', 'date' => $besok])->assertSessionHasErrors('date');
    }

    public function test_logbook_edit_owner_only(): void
    {
        $u1 = $this->userByUsername('3.34.23.2.01');
        $u2 = $this->userByUsername('3.34.23.2.02');
        $lb = LogBook::create(['student_id' => $u1->student->id, 'title' => 'ASLI', 'activity' => 'x', 'date' => '2024-07-01']);

        $this->actingAs($u1)->put("/mahasiswa/logbook/{$lb->id}", ['title' => 'EDIT', 'activity' => 'y', 'date' => '2024-07-02']);
        $this->assertSame('EDIT', $lb->fresh()->title);

        $this->actingAs($u2)->put("/mahasiswa/logbook/{$lb->id}", ['title' => 'HACK', 'activity' => 'z', 'date' => '2024-07-02'])
            ->assertForbidden();
        $this->assertSame('EDIT', $lb->fresh()->title);
    }

    public function test_cancel_pending_magang_request(): void
    {
        $u1 = $this->userByUsername('3.34.23.2.01');
        $u2 = $this->userByUsername('3.34.23.2.02');

        $pending = CompanyRequest::create(['student_id' => $u1->student->id, 'company_name' => 'PT P', 'pic_name' => 'A', 'position' => 'Dev', 'start_date' => '2024-08-01', 'status' => 'pending']);
        $this->actingAs($u1)->delete("/mahasiswa/ajukan-magang/{$pending->id}");
        $this->assertNull(CompanyRequest::find($pending->id));

        // IDOR
        $other = CompanyRequest::create(['student_id' => $u1->student->id, 'company_name' => 'PT Q', 'pic_name' => 'B', 'position' => 'QA', 'start_date' => '2024-08-01', 'status' => 'pending']);
        $this->actingAs($u2)->delete("/mahasiswa/ajukan-magang/{$other->id}")->assertForbidden();
        $this->assertNotNull(CompanyRequest::find($other->id));

        // approved tak bisa dibatalkan
        $appr = CompanyRequest::create(['student_id' => $u1->student->id, 'company_name' => 'PT R', 'pic_name' => 'C', 'position' => 'PM', 'start_date' => '2024-08-01', 'status' => 'approved']);
        $this->from('/mahasiswa/ajukan-magang')->actingAs($u1)->delete("/mahasiswa/ajukan-magang/{$appr->id}");
        $this->assertNotNull(CompanyRequest::find($appr->id));
    }

    public function test_bimbingan_delete_rules(): void
    {
        $u1 = $this->userByUsername('3.34.23.2.01');
        $u2 = $this->userByUsername('3.34.23.2.02');

        $pending = Guidance::create(['student_id' => $u1->student->id, 'title' => 'P', 'activity' => 'a', 'date' => '2024-07-01', 'status' => 'pending']);
        $this->actingAs($u1)->delete("/mahasiswa/bimbingan/{$pending->id}");
        $this->assertNull(Guidance::find($pending->id));

        $approved = Guidance::create(['student_id' => $u1->student->id, 'title' => 'A', 'activity' => 'a', 'date' => '2024-07-01', 'status' => 'approved']);
        $this->from('/mahasiswa/bimbingan')->actingAs($u1)->delete("/mahasiswa/bimbingan/{$approved->id}");
        $this->assertNotNull(Guidance::find($approved->id)); // approved terkunci

        $mine = Guidance::create(['student_id' => $u1->student->id, 'title' => 'M', 'activity' => 'a', 'date' => '2024-07-01', 'status' => 'pending']);
        $this->actingAs($u2)->delete("/mahasiswa/bimbingan/{$mine->id}")->assertForbidden();
        $this->assertNotNull(Guidance::find($mine->id));
    }
}
