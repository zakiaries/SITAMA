<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Audit menyeluruh notifikasi bisa-diklik.
 *
 * Bug aslinya: notifikasi diklik hanya memantulkan pengguna kembali ke halaman
 * notifikasi. Penyebabnya bertingkat — kolom `link` yang tak terisi (#23),
 * baris lama yang menyusul (#22), dan kejadian yang tak pernah membuat
 * notifikasi sama sekali (berkas ini).
 *
 * Tes di sini menjaga aturannya, bukan satu kasus: setiap pemanggilan
 * Notification::kirim() wajib menyertakan tujuan, dan tiap portal harus
 * benar-benar mengantar pengguna ke sana saat notifikasinya dibuka.
 *
 * Catatan: portalnya EMPAT, bukan lima. Enum users.role memang memuat
 * 'industri', tapi nilai itu mati — tak ada yang membuatnya, tak ada route,
 * tak ada controller. Peninggalan rancangan lama saat perusahaan mendaftar
 * sendiri, sebelum akun pembimbing industri dibuatkan Kaprodi.
 */
class AuditNotifikasiTest extends FeatureTestCase
{
    /**
     * Penjaga statis: helper kirim() menerima $link opsional demi kemudahan,
     * dan justru di situ celahnya — argumen yang mudah lupa ditulis. Tes ini
     * menuntut kelima argumen ada di SETIAP pemanggilan.
     */
    public function test_setiap_kirim_notifikasi_menyertakan_tujuan(): void
    {
        $tanpaTujuan = [];

        foreach ($this->berkasPhpAplikasi() as $path) {
            $isi = file_get_contents($path);
            $offset = 0;

            while (($pos = strpos($isi, 'Notification::kirim(', $offset)) !== false) {
                $mulai = $pos + strlen('Notification::kirim(');
                $argumen = $this->pisahArgumen($isi, $mulai);
                $offset  = $mulai;

                if (count($argumen) < 5) {
                    $baris = substr_count(substr($isi, 0, $pos), "\n") + 1;
                    $tanpaTujuan[] = $this->relatif($path) . ':' . $baris
                        . ' (' . count($argumen) . ' argumen)';
                }
            }
        }

        $this->assertSame([], $tanpaTujuan,
            "Notifikasi tanpa argumen tujuan akan memantul balik ke halaman notifikasi saat diklik.");
    }

    /** Tiap portal harus mengantar ke tujuan yang tersimpan, bukan ke mana-mana. */
    public function test_membuka_notifikasi_mengantar_ke_tujuannya(): void
    {
        $portal = [
            'kaprodi'      => ['kaprodi', '/kaprodi'],
            'dosen1'       => ['dosen', '/dosen'],
            'industri1'    => ['dosen-industri', '/dosen-industri'],
            '3.34.23.2.01' => ['mahasiswa', '/mahasiswa'],
        ];

        foreach ($portal as $username => [$prefix, $tujuan]) {
            $user  = $this->userByUsername($username);
            $notif = Notification::create([
                'user_id'  => $user->id, 'message' => 'Uji tujuan',
                'date'     => now()->toDateString(), 'category' => 'log_book',
                'is_read'  => false, 'link' => $tujuan . '/notifikasi',
            ]);

            $this->actingAs($user)
                ->get("/{$prefix}/notifikasi/{$notif->id}/open")
                ->assertRedirect($tujuan . '/notifikasi');

            $this->assertTrue($notif->fresh()->is_read, "Portal {$prefix} tak menandai dibaca.");
        }
    }

    // ── Pendaftaran mahasiswa: kejadian yang dulu tak bernotifikasi ─────────

    public function test_pendaftaran_mahasiswa_memberi_tahu_kaprodi(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');
        $sebelum = Notification::where('user_id', $kaprodi->id)->count();

        $this->post('/register', [
            'name'                  => 'Calon Mahasiswa',
            'username'              => '3.34.23.2.77',
            'email'                 => 'calon@test.ac.id',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'the_class'             => 'IK-3C',
            'study_program'         => 'Teknik Informatika',
            'major'                 => 'Teknik Elektro',
            'academic_year'         => '2023/2024',
        ])->assertSessionHasNoErrors();

        $this->assertSame($sebelum + 1, Notification::where('user_id', $kaprodi->id)->count());

        $notif = Notification::where('user_id', $kaprodi->id)
            ->where('category', 'pendaftaran')->latest('id')->firstOrFail();

        $this->assertStringContainsString('Calon Mahasiswa', $notif->message);
        $this->assertStringContainsString('3.34.23.2.77', $notif->detail_text);
        $this->assertSame('/kaprodi/mahasiswa?status=pending', $notif->link);
    }

    /** Diklik harus mendarat di tab yang memuat tombol Setujui/Tolak. */
    public function test_notifikasi_pendaftaran_mendarat_di_halaman_persetujuan(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');

        $notif = Notification::create([
            'user_id' => $kaprodi->id, 'message' => 'Mahasiswa baru mendaftar: X',
            'date' => now()->toDateString(), 'category' => 'pendaftaran',
            'is_read' => false, 'link' => '/kaprodi/mahasiswa?status=pending',
        ]);

        $this->actingAs($kaprodi)->get("/kaprodi/notifikasi/{$notif->id}/open")
            ->assertRedirect('/kaprodi/mahasiswa?status=pending');

        // Fixture punya 1 mahasiswa pending -> tombol persetujuannya harus ada.
        $pending = \App\Models\Student::where('status', 'pending')->firstOrFail();

        $this->actingAs($kaprodi)->get('/kaprodi/mahasiswa?status=pending')
            ->assertOk()
            ->assertSee(route('kaprodi.mahasiswa.approve', $pending), false);
    }

    /** Notifikasi lama tanpa tujuan tetap mendarat di halaman yang masuk akal. */
    public function test_kategori_pendaftaran_punya_tujuan_cadangan(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');

        $notif = Notification::create([
            'user_id' => $kaprodi->id, 'message' => 'Lama tanpa tujuan',
            'date' => now()->toDateString(), 'category' => 'pendaftaran',
            'is_read' => false, 'link' => null,
        ]);

        $this->actingAs($kaprodi)->get("/kaprodi/notifikasi/{$notif->id}/open")
            ->assertRedirect(route('kaprodi.mahasiswa.index', ['status' => 'pending']));
    }

    // ── Pembantu ───────────────────────────────────────────────────────────

    /** @return string[] */
    private function berkasPhpAplikasi(): array
    {
        $berkas = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(app_path(), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $berkas[] = $file->getPathname();
            }
        }

        sort($berkas);

        return $berkas;
    }

    private function relatif(string $path): string
    {
        return str_replace(app_path() . DIRECTORY_SEPARATOR, '', $path);
    }

    /**
     * Pisahkan argumen pemanggilan mulai dari posisi setelah tanda kurung buka.
     * Hitung kedalaman kurung dan lewati isi string agar koma di dalam teks
     * (mis. "Perusahaan: x, y") tidak terhitung sebagai pemisah argumen.
     *
     * @return string[]
     */
    private function pisahArgumen(string $isi, int $mulai): array
    {
        $argumen = [];
        $sekarang = '';
        $dalam    = 0;
        $kutip    = null;

        for ($i = $mulai, $n = strlen($isi); $i < $n; $i++) {
            $c = $isi[$i];

            if ($kutip !== null) {
                if ($c === '\\') {
                    $sekarang .= $c . ($isi[$i + 1] ?? '');
                    $i++;
                    continue;
                }
                if ($c === $kutip) {
                    $kutip = null;
                }
                $sekarang .= $c;
                continue;
            }

            if ($c === '"' || $c === "'") {
                $kutip = $c;
                $sekarang .= $c;
                continue;
            }

            if ($c === '(' || $c === '[') {
                $dalam++;
            } elseif ($c === ')' && $dalam === 0) {
                if (trim($sekarang) !== '') {
                    $argumen[] = trim($sekarang);
                }
                break;
            } elseif ($c === ')' || $c === ']') {
                $dalam--;
            } elseif ($c === ',' && $dalam === 0) {
                $argumen[] = trim($sekarang);
                $sekarang  = '';
                continue;
            }

            $sekarang .= $c;
        }

        return $argumen;
    }
}
