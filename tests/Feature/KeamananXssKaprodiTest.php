<?php

namespace Tests\Feature;

use App\Models\CompanyRequest;
use Illuminate\Support\Facades\Mail;
use Tests\FeatureTestCase;

/**
 * Stored XSS: email pembimbing industri diisi MAHASISWA saat mengajukan magang,
 * lalu ditampilkan di halaman Kaprodi. Dulu controller merakit HTML sendiri
 * ("... ke <strong>{email}</strong>.") dan blade merendernya dengan {!! !!},
 * sehingga skrip apa pun di dalam email itu jalan di browser Kaprodi — peran
 * dengan wewenang tertinggi.
 *
 * Aturan 'email' Laravel memakai RFCValidation yang mengizinkan local-part
 * berkutip, jadi payload seperti "<img src=x onerror=...>"@evil.com lolos
 * validasi. Tes ini menjaga dua sisi: payload memang lolos validasi (jadi
 * pertahanannya tak boleh bergantung pada validator), dan keluarannya ter-escape.
 */
class KeamananXssKaprodiTest extends FeatureTestCase
{
    private const PAYLOAD = '"<img src=x onerror=alert(1)>"@evil.com';

    /** Kalau ini gagal, berarti validator sudah menyaring — pertahanan lain boleh disederhanakan. */
    public function test_payload_memang_lolos_validasi_email(): void
    {
        $v = validator(['email' => self::PAYLOAD], ['email' => 'nullable|email|max:255']);

        $this->assertTrue($v->passes(),
            'Validator menolak payload — perbarui tes ini, jangan hapus escaping-nya.');
    }

    public function test_email_berisi_html_tidak_dirender_mentah_di_halaman_kaprodi(): void
    {
        Mail::fake();

        $req = CompanyRequest::create([
            'student_id'   => $this->userByUsername('3.34.23.2.02')->student->id,
            'company_name' => 'PT Uji XSS',
            'pic_name'     => 'Budi',
            'pic_email'    => self::PAYLOAD,
            'status'       => 'pending',
        ]);

        $kaprodi = $this->userByUsername('kaprodi');

        $this->actingAs($kaprodi)
            ->post("/kaprodi/pengajuan-magang/{$req->id}/approve")
            ->assertSessionHasNoErrors();

        $html = $this->actingAs($kaprodi)->get('/kaprodi/pengajuan-magang')
            ->assertOk()->getContent();

        // Tag mentah tak boleh muncul; yang tampil harus versi ter-escape.
        $this->assertStringNotContainsString('<img src=x onerror=', $html,
            'Payload dirender mentah — XSS aktif di halaman Kaprodi.');
        $this->assertStringContainsString('&lt;img src=x onerror=', $html,
            'Payload tidak tampil sama sekali — pastikan tesnya masih menguji jalur yang benar.');
    }

    /** Penjaga menyeluruh: jangan ada {!! !!} baru yang menyuapkan data pengguna. */
    public function test_output_mentah_hanya_di_tempat_yang_sudah_ditinjau(): void
    {
        $diizinkan = [
            'components/icon.blade.php',   // array ikon tetap di dalam komponen
            'dosen/seminar/qr.blade.php',  // SVG QR dibangkitkan server
        ];

        $temuan = [];
        $dasar  = resource_path('views') . DIRECTORY_SEPARATOR;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            if (! str_contains(file_get_contents($file->getPathname()), '{!!')) {
                continue;
            }

            $relatif = str_replace([$dasar, DIRECTORY_SEPARATOR], ['', '/'], $file->getPathname());

            if (! in_array($relatif, $diizinkan, true)) {
                $temuan[] = $relatif;
            }
        }

        $this->assertSame([], $temuan,
            'Ada {!! !!} baru. Pakai {{ }} kecuali isinya benar-benar dibangkitkan server, '
            . 'lalu daftarkan di sini beserta alasannya.');
    }
}
