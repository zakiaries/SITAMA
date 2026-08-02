<?php

namespace Tests\Feature\BlackBox;

use App\Models\ChatbotKnowledge;
use App\Models\CompanyRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

/**
 * PENGUJIAN BLACK-BOX — TABEL 4.4 FITUR KETUA PROGRAM STUDI
 *
 * Menjalankan skenario U-05, U-15, U-17, dan U-18 dari Tabel 3.25.
 */
class PengujianKaprodiTest extends FeatureTestCase
{
    private function pengajuan(string $perusahaan): CompanyRequest
    {
        return CompanyRequest::create([
            'student_id'   => $this->userByUsername('3.34.23.2.02')->student->id,
            'company_name' => $perusahaan,
            'pic_name'     => 'Budi Santoso',
            'pic_email'    => 'budi@' . Str::random(6) . '.test',
            'position'     => 'Developer',
            'start_date'   => now()->addMonth()->toDateString(),
            'status'       => 'pending',
        ]);
    }

    /** U-05 — Kaprodi menyetujui dan menolak pengajuan magang. */
    public function test_u05_kaprodi_menyetujui_dan_menolak_pengajuan(): void
    {
        Mail::fake();

        $kaprodi = $this->userByUsername('kaprodi');
        $mhs     = $this->userByUsername('3.34.23.2.02');

        // Pengajuan tampil di halaman verifikasi.
        $disetujui = $this->pengajuan('PT Disetujui');

        $this->actingAs($kaprodi)->get('/kaprodi/pengajuan-magang')
            ->assertOk()
            ->assertSee('PT Disetujui');

        // Menyetujui: status berubah dan mahasiswa menerima notifikasi.
        $this->actingAs($kaprodi)
            ->post("/kaprodi/pengajuan-magang/{$disetujui->id}/approve")
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $disetujui->fresh()->status);
        $this->assertTrue(
            Notification::where('user_id', $mhs->id)
                ->where('category', 'pengajuan_magang')
                ->where('message', 'like', '%disetujui%')->exists(),
            'Notifikasi persetujuan tidak terkirim ke mahasiswa.'
        );

        // Menolak pengajuan lain: status berubah dan notifikasi penolakan terkirim.
        $ditolak = $this->pengajuan('PT Ditolak');

        $this->actingAs($kaprodi)
            ->post("/kaprodi/pengajuan-magang/{$ditolak->id}/reject", [
                'rejection_reason' => 'Bukti penerimaan tidak terbaca.',
            ])->assertSessionHasNoErrors();

        $this->assertSame('rejected', $ditolak->fresh()->status);
        $this->assertTrue(
            Notification::where('user_id', $mhs->id)
                ->where('message', 'like', '%ditolak%')->exists(),
            'Notifikasi penolakan tidak terkirim ke mahasiswa.'
        );
    }

    /** U-15 — Kaprodi menambah entri pengetahuan dan menonaktifkannya. */
    public function test_u15_kaprodi_mengelola_basis_pengetahuan_chatbot(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');
        $mhs     = $this->userByUsername('3.34.23.2.01');

        // Menambah entri baru.
        $this->actingAs($kaprodi)->post('/kaprodi/chatbot', [
            'pertanyaan' => 'Berapa lama durasi magang?',
            'kata_kunci' => 'durasi lama waktu magang berapa bulan',
            'jawaban'    => 'Durasi magang minimal tiga bulan sesuai ketentuan program studi.',
            'kategori'   => 'Umum',
            'is_active'  => 1,
        ])->assertSessionHasNoErrors();

        $entri = ChatbotKnowledge::where('pertanyaan', 'Berapa lama durasi magang?')->firstOrFail();
        $this->assertTrue((bool) $entri->is_active);

        // Entri aktif dipakai chatbot saat menjawab.
        $jawaban = $this->actingAs($mhs)
            ->postJson('/mahasiswa/chatbot/ask', ['message' => 'berapa lama durasi magang?'])
            ->assertOk()->json();

        $this->assertStringContainsString('tiga bulan', mb_strtolower(json_encode($jawaban)),
            'Entri baru tidak dipakai chatbot.');

        // Dinonaktifkan: tidak lagi dipakai menjawab.
        $this->actingAs($kaprodi)->post("/kaprodi/chatbot/{$entri->id}/toggle")
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $entri->fresh()->is_active, 'Entri tidak berubah menjadi nonaktif.');

        $sesudah = $this->actingAs($mhs)
            ->postJson('/mahasiswa/chatbot/ask', ['message' => 'berapa lama durasi magang?'])
            ->assertOk()->json();

        $this->assertStringNotContainsString('tiga bulan', mb_strtolower(json_encode($sesudah)),
            'Entri yang sudah dinonaktifkan masih dipakai menjawab.');
    }

    /** U-17 — Mengekspor rekapitulasi data magang ke berkas Excel. */
    public function test_u17_ekspor_rekapitulasi_ke_excel(): void
    {
        $response = $this->actingAs($this->userByUsername('kaprodi'))
            ->get('/kaprodi/dashboard/export-excel')
            ->assertOk();

        $tipe = $response->headers->get('content-type');

        $this->assertTrue(
            str_contains($tipe, 'spreadsheet') || str_contains($tipe, 'excel')
                || str_contains($tipe, 'octet-stream'),
            "Berkas yang diunduh bukan Excel (content-type: {$tipe})."
        );

        $this->assertNotEmpty($response->headers->get('content-disposition'),
            'Respons tidak menawarkan berkas untuk diunduh.');
    }

    /** U-18 — Mengakses halaman milik peran lain. */
    public function test_u18_akses_lintas_peran_ditolak(): void
    {
        $peta = [
            // pengguna            => halaman peran lain yang dicoba dibuka
            '3.34.23.2.01' => ['/kaprodi/mahasiswa', '/dosen/dashboard', '/dosen-industri/dashboard'],
            'dosen1'       => ['/kaprodi/mahasiswa', '/mahasiswa/logbook', '/dosen-industri/dashboard'],
            'industri1'    => ['/kaprodi/mahasiswa', '/mahasiswa/logbook', '/dosen/dashboard'],
            'kaprodi'      => ['/mahasiswa/logbook', '/dosen/dashboard', '/dosen-industri/dashboard'],
        ];

        foreach ($peta as $username => $halaman) {
            $user = $this->userByUsername($username);

            foreach ($halaman as $url) {
                $status = $this->actingAs($user)->get($url)->baseResponse->getStatusCode();

                $this->assertContains($status, [302, 403, 404],
                    "{$username} dapat membuka {$url} (status {$status}) — akses lintas peran tidak ditolak.");
            }
        }

        // Tamu tanpa login diarahkan ke halaman masuk.
        $this->post('/logout');
        $this->get('/kaprodi/mahasiswa')->assertRedirect('/login');
        $this->get('/mahasiswa/logbook')->assertRedirect('/login');
    }
}
