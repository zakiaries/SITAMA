<?php

namespace Tests\Feature;

use App\Models\Guidance;
use App\Models\LogBook;
use App\Models\Notification;
use Tests\FeatureTestCase;

/**
 * Notifikasi yang lahir sebelum kolom `link` ada bernilai null, sehingga diklik
 * hanya memulangkan pengguna ke halaman notifikasi. Perintah simama:isi-link-
 * notifikasi mengisinya berdasarkan peran penerima + kategori.
 */
class IsiLinkNotifikasiTest extends FeatureTestCase
{
    private function notifLama(string $username, string $category, string $message): Notification
    {
        return Notification::create([
            'user_id'  => $this->userByUsername($username)->id,
            'message'  => $message,
            'date'     => now()->toDateString(),
            'category' => $category,
            'is_read'  => false,
            'link'     => null,
        ]);
    }

    public function test_mengisi_tujuan_untuk_tiap_peran(): void
    {
        $mhsBimbingan = $this->notifLama('3.34.23.2.01', 'bimbingan', 'Bimbingan "B1" disetujui dosen pembimbing.');
        $mhsSeminar   = $this->notifLama('3.34.23.2.01', 'seminar', 'Dosen membuka penjadwalan seminar: Seminar A');
        $kaprodiAju   = $this->notifLama('kaprodi', 'pengajuan_magang', 'Pengajuan magang baru dari Mahasiswa Satu');

        $this->artisan('simama:isi-link-notifikasi')->assertSuccessful();

        $this->assertSame('/mahasiswa/bimbingan', $mhsBimbingan->fresh()->link);
        $this->assertSame('/mahasiswa/seminar', $mhsSeminar->fresh()->link);
        $this->assertSame('/kaprodi/pengajuan-magang', $kaprodiAju->fresh()->link);
    }

    /** Pesan ke pembimbing diawali nama mahasiswa — dipakai untuk menemukan detailnya. */
    public function test_menemukan_mahasiswa_dari_pesan_dan_menajamkan_ke_item(): void
    {
        $student = $this->userByUsername('3.34.23.2.01')->student;

        $lb = LogBook::create([
            'student_id' => $student->id, 'title' => 'Hari pertama',
            'activity' => 'orientasi', 'date' => '2024-02-01',
        ]);

        $g = Guidance::create([
            'student_id' => $student->id, 'title' => 'Konsultasi Bab 1',
            'activity' => 'bahas bab 1', 'date' => '2024-02-01', 'status' => 'pending',
        ]);

        $nLog = $this->notifLama('dosen1', 'log_book', 'Mahasiswa Satu mengisi log book: "Hari pertama".');
        $nBim = $this->notifLama('dosen1', 'bimbingan', 'Mahasiswa Satu mengajukan bimbingan baru: "Konsultasi Bab 1".');
        $nInd = $this->notifLama('industri1', 'log_book', 'Mahasiswa Satu mengisi log book: "Hari pertama".');

        $this->artisan('simama:isi-link-notifikasi')->assertSuccessful();

        $this->assertSame("/dosen/mahasiswa/{$student->id}#logbook-{$lb->id}", $nLog->fresh()->link);
        $this->assertSame("/dosen/mahasiswa/{$student->id}#bimbingan-{$g->id}", $nBim->fresh()->link);
        $this->assertSame("/dosen-industri/mahasiswa/{$student->id}#logbook-{$lb->id}", $nInd->fresh()->link);
    }

    /** Kaprodi: ACC selesai magang ada di detail, dan namanya ada setelah "dari". */
    public function test_selesai_magang_kaprodi_mengarah_ke_detail_mahasiswa(): void
    {
        $student = $this->userByUsername('3.34.23.2.01')->student;
        $n = $this->notifLama('kaprodi', 'selesai_magang', 'Pengajuan selesai magang dari Mahasiswa Satu');

        $this->artisan('simama:isi-link-notifikasi')->assertSuccessful();

        $this->assertSame("/kaprodi/mahasiswa/{$student->id}", $n->fresh()->link);
    }

    /** Item tak dikenali tetap mendarat di halaman yang benar, bukan memantul. */
    public function test_jatuh_ke_halaman_saat_item_tak_ditemukan(): void
    {
        $n = $this->notifLama('dosen1', 'log_book', 'Orang Tak Dikenal mengisi log book: "Entah".');

        $this->artisan('simama:isi-link-notifikasi')->assertSuccessful();

        $this->assertSame('/dosen/dashboard', $n->fresh()->link);
    }

    /** Judul yang sama di dua item: jangan menebak, cukup ke halaman detailnya. */
    public function test_judul_ganda_tidak_ditebak(): void
    {
        $student = $this->userByUsername('3.34.23.2.01')->student;

        foreach ([1, 2] as $i) {
            LogBook::create([
                'student_id' => $student->id, 'title' => 'Sama',
                'activity' => "kegiatan {$i}", 'date' => "2024-03-0{$i}",
            ]);
        }

        $n = $this->notifLama('dosen1', 'log_book', 'Mahasiswa Satu mengisi log book: "Sama".');

        $this->artisan('simama:isi-link-notifikasi')->assertSuccessful();

        $this->assertSame("/dosen/mahasiswa/{$student->id}", $n->fresh()->link);
    }

    public function test_pratinjau_tidak_menyimpan(): void
    {
        $n = $this->notifLama('3.34.23.2.01', 'laporan', 'Laporan akhir magang disetujui dosen pembimbing.');

        $this->artisan('simama:isi-link-notifikasi', ['--pratinjau' => true])->assertSuccessful();

        $this->assertNull($n->fresh()->link, 'Mode pratinjau tidak boleh menyimpan apa pun.');
    }

    /** Yang sudah punya tujuan tak boleh ditimpa — perintah harus aman diulang. */
    public function test_tidak_menimpa_link_yang_sudah_ada(): void
    {
        $n = Notification::create([
            'user_id'  => $this->userByUsername('3.34.23.2.01')->id,
            'message'  => 'Bimbingan disetujui.', 'date' => now()->toDateString(),
            'category' => 'bimbingan', 'is_read' => false,
            'link'     => '/mahasiswa/bimbingan#khusus',
        ]);

        $this->artisan('simama:isi-link-notifikasi')->assertSuccessful();
        $this->artisan('simama:isi-link-notifikasi')->assertSuccessful();

        $this->assertSame('/mahasiswa/bimbingan#khusus', $n->fresh()->link);
    }

    /** Tujuan hasil pengisian harus benar-benar bisa dibuka, bukan sekadar string. */
    public function test_tujuan_hasil_pengisian_bisa_dibuka(): void
    {
        $n = $this->notifLama('3.34.23.2.01', 'seminar', 'Dosen membuka penjadwalan seminar: Seminar A');

        $this->artisan('simama:isi-link-notifikasi')->assertSuccessful();

        $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get("/mahasiswa/notifikasi/{$n->id}/open")
            ->assertRedirect('/mahasiswa/seminar');
    }
}
