<?php

namespace Tests\Feature\BlackBox;

use App\Models\ChatbotKnowledge;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\Guidance;
use App\Models\JobListing;
use App\Models\LogBook;
use App\Models\Notification;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\FeatureTestCase;

/**
 * PENGUJIAN BLACK-BOX — TABEL 4.1 FITUR MAHASISWA
 *
 * Menjalankan skenario U-01, U-03, U-04, U-06, U-07, U-08, U-10, U-11, U-13,
 * U-14, dan U-16 dari Tabel 3.25 Rencana Pengujian Black-Box.
 *
 * Diuji lewat antarmuka HTTP seperti yang dilakukan pengguna: mengirim
 * formulir, membuka halaman, menekan tombol. Yang diperiksa hanya keluaran
 * yang terlihat — halaman yang muncul, pesan galat, dan data yang tersimpan —
 * tanpa menyentuh logika internalnya.
 */
class PengujianMahasiswaTest extends FeatureTestCase
{
    /** U-01 — Login dengan kredensial benar dan salah. */
    public function test_u01_login_benar_dan_salah(): void
    {
        // Kredensial benar: diarahkan ke dasbor sesuai peran.
        $this->post('/login', [
            'username' => '3.34.23.2.01',
            'password' => 'password',
        ])->assertRedirect('/mahasiswa/dashboard');

        $this->assertAuthenticated();

        // Kredensial salah: kembali ke form dengan pesan galat, tidak masuk.
        $this->post('/logout');
        $this->post('/login', [
            'username' => '3.34.23.2.01',
            'password' => 'salah-total',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }

    /** U-03 — Membuka daftar dan detail lowongan. */
    public function test_u03_daftar_dan_detail_lowongan(): void
    {
        $company = Company::firstOrFail();

        $lowongan = JobListing::create([
            'company_id'   => $company->id,
            'company_name' => $company->name,
            'title'        => 'Junior Web Developer',
            'bidang'       => 'Pengembangan Perangkat Lunak',
            'location'     => 'Semarang',
            'pic_name'     => 'Budi',
            'pic_email'    => 'budi@uji.test',
            'status'       => 'active',
        ]);

        $mhs = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($mhs)->get('/mahasiswa/lowongan')
            ->assertOk()
            ->assertSee('Junior Web Developer')
            ->assertSee($company->name);

        $this->actingAs($mhs)->get("/mahasiswa/lowongan/{$lowongan->id}")
            ->assertOk()
            ->assertSee('Junior Web Developer')
            ->assertSee($company->name)
            ->assertSee('Semarang');   // kolom "bidang" dipakai untuk filter & chatbot,
                                        // tidak ditampilkan di halaman detail
    }

    /** U-04 — Mengajukan magang dengan dan tanpa bukti penerimaan. */
    public function test_u04_pengajuan_magang_dengan_dan_tanpa_bukti(): void
    {
        Storage::fake('local');
        $mhs = $this->userByUsername('3.34.23.2.02'); // sudah punya dospem, belum magang

        $isian = [
            'company_name' => 'PT Uji Pengajuan',
            'pic_name'     => 'Budi',
            'pic_email'    => 'budi@uji.test',
            'pic_phone'    => '081234567890',
            'position'     => 'Developer',
            'bidang'       => 'IT',
            'start_date'   => now()->addMonth()->toDateString(),
        ];

        // Tanpa bukti penerimaan: ditolak dengan pesan validasi.
        $this->from('/mahasiswa/ajukan-magang')->actingAs($mhs)
            ->post('/mahasiswa/ajukan-magang', $isian)
            ->assertSessionHasErrors('proof_file');

        $this->assertSame(0, CompanyRequest::where('company_name', 'PT Uji Pengajuan')->count());

        // Dengan bukti penerimaan: tersimpan berstatus menunggu review.
        $this->actingAs($mhs)->post('/mahasiswa/ajukan-magang', $isian + [
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $pengajuan = CompanyRequest::where('company_name', 'PT Uji Pengajuan')->firstOrFail();
        $this->assertSame('pending', $pengajuan->status);
        $this->assertNotNull($pengajuan->proof_file);
    }

    /** U-06 — Menambah, melihat, dan menghapus entri logbook. */
    public function test_u06_logbook_tambah_lihat_hapus(): void
    {
        $mhs = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));

        // Tambah dua entri.
        foreach ([['Hari pertama', '2024-07-01'], ['Hari kedua', '2024-07-02']] as [$judul, $tgl]) {
            $this->actingAs($mhs)->post('/mahasiswa/logbook', [
                'title' => $judul, 'activity' => 'kegiatan ' . $judul, 'date' => $tgl,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(2, LogBook::where('student_id', $mhs->student->id)->count());

        // Lihat: keduanya tampil di halaman.
        $this->actingAs($mhs)->get('/mahasiswa/logbook')
            ->assertOk()
            ->assertSee('Hari pertama')
            ->assertSee('Hari kedua');

        // Hapus salah satu.
        $lb = LogBook::where('title', 'Hari pertama')->firstOrFail();
        $this->actingAs($mhs)->delete("/mahasiswa/logbook/{$lb->id}")
            ->assertSessionHasNoErrors();

        $this->assertNull(LogBook::find($lb->id));
        $this->actingAs($mhs)->get('/mahasiswa/logbook')
            ->assertOk()
            ->assertDontSee('Hari pertama')
            ->assertSee('Hari kedua');
    }

    /** U-07 — Mengajukan bimbingan (beserta berkas) dan menerima permintaan revisi. */
    public function test_u07_bimbingan_diajukan_dan_direvisi(): void
    {
        Storage::fake('local');
        $mhs   = $this->magangBerjalan($this->userByUsername('3.34.23.2.01'));
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($mhs)->post('/mahasiswa/bimbingan', [
            'title'    => 'Konsultasi Bab 1',
            'activity' => 'membahas rumusan masalah',
            'date'     => '2024-07-01',
            'file'     => UploadedFile::fake()->create('draft.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $bimbingan = Guidance::where('title', 'Konsultasi Bab 1')->firstOrFail();
        $this->assertSame('pending', $bimbingan->status);
        $this->assertNotNull($bimbingan->name_file, 'Berkas lampiran tidak tersimpan.');

        // Dosen meminta revisi.
        $this->actingAs($dosen)->post(
            "/dosen/mahasiswa/{$mhs->student->id}/bimbingan/{$bimbingan->id}/revisi",
            ['note' => 'Perbaiki latar belakang.']
        )->assertSessionHasNoErrors();

        $this->assertSame('rejected', $bimbingan->fresh()->status);
        $this->assertSame('Perbaiki latar belakang.', $bimbingan->fresh()->lecturer_note);

        // Mahasiswa melihat statusnya berubah beserta catatan dosen.
        $this->actingAs($mhs)->get('/mahasiswa/bimbingan')
            ->assertOk()
            ->assertSee('Perbaiki latar belakang.');
    }

    /** U-08 — Mengunggah laporan dan meninjau hasil peninjauan dosen. */
    public function test_u08_laporan_diunggah_dan_ditinjau(): void
    {
        Storage::fake('local');
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($mhs)->post('/mahasiswa/laporan', [
            'title' => 'Laporan Akhir Magang',
            'file'  => UploadedFile::fake()->create('laporan.pdf', 200, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $laporan = $mhs->student->fresh()->report;
        $this->assertNotNull($laporan);
        $this->assertSame('pending', $laporan->status);

        $this->actingAs($dosen)->post(
            "/dosen/mahasiswa/{$mhs->student->id}/laporan/{$laporan->id}/approve",
            ['note' => 'Sudah sesuai.']
        )->assertSessionHasNoErrors();

        $this->assertSame('approved', $laporan->fresh()->status);

        $this->actingAs($mhs)->get('/mahasiswa/laporan')->assertOk();
    }

    /** U-10 — Mahasiswa mengisi ketersediaan tanggal seminar yang dibuat dosen. */
    public function test_u10_mahasiswa_mengisi_ketersediaan_seminar(): void
    {
        $mhs   = $this->userByUsername('3.34.23.2.01');
        $dosen = $this->userByUsername('dosen1');

        $this->actingAs($dosen)->post('/dosen/seminar', [
            'title'       => 'Seminar Hasil Magang 2025/2026',
            'description' => 'Seminar hasil pelaksanaan magang.',
            'program'     => 'Teknik Informatika',
            'organizer'   => 'Politeknik Negeri Semarang',
            'student_ids' => [$mhs->student->id],
        ])->assertSessionHasNoErrors();

        $seminar = Seminar::where('title', 'Seminar Hasil Magang 2025/2026')->firstOrFail();
        $this->assertSame('draft', $seminar->status);

        $tanggal = now()->addDays(7)->toDateString() . ', ' . now()->addDays(8)->toDateString();

        $this->actingAs($mhs)->post("/mahasiswa/seminar/{$seminar->id}/availability", [
            'available_dates' => $tanggal,
        ])->assertSessionHasNoErrors();

        $penyaji = SeminarPresenter::where('seminar_id', $seminar->id)
            ->where('student_id', $mhs->student->id)->firstOrFail();

        $this->assertNotEmpty($penyaji->available_dates, 'Ketersediaan tanggal tidak tersimpan.');
    }

    /** U-11 — Audiens login lalu menekan tombol hadir melalui QR Code. */
    public function test_u11_presensi_qr_audiens(): void
    {
        $dosen   = $this->userByUsername('dosen1');
        $penyaji = $this->userByUsername('3.34.23.2.01');
        $audiens = $this->userByUsername('3.34.23.2.02');

        $seminar = Seminar::create([
            'lecturer_id' => $dosen->lecturer->id,
            'title'       => 'Seminar Uji Presensi',
            'program'     => 'Teknik Informatika',
            'organizer'   => 'Polines',
            'status'      => 'scheduled',
            'date'        => now()->toDateString(),
            'time'        => '09.00 - 11.00',
            'location'    => 'Ruang TI-01',
            'access_token' => \Illuminate\Support\Str::random(48),
        ]);
        SeminarPresenter::create(['seminar_id' => $seminar->id, 'student_id' => $penyaji->student->id]);

        $rt = $seminar->rotatingToken(intdiv(time(), Seminar::QR_INTERVAL));

        // Halaman daftar hadir terbuka bagi audiens yang sudah login.
        $this->actingAs($audiens)
            ->get("/seminar/hadir/{$seminar->access_token}?rt={$rt}")
            ->assertOk();

        // Menekan tombol hadir menyimpan kehadiran.
        $this->actingAs($audiens)->post("/seminar/hadir/{$seminar->access_token}", [
            'rt'   => $rt,
            'name' => $audiens->name,
        ])->assertOk();

        $this->assertSame(1, $seminar->attendances()->count());

        // Satu akun hanya tercatat satu kali walau menekan berulang.
        $this->actingAs($audiens)->post("/seminar/hadir/{$seminar->access_token}", [
            'rt'   => $rt,
            'name' => $audiens->name,
        ]);

        $this->assertSame(1, $seminar->fresh()->attendances()->count(),
            'Kehadiran tercatat ganda untuk satu akun.');
    }

    /** U-13 — Mengajukan pertanyaan prosedur magang kepada chatbot. */
    public function test_u13_chatbot_menjawab_pertanyaan_prosedural(): void
    {
        ChatbotKnowledge::create([
            'pertanyaan' => 'Apa syarat mengikuti seminar magang?',
            'kata_kunci' => 'syarat seminar magang sertifikat laporan nilai logbook',
            'jawaban'    => 'Syarat seminar magang: sertifikat sudah diunggah, laporan akhir di-ACC, '
                . 'nilai kedua pembimbing terisi, dan minimal 20 logbook.',
            'kategori'   => 'Seminar',
            'is_active'  => true,
        ]);

        $mhs = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($mhs)->get('/mahasiswa/chatbot')->assertOk();

        $jawaban = $this->actingAs($mhs)
            ->postJson('/mahasiswa/chatbot/ask', ['message' => 'syarat seminar magang apa saja?'])
            ->assertOk()->json();

        $teks = json_encode($jawaban);
        $this->assertStringContainsString('sertifikat', mb_strtolower($teks),
            'Chatbot tidak mengembalikan jawaban yang relevan.');
    }

    /** U-14 — Meminta rekomendasi tempat magang sesuai bidang. */
    public function test_u14_chatbot_merekomendasikan_tempat_magang(): void
    {
        $company = Company::firstOrFail();

        JobListing::create([
            'company_id'   => $company->id,
            'company_name' => $company->name,
            'title'        => 'Network Engineer',
            'bidang'       => 'Jaringan Komputer',
            'description'  => 'Mengelola infrastruktur jaringan, router, dan switch.',
            'skills'       => 'jaringan router switch mikrotik',
            'location'     => 'Semarang',
            'status'       => 'active',
        ]);

        $mhs = $this->userByUsername('3.34.23.2.01');

        $jawaban = $this->actingAs($mhs)
            ->postJson('/mahasiswa/chatbot/ask', ['message' => 'saya cari tempat magang bidang jaringan'])
            ->assertOk()->json();

        $teks = mb_strtolower(json_encode($jawaban));
        $this->assertTrue(
            str_contains($teks, 'network engineer') || str_contains($teks, mb_strtolower($company->name)),
            'Chatbot tidak merekomendasikan lowongan yang sesuai bidang.'
        );
    }

    /** U-16 — Menerima notifikasi dan menandainya terbaca. */
    public function test_u16_notifikasi_diterima_dan_ditandai_terbaca(): void
    {
        $mhs = $this->userByUsername('3.34.23.2.01');

        $notif = Notification::create([
            'user_id'     => $mhs->id,
            'message'     => 'Bimbingan "Konsultasi Bab 1" disetujui dosen pembimbing.',
            'detail_text' => 'Catatan: sudah sesuai.',
            'date'        => now()->toDateString(),
            'category'    => 'bimbingan',
            'is_read'     => false,
            'link'        => '/mahasiswa/bimbingan',
        ]);

        // Tampil di halaman notifikasi beserta kategorinya.
        $this->actingAs($mhs)->get('/mahasiswa/notifikasi')
            ->assertOk()
            ->assertSee('disetujui dosen pembimbing', false);

        // Dibuka: ditandai terbaca dan diarahkan ke hal yang diberitahukan.
        $this->actingAs($mhs)->get("/mahasiswa/notifikasi/{$notif->id}/open")
            ->assertRedirect('/mahasiswa/bimbingan');

        $this->assertTrue($notif->fresh()->is_read, 'Status baca tidak berubah.');
    }
}
