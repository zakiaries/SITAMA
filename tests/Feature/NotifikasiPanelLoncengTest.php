<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Notifikasi dulu ditempel sebagai kartu biasa di dashboard mahasiswa: mendorong
 * turun isi halaman, tak bisa diklik, tak punya tombol tutup, dan tak terlihat
 * dari halaman lain. Satu-satunya cara menyingkirkannya adalah pergi ke lonceng,
 * membuka halaman notifikasi, lalu menekan "Tandai dibaca" satu per satu — jadi
 * kabar lama menumpuk di halaman pertama yang dilihat mahasiswa tiap kali masuk.
 *
 * Sekarang isinya melayang dari lonceng, tersedia di seluruh halaman, dan tiap
 * barisnya bisa langsung dibuka atau ditandai dibaca. Panelnya dipakai keempat
 * peran, bukan mahasiswa saja.
 */
class NotifikasiPanelLoncengTest extends FeatureTestCase
{
    private function notifikasi(User $user, array $ganti = []): Notification
    {
        // created_at bukan kolom fillable, jadi harus dipasang setelah dibuat —
        // kalau dibiarkan seragam, urutan "terbaru" jadi seri dan tak terduga.
        $dibuat = $ganti['created_at'] ?? null;
        unset($ganti['created_at']);

        $notif = Notification::create(array_merge([
            'user_id'     => $user->id,
            'message'     => 'Bimbingan kamu disetujui dosen.',
            'category'    => 'bimbingan',
            'detail_text' => 'Bab 1 sudah bisa dilanjutkan.',
            'date'        => now(),
            'is_read'     => false,
            'link'        => '/mahasiswa/bimbingan',
        ], $ganti));

        if ($dibuat) {
            $notif->forceFill(['created_at' => $dibuat])->save();
        }

        return $notif;
    }

    /** Peran → [username fixture, halaman mana pun yang memakai topbar]. */
    private function peran(): array
    {
        return [
            'mahasiswa'      => ['3.34.23.2.01', '/mahasiswa/logbook'],
            'dosen'          => ['dosen1',       '/dosen/dashboard'],
            'dosen-industri' => ['industri1',    '/dosen-industri/dashboard'],
            'kaprodi'        => ['kaprodi',      '/kaprodi/dashboard'],
        ];
    }

    // ── Panel tersedia di keempat peran ─────────────────────────────────────

    public function test_panel_lonceng_ada_di_keempat_peran(): void
    {
        foreach ($this->peran() as $peran => [$username, $halaman]) {
            $user  = $this->userByUsername($username);
            $notif = $this->notifikasi($user, ['message' => "Kabar untuk {$peran}"]);

            $html = $this->actingAs($user)->get($halaman)->assertOk()->getContent();

            $this->assertStringContainsString('notif-panel', $html,
                "Panel notifikasi tak dirender untuk peran {$peran}.");
            $this->assertStringContainsString("Kabar untuk {$peran}", $html,
                "Isi notifikasi tak muncul di panel peran {$peran}.");
            $this->assertStringContainsString(route($peran . '.notifikasi.open', $notif->id), $html,
                "Baris notifikasi peran {$peran} tak bisa diklik.");
            $this->assertStringContainsString(route($peran . '.notifikasi.read', $notif->id), $html,
                "Baris notifikasi peran {$peran} tak punya tombol tandai dibaca.");
            $this->assertStringContainsString(route($peran . '.notifikasi.read-all'), $html,
                "Peran {$peran} tak punya tombol tandai semua.");
            $this->assertStringContainsString(route($peran . '.notifikasi'), $html,
                "Peran {$peran} tak punya tautan lihat semua notifikasi.");

            $this->app['auth']->forgetGuards();
        }
    }

    /** Inti keluhannya: panel bisa dibuka dari halaman mana pun, bukan dashboard saja. */
    public function test_panel_muncul_di_halaman_selain_dashboard(): void
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.01');
        $this->notifikasi($mahasiswa, ['message' => 'Kabar di halaman mana pun']);

        foreach (['/mahasiswa/dashboard', '/mahasiswa/logbook', '/mahasiswa/bimbingan'] as $halaman) {
            $this->actingAs($mahasiswa)->get($halaman)->assertOk()
                ->assertSee('Kabar di halaman mana pun');
        }
    }

    // ── Dashboard bersih ────────────────────────────────────────────────────

    public function test_kartu_notifikasi_tak_lagi_menumpuk_di_dashboard(): void
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.01');

        foreach (range(1, 5) as $i) {
            $this->notifikasi($mahasiswa, ['message' => "Kabar lama {$i}"]);
        }

        $html = $this->actingAs($mahasiswa)->get('/mahasiswa/dashboard')
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('alert-box', $html,
            'Dashboard masih menempelkan kartu notifikasi di badan halaman.');
    }

    // ── Menandai dibaca dari panel ──────────────────────────────────────────

    public function test_menandai_dibaca_dari_panel_menghilangkan_titik_merah(): void
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.01');
        $notif     = $this->notifikasi($mahasiswa);

        $this->actingAs($mahasiswa)->get('/mahasiswa/dashboard')
            ->assertOk()->assertSee('notif-dot', false);

        $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.notifikasi.read', $notif->id), [], ['referer' => '/mahasiswa/dashboard'])
            ->assertRedirect('/mahasiswa/dashboard');

        $this->assertTrue($notif->fresh()->is_read);

        $this->actingAs($mahasiswa)->get('/mahasiswa/dashboard')
            ->assertOk()->assertDontSee('notif-dot', false);
    }

    public function test_tandai_semua_dari_panel_mengosongkan_yang_belum_dibaca(): void
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.01');

        foreach (range(1, 3) as $i) {
            $this->notifikasi($mahasiswa, ['message' => "Kabar {$i}"]);
        }

        $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.notifikasi.read-all'), [], ['referer' => '/mahasiswa/dashboard'])
            ->assertRedirect('/mahasiswa/dashboard');

        $this->assertSame(0, $mahasiswa->notifications()->where('is_read', false)->count());
    }

    /** Yang sudah dibaca tetap terlihat di panel, hanya tombolnya yang hilang. */
    public function test_notifikasi_terbaca_tetap_terlihat_tanpa_tombol(): void
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.01');
        $notif     = $this->notifikasi($mahasiswa, ['message' => 'Sudah kubaca', 'is_read' => true]);

        $html = $this->actingAs($mahasiswa)->get('/mahasiswa/dashboard')
            ->assertOk()->getContent();

        $this->assertStringContainsString('Sudah kubaca', $html);
        $this->assertStringNotContainsString(route('mahasiswa.notifikasi.read', $notif->id), $html,
            'Notifikasi yang sudah dibaca masih menawarkan tombol tandai dibaca.');
    }

    /** Panel dibatasi lima baris terbaru; sisanya lewat "Lihat semua". */
    public function test_panel_hanya_menampilkan_lima_terbaru(): void
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.01');

        foreach (range(1, 7) as $i) {
            $this->notifikasi($mahasiswa, [
                'message'    => "Kabar ke-{$i}",
                'created_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $this->actingAs($mahasiswa)->get('/mahasiswa/dashboard')->assertOk()
            ->assertSee('Kabar ke-7')
            ->assertSee('Kabar ke-3')
            ->assertDontSee('Kabar ke-2')
            ->assertDontSee('Kabar ke-1');
    }

    public function test_panel_kosong_saat_belum_ada_notifikasi(): void
    {
        $mahasiswa = $this->userByUsername('3.34.23.2.02');
        $mahasiswa->notifications()->delete();

        $this->actingAs($mahasiswa)->get('/mahasiswa/dashboard')->assertOk()
            ->assertSee('Belum ada notifikasi.')
            ->assertDontSee('notif-dot', false);
    }

    /** Notifikasi milik orang lain tetap tak bisa disentuh. */
    public function test_tak_bisa_menandai_notifikasi_milik_orang_lain(): void
    {
        $notifOrangLain = $this->notifikasi($this->userByUsername('3.34.23.2.02'));

        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->post(route('mahasiswa.notifikasi.read', $notifOrangLain->id))
            ->assertForbidden();

        $this->assertFalse($notifOrangLain->fresh()->is_read);
    }
}
