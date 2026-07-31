<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

/**
 * Notifikasi harus bisa diklik dan mengarah ke hal yang dirujuk — bukan sekadar
 * teks. Tujuannya disimpan di kolom `link` saat notifikasi dibuat, lalu route
 * `notifikasi.open` menandainya dibaca sekaligus mengarahkan ke sana.
 */
class NotifikasiBisaDiklikTest extends FeatureTestCase
{
    public function test_notifikasi_logbook_mengarah_ke_logbook_yang_dimaksud(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($mhs)->post('/mahasiswa/logbook', [
            'title' => 'Hari pertama', 'activity' => 'orientasi', 'date' => '2024-07-01',
        ])->assertSessionHasNoErrors();

        $lb    = LogBook::where('title', 'Hari pertama')->firstOrFail();
        $notif = Notification::where('user_id', $dosen->id)->where('category', 'log_book')->firstOrFail();

        $this->assertSame("/dosen/mahasiswa/{$mhs->student->id}#logbook-{$lb->id}", $notif->link);
    }

    public function test_notifikasi_bimbingan_mengarah_ke_detail_mahasiswa(): void
    {
        Storage::fake('public');
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($mhs)->post('/mahasiswa/bimbingan', [
            'title' => 'Konsultasi', 'activity' => 'bahas bab 1', 'date' => '2024-07-01',
        ])->assertSessionHasNoErrors();

        $notif = Notification::where('user_id', $dosen->id)->where('category', 'bimbingan')->firstOrFail();

        $this->assertSame("/dosen/mahasiswa/{$mhs->student->id}#bimbingan", $notif->link);
    }

    /** Klik notifikasi: ditandai dibaca lalu diarahkan ke tujuannya. */
    public function test_membuka_notifikasi_menandai_dibaca_dan_mengarahkan(): void
    {
        $dosen = $this->userByUsername('dosen1');

        $notif = Notification::create([
            'user_id' => $dosen->id, 'message' => 'Uji', 'date' => now()->toDateString(),
            'category' => 'log_book', 'is_read' => false, 'link' => '/dosen/mahasiswa/1#logbook-9',
        ]);

        $this->actingAs($dosen)->get("/dosen/notifikasi/{$notif->id}/open")
            ->assertRedirect('/dosen/mahasiswa/1#logbook-9');

        $this->assertTrue($notif->fresh()->is_read);
    }

    /** Notifikasi lama tanpa tujuan tetap aman: jatuh ke halaman notifikasi. */
    public function test_notifikasi_tanpa_link_tidak_error(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');

        $notif = Notification::create([
            'user_id' => $mhs->id, 'message' => 'Lama', 'date' => now()->toDateString(),
            'category' => 'log_book', 'is_read' => false,
        ]);

        $this->actingAs($mhs)->get("/mahasiswa/notifikasi/{$notif->id}/open")
            ->assertRedirect(route('mahasiswa.notifikasi'));
    }

    /** Notifikasi milik orang lain tak boleh dibuka. */
    public function test_notifikasi_orang_lain_ditolak(): void
    {
        $dosen = $this->userByUsername('dosen1');
        $mhs   = $this->userByUsername('3.34.23.2.01');

        $notif = Notification::create([
            'user_id' => $dosen->id, 'message' => 'Uji', 'date' => now()->toDateString(),
            'category' => 'log_book', 'is_read' => false, 'link' => '/dosen/mahasiswa/1',
        ]);

        $this->actingAs($mhs)->get("/mahasiswa/notifikasi/{$notif->id}/open")->assertForbidden();
    }

    /** Halaman notifikasi merender tautannya, bukan teks mati. */
    public function test_halaman_notifikasi_merender_tautan(): void
    {
        $dosen = $this->userByUsername('dosen1');
        $g     = Guidance::create([
            'student_id' => $this->userByUsername('3.34.23.2.01')->student->id,
            'title' => 'B1', 'activity' => 'a', 'date' => '2024-07-01', 'status' => 'pending',
        ]);

        $notif = Notification::create([
            'user_id' => $dosen->id, 'message' => 'Ada bimbingan baru', 'date' => now()->toDateString(),
            'category' => 'bimbingan', 'is_read' => false, 'link' => "/dosen/mahasiswa/{$g->student_id}",
        ]);

        $this->actingAs($dosen)->get('/dosen/notifikasi')
            ->assertOk()
            ->assertSee(route('dosen.notifikasi.open', $notif->id), false);
    }
}
