<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

/**
 * Pengajuan selesai magang digerbangi checklist kelengkapan: sertifikat,
 * laporan di-ACC, nilai kedua pembimbing, dan minimal logbook. Selama isinya
 * masih bisa diubah setelah pengajuan dikirim, yang diperiksa Kaprodi bisa
 * berbeda dari yang diajukan — sertifikat ditukar, logbook dikurangi, bimbingan
 * ditambah belakangan.
 *
 * Kuncinya berlaku untuk DUA keadaan: menunggu ACC (finish_requested) dan sudah
 * ditutup (is_finished). Keduanya punya jalan keluar — mahasiswa membatalkan
 * pengajuannya sendiri, atau Kaprodi membuka kembali status selesai.
 */
class IsianMahasiswaTerkunciTest extends FeatureTestCase
{
    /** Mahasiswa 01 magangnya sudah selesai di fixture; ubah sesuai keadaan uji. */
    private function siapkan(array $keadaan)
    {
        $mhs = $this->userByUsername('3.34.23.2.01');
        $mhs->student->internships()->latest('id')->first()->update($keadaan);

        return $mhs;
    }

    private function menungguAcc()
    {
        return $this->siapkan(['is_finished' => false, 'finish_requested' => true]);
    }

    private function berjalan()
    {
        return $this->siapkan(['is_finished' => false, 'finish_requested' => false]);
    }

    // ── Sertifikat ──────────────────────────────────────────────────────────

    public function test_sertifikat_terkunci_saat_menunggu_acc(): void
    {
        Storage::fake('public');
        $mhs = $this->menungguAcc();

        $this->actingAs($mhs)->post('/mahasiswa/magang-saya/sertifikat', [
            'certificate' => UploadedFile::fake()->create('baru.pdf', 50, 'application/pdf'),
        ])->assertSessionHas('error');

        $this->assertNull($mhs->student->internships()->latest('id')->first()->certificate_path);
    }

    public function test_sertifikat_terkunci_saat_sudah_selesai(): void
    {
        Storage::fake('public');
        $mhs = $this->siapkan(['is_finished' => true, 'finish_requested' => false]);

        $this->actingAs($mhs)->post('/mahasiswa/magang-saya/sertifikat', [
            'certificate' => UploadedFile::fake()->create('baru.pdf', 50, 'application/pdf'),
        ])->assertSessionHas('error');
    }

    public function test_sertifikat_bisa_diunggah_saat_magang_berjalan(): void
    {
        Storage::fake('public');
        $mhs = $this->berjalan();

        $this->actingAs($mhs)->post('/mahasiswa/magang-saya/sertifikat', [
            'certificate' => UploadedFile::fake()->create('baru.pdf', 50, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->assertNotNull($mhs->student->internships()->latest('id')->first()->certificate_path);
    }

    // ── Log book ────────────────────────────────────────────────────────────

    public function test_logbook_tak_bisa_ditambah_diubah_dihapus_saat_terkunci(): void
    {
        $mhs = $this->berjalan();
        $lb  = LogBook::create([
            'student_id' => $mhs->student->id, 'title' => 'Asli',
            'activity' => 'kegiatan', 'date' => '2024-02-01',
        ]);

        $this->menungguAcc();

        $this->actingAs($mhs)->post('/mahasiswa/logbook', [
            'title' => 'Baru', 'activity' => 'x', 'date' => '2024-02-02',
        ])->assertSessionHas('error');

        $this->actingAs($mhs)->put("/mahasiswa/logbook/{$lb->id}", [
            'title' => 'Diubah', 'activity' => 'x', 'date' => '2024-02-01',
        ])->assertSessionHas('error');

        $this->actingAs($mhs)->delete("/mahasiswa/logbook/{$lb->id}")->assertSessionHas('error');

        $this->assertSame('Asli', $lb->fresh()->title);
        $this->assertSame(1, LogBook::where('student_id', $mhs->student->id)->count());
    }

    // ── Bimbingan ───────────────────────────────────────────────────────────

    public function test_bimbingan_tak_bisa_ditambah_diubah_dihapus_saat_terkunci(): void
    {
        Storage::fake('public');
        $mhs = $this->berjalan();
        $g   = Guidance::create([
            'student_id' => $mhs->student->id, 'title' => 'Asli',
            'activity' => 'bahas', 'date' => '2024-02-01', 'status' => 'pending',
        ]);

        $this->menungguAcc();

        $this->actingAs($mhs)->post('/mahasiswa/bimbingan', [
            'title' => 'Baru', 'activity' => 'x', 'date' => '2024-02-02',
        ])->assertSessionHas('error');

        $this->actingAs($mhs)->put("/mahasiswa/bimbingan/{$g->id}", [
            'title' => 'Diubah', 'activity' => 'x', 'date' => '2024-02-01',
        ])->assertSessionHas('error');

        $this->actingAs($mhs)->delete("/mahasiswa/bimbingan/{$g->id}")->assertSessionHas('error');

        $this->assertSame('Asli', $g->fresh()->title);
        $this->assertSame(1, Guidance::where('student_id', $mhs->student->id)->count());
    }

    // ── Jalan keluar: batalkan pengajuan ────────────────────────────────────

    public function test_mahasiswa_bisa_membatalkan_pengajuan_lalu_mengubah_lagi(): void
    {
        $mhs = $this->menungguAcc();

        $this->actingAs($mhs)->post('/mahasiswa/magang-saya/batal-selesai')
            ->assertSessionHas('success');

        $this->assertFalse($mhs->student->internships()->latest('id')->first()->finish_requested);

        $this->actingAs($mhs)->post('/mahasiswa/logbook', [
            'title' => 'Setelah batal', 'activity' => 'x', 'date' => '2024-02-02',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, LogBook::where('title', 'Setelah batal')->count());
    }

    public function test_pembatalan_memberi_tahu_kaprodi(): void
    {
        $mhs = $this->menungguAcc();

        $this->actingAs($mhs)->post('/mahasiswa/magang-saya/batal-selesai');

        $notif = Notification::where('user_id', $this->userByUsername('kaprodi')->id)
            ->where('category', 'selesai_magang')->latest('id')->firstOrFail();

        $this->assertStringContainsString('membatalkan', $notif->message);
        $this->assertSame("/kaprodi/mahasiswa/{$mhs->student->id}", $notif->link);
    }

    /** Setelah di-ACC, pembatalan bukan lagi hak mahasiswa. */
    public function test_tak_bisa_membatalkan_setelah_di_acc(): void
    {
        $mhs = $this->siapkan(['is_finished' => true, 'finish_requested' => false]);

        $this->actingAs($mhs)->post('/mahasiswa/magang-saya/batal-selesai')
            ->assertSessionHas('error');

        $this->assertTrue($mhs->student->internships()->latest('id')->first()->is_finished);
    }

    // ── Tampilan ────────────────────────────────────────────────────────────

    public function test_halaman_menyembunyikan_tombol_saat_terkunci(): void
    {
        $mhs = $this->berjalan();
        $lb  = LogBook::create([
            'student_id' => $mhs->student->id, 'title' => 'Asli',
            'activity' => 'kegiatan', 'date' => '2024-02-01',
        ]);
        $this->menungguAcc();

        $this->actingAs($mhs)->get('/mahasiswa/magang-saya')->assertOk()
            ->assertSee('Batalkan Pengajuan')
            ->assertDontSee('Unggah Sertifikat');

        // Deklarasi fungsi JS-nya selalu ikut tercetak, jadi yang diperiksa
        // tautan aksinya — itu yang hanya ada bila tombolnya dirender.
        $this->actingAs($mhs)->get('/mahasiswa/logbook')->assertOk()
            ->assertSee('menunggu ACC Kaprodi', false)
            ->assertDontSee(route('mahasiswa.logbook.destroy', $lb->id), false);

        $this->actingAs($mhs)->get('/mahasiswa/bimbingan')->assertOk()
            ->assertSee('menunggu ACC Kaprodi', false);
    }

    public function test_tombol_kembali_setelah_pengajuan_dibatalkan(): void
    {
        $mhs = $this->menungguAcc();

        $this->actingAs($mhs)->post('/mahasiswa/magang-saya/batal-selesai');

        $this->actingAs($mhs)->get('/mahasiswa/logbook')->assertOk()
            ->assertDontSee('menunggu ACC Kaprodi', false);
    }

    // ── API: kunci web tak boleh bisa ditembus lewat aplikasi HP ────────────

    /**
     * Menjaga di web saja tidak cukup — endpoint yang sama dipanggil aplikasi
     * mobile. Tanpa guard di sini, kuncinya cukup dilewati dengan memakai HP.
     */
    public function test_api_ikut_menolak_saat_terkunci(): void
    {
        Storage::fake('public');
        $mhs = $this->berjalan();
        $lb  = LogBook::create([
            'student_id' => $mhs->student->id, 'title' => 'Asli',
            'activity' => 'kegiatan', 'date' => '2024-02-01',
        ]);
        $g = Guidance::create([
            'student_id' => $mhs->student->id, 'title' => 'Asli',
            'activity' => 'bahas', 'date' => '2024-02-01', 'status' => 'pending',
        ]);

        $this->menungguAcc();

        $this->actingAs($mhs, 'sanctum')->postJson('/api/mahasiswa/logbook', [
            'title' => 'Baru', 'activity' => 'x', 'date' => '2024-02-02',
        ])->assertStatus(422);

        $this->actingAs($mhs, 'sanctum')
            ->putJson("/api/mahasiswa/logbook/{$lb->id}", ['title' => 'Diubah', 'activity' => 'x', 'date' => '2024-02-01'])
            ->assertStatus(422);

        $this->actingAs($mhs, 'sanctum')
            ->deleteJson("/api/mahasiswa/logbook/{$lb->id}")->assertStatus(422);

        $this->actingAs($mhs, 'sanctum')->postJson('/api/mahasiswa/bimbingan', [
            'title' => 'Baru', 'activity' => 'x', 'date' => '2024-02-02',
        ])->assertStatus(422);

        $this->actingAs($mhs, 'sanctum')
            ->deleteJson("/api/mahasiswa/bimbingan/{$g->id}")->assertStatus(422);

        $this->assertSame('Asli', $lb->fresh()->title);
        $this->assertNotNull($g->fresh());
        $this->assertSame(1, LogBook::where('student_id', $mhs->student->id)->count());
    }

    /** Jalan keluarnya juga harus ada di aplikasi, bukan cuma di web. */
    public function test_api_bisa_membatalkan_pengajuan(): void
    {
        $mhs = $this->menungguAcc();

        $this->actingAs($mhs, 'sanctum')
            ->postJson('/api/mahasiswa/magang-saya/batal-selesai')
            ->assertOk();

        $this->assertFalse($mhs->student->internships()->latest('id')->first()->finish_requested);

        $this->actingAs($mhs, 'sanctum')->postJson('/api/mahasiswa/logbook', [
            'title' => 'Setelah batal', 'activity' => 'x', 'date' => '2024-02-02',
        ])->assertSuccessful();
    }

    /**
     * Aplikasi tak boleh menyimpulkan sendiri kapan terkunci — aturannya dikirim
     * server supaya satu sumber, dan teks alasannya tak digandakan di Dart.
     */
    public function test_payload_api_membawa_keadaan_terkunci(): void
    {
        $mhs = $this->menungguAcc();

        $magang = $this->actingAs($mhs, 'sanctum')->getJson('/api/mahasiswa/magang-saya')
            ->assertOk()->json('internship');

        $this->assertTrue($magang['locked']);
        $this->assertTrue($magang['can_cancel_finish']);
        $this->assertStringContainsString('menunggu ACC Kaprodi', $magang['locked_reason']);

        // Field lama wajib tetap ada: APK yang sudah terpasang membacanya.
        $this->assertArrayHasKey('is_finished', $magang);
        $this->assertArrayHasKey('finish_requested', $magang);

        foreach (['/api/mahasiswa/logbook', '/api/mahasiswa/bimbingan'] as $url) {
            $data = $this->actingAs($mhs, 'sanctum')->getJson($url)->assertOk()->json();
            $this->assertTrue($data['locked'], "{$url} tidak menandai terkunci.");
            $this->assertNotEmpty($data['locked_reason'], "{$url} tidak menyertakan alasan.");
        }
    }

    public function test_payload_api_tidak_terkunci_saat_magang_berjalan(): void
    {
        $mhs = $this->berjalan();

        $magang = $this->actingAs($mhs, 'sanctum')->getJson('/api/mahasiswa/magang-saya')
            ->assertOk()->json('internship');

        $this->assertFalse($magang['locked']);
        $this->assertFalse($magang['can_cancel_finish']);
        $this->assertNull($magang['locked_reason']);

        $data = $this->actingAs($mhs, 'sanctum')->getJson('/api/mahasiswa/logbook')->assertOk()->json();
        $this->assertFalse($data['locked']);
    }

    /**
     * Pengajuan selesai lewat aplikasi dulu tak memberi tahu Kaprodi sama sekali
     * — jalur webnya memberi tahu, jalur API-nya tidak.
     */
    public function test_api_ajukan_selesai_memberi_tahu_kaprodi(): void
    {
        $mhs     = $this->berjalan();
        $student = $mhs->student;
        $intern  = $student->internships()->latest('id')->first();

        $intern->update(['certificate_path' => 'certificates/uji.pdf']);
        \App\Models\InternshipReport::create([
            'student_id' => $student->id, 'title' => 'Lap',
            'file_path' => 'reports/x.pdf', 'status' => 'approved',
        ]);
        $komponen = \App\Models\DetailedAssessmentComponent::firstOrFail();
        foreach (['lecturer', 'lecturer_industry'] as $penilai) {
            \App\Models\StudentScore::create([
                'internship_id' => $intern->id,
                'detailed_assessment_component_id' => $komponen->id,
                'scorer_type' => $penilai, 'score' => 8,
            ]);
        }
        for ($i = 1; $i <= \App\Models\Internship::MIN_LOGBOOK; $i++) {
            LogBook::create([
                'student_id' => $student->id, 'title' => "H{$i}",
                'activity' => 'x', 'date' => now()->subDays($i)->toDateString(),
            ]);
        }

        $this->actingAs($mhs, 'sanctum')
            ->postJson('/api/mahasiswa/magang-saya/ajukan-selesai')->assertOk();

        $notif = Notification::where('user_id', $this->userByUsername('kaprodi')->id)
            ->where('category', 'selesai_magang')->latest('id')->firstOrFail();

        $this->assertSame("/kaprodi/mahasiswa/{$student->id}", $notif->link);
    }
}
