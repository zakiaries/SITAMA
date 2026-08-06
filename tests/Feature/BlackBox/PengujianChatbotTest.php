<?php

namespace Tests\Feature\BlackBox;

use App\Models\Company;
use App\Models\JobListing;
use App\Services\Chatbot\ChatbotService;
use Database\Seeders\ChatbotKnowledgeSeeder;
use Illuminate\Support\Facades\Cache;
use Tests\FeatureTestCase;

/**
 * PENGUJIAN CHATBOT — TABEL 4.6 & 4.7
 *
 * Menjalankan pertanyaan uji persis seperti yang tercantum di Bab IV:
 *   Tabel 4.6 — enam pertanyaan prosedural, dua di antaranya parafrase
 *               (nomor 4 & 5) untuk menguji ketahanan terhadap variasi kalimat.
 *   Tabel 4.7 — enam kata kunci bidang untuk menguji rekomendasi tempat magang.
 *
 * Nilai kemiripan yang tercetak adalah cosine similarity yang benar-benar
 * dihitung mesin, bukan angka yang disusun manual.
 */
class PengujianChatbotTest extends FeatureTestCase
{
    private ChatbotService $chatbot;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();                       // indeks TF-IDF dibangun ulang dari korpus uji
        $this->seed(ChatbotKnowledgeSeeder::class);
        $this->siapkanLowongan();

        $this->chatbot = new ChatbotService();
    }

    /** Lowongan afiliasi yang mewakili tiap bidang pada Tabel 4.7. */
    private function siapkanLowongan(): void
    {
        $company = Company::firstOrFail();

        $daftar = [
            ['Back End Developer', 'Back End', 'Semarang',
             'Membangun REST API dan mengelola basis data.', 'php laravel mysql api backend'],
            ['Front End Developer', 'Front End', 'Semarang',
             'Membangun antarmuka web yang responsif.', 'html css javascript react frontend'],
            ['Full Stack Developer', 'Full Stack', 'Yogyakarta',
             'Menangani sisi server sekaligus antarmuka.', 'laravel vue fullstack api'],
            ['UI/UX Designer', 'UI/UX', 'Semarang',
             'Merancang antarmuka dan pengalaman pengguna.', 'figma wireframe prototype uiux desain'],
            ['IT Support', 'IT Support', 'Semarang',
             'Menangani perawatan perangkat dan jaringan kantor.', 'troubleshooting jaringan hardware helpdesk'],
            ['Data Analyst', 'Data', 'Jakarta',
             'Mengolah dan memvisualkan data operasional.', 'sql python visualisasi data analisis'],
        ];

        foreach ($daftar as [$judul, $bidang, $lokasi, $deskripsi, $skills]) {
            JobListing::create([
                'company_id'   => $company->id,
                'company_name' => $company->name,
                'title'        => $judul,
                'bidang'       => $bidang,
                'location'     => $lokasi,
                'description'  => $deskripsi,
                'skills'       => $skills,
                'status'       => 'active',
            ]);
        }
    }

    /** TABEL 4.6 — Pengujian jawaban pertanyaan prosedural. */
    public function test_tabel_46_jawaban_pertanyaan_prosedural(): void
    {
        // Entri yang diharapkan ditulis utuh, bukan sekadar kategorinya: Bab IV
        // menuntut jawaban dari entri yang tepat, dan pemeriksaan sebatas
        // kategori pernah meloloskan nomor 5 yang sebenarnya salah entri.
        $uji = [
            ['Bagaimana cara mengajukan magang?',          'Magang',
             'Bagaimana cara mengajukan magang di SIMAMA?'],
            ['Apa saja syarat mengajukan seminar?',        'Seminar',
             'Apa saja syarat agar bisa mengajukan seminar magang?'],
            ['Bagaimana cara mengunggah laporan akhir?',   'Laporan',
             'Bagaimana cara mengunggah laporan akhir magang?'],
            ['Saya ingin daftar magang, mulai dari mana?', 'Magang',      // parafrase no. 1
             'Bagaimana cara mengajukan magang di SIMAMA?'],
            ['Kapan saya boleh seminar?',                  'Seminar',     // parafrase no. 2
             'Apa saja syarat agar bisa mengajukan seminar magang?'],
            ['Saya lupa kata sandi, harus bagaimana?',     'Akun',
             'Saya lupa kata sandi, bagaimana cara reset password?'],
        ];

        $baris = [];
        $sesuai = 0;

        foreach ($uji as $no => [$pertanyaan, $kategoriHarapan, $entriHarapan]) {
            $hasil = $this->chatbot->answer($pertanyaan);

            $cocok = $hasil['found']
                && $hasil['category'] === $kategoriHarapan
                && $hasil['question'] === $entriHarapan;
            $sesuai += $cocok ? 1 : 0;

            $baris[] = sprintf(
                "%d | %-44s | %-9s | %-46s | %.4f | %s",
                $no + 1,
                mb_strimwidth($pertanyaan, 0, 44),
                $hasil['category'] ?? '-',
                mb_strimwidth($hasil['question'] ?? '(tidak ditemukan)', 0, 46),
                $hasil['score'],
                $cocok ? 'Sesuai' : 'TIDAK'
            );

            $this->assertTrue($hasil['found'],
                "Pertanyaan \"{$pertanyaan}\" tidak terjawab (skor {$hasil['score']}).");
            $this->assertSame($kategoriHarapan, $hasil['category'],
                "Pertanyaan \"{$pertanyaan}\" dijawab dari kategori {$hasil['category']}, "
                . "seharusnya {$kategoriHarapan}.");
        }

        fwrite(STDERR, "\n\n=== TABEL 4.6 — Jawaban Pertanyaan Prosedural ===\n"
            . "No | Pertanyaan Uji | Kategori | Entri KB yang Dicocokkan | Cosine | Ket\n"
            . implode("\n", $baris)
            . "\n>>> {$sesuai}/" . count($uji) . " pertanyaan terjawab benar\n");
    }

    /** TABEL 4.7 — Pengujian rekomendasi tempat magang. */
    public function test_tabel_47_rekomendasi_tempat_magang(): void
    {
        $uji = [
            ['Back End',          'Back End Developer'],
            ['Front End',         'Front End Developer'],
            ['Full Stack',        'Full Stack Developer'],
            ['UI/UX di Semarang', 'UI/UX Designer'],
            ['IT Support',        'IT Support'],
            ['Data',              'Data Analyst'],
        ];

        $baris = [];
        $sesuai = 0;

        foreach ($uji as $no => [$kataKunci, $harapan]) {
            $hasil = $this->chatbot->answer("rekomendasi magang {$kataKunci}");

            $teratas = $hasil['recommendations'][0] ?? null;
            $cocok   = $teratas && $teratas['title'] === $harapan;
            $sesuai += $cocok ? 1 : 0;

            $baris[] = sprintf(
                "%d | %-18s | %-22s | %.4f | %d rekomendasi | %s",
                $no + 1,
                $kataKunci,
                $teratas['title'] ?? '(kosong)',
                $teratas['score'] ?? 0,
                count($hasil['recommendations']),
                $cocok ? 'Sesuai' : 'TIDAK'
            );

            $this->assertSame('recommendation', $hasil['type'],
                "Kata kunci \"{$kataKunci}\" tidak dikenali sebagai permintaan rekomendasi.");
            $this->assertNotNull($teratas,
                "Kata kunci \"{$kataKunci}\" tidak menghasilkan rekomendasi apa pun.");
            $this->assertSame($harapan, $teratas['title'],
                "Rekomendasi teratas untuk \"{$kataKunci}\" adalah {$teratas['title']}, "
                . "seharusnya {$harapan}.");
        }

        fwrite(STDERR, "\n\n=== TABEL 4.7 — Rekomendasi Tempat Magang ===\n"
            . "No | Kata Kunci Bidang | Rekomendasi Teratas | Cosine | Jumlah | Ket\n"
            . implode("\n", $baris)
            . "\n>>> {$sesuai}/" . count($uji) . " kata kunci direkomendasikan tepat\n");
    }

    /**
     * Batas bawah: pertanyaan di luar cakupan tidak boleh dijawab asal.
     *
     * Ini yang membedakan sistem berambang dari sistem yang selalu mengembalikan
     * dokumen termirip — tanpa ambang, pertanyaan apa pun akan tetap dijawab.
     */
    public function test_pertanyaan_di_luar_cakupan_ditolak_dengan_jujur(): void
    {
        $luar = [
            'Bagaimana cuaca besok di Semarang?',
            'Siapa presiden Indonesia sekarang?',
            'Resep nasi goreng enak',
        ];

        $baris = [];

        foreach ($luar as $pertanyaan) {
            $hasil = $this->chatbot->answer($pertanyaan);

            $baris[] = sprintf("%-42s | %-12s | %.4f | %s",
                mb_strimwidth($pertanyaan, 0, 42), $hasil['type'], $hasil['score'],
                $hasil['found'] ? 'DIJAWAB' : 'ditolak');

            $this->assertFalse($hasil['found'],
                "Pertanyaan di luar cakupan \"{$pertanyaan}\" tetap dijawab "
                . "(tipe {$hasil['type']}, skor {$hasil['score']}).");
        }

        fwrite(STDERR, "\n\n=== Batas bawah — pertanyaan di luar cakupan ===\n"
            . "Pertanyaan | Tipe | Cosine | Hasil\n" . implode("\n", $baris) . "\n");
    }

    /** Saran pertanyaan lain ikut ditawarkan, sesuai Hasil yang Diharapkan U-13. */
    public function test_jawaban_faq_menyertakan_saran_pertanyaan_lain(): void
    {
        $hasil = $this->chatbot->answer('Bagaimana cara mengajukan magang?');

        $this->assertTrue($hasil['found']);
        $this->assertNotEmpty($hasil['suggestions'],
            'Jawaban FAQ tidak menyertakan saran pertanyaan lain.');
        $this->assertLessThanOrEqual(3, count($hasil['suggestions']));
    }

    /**
     * TABEL 4.8 — Ketahanan chatbot terhadap variasi pertanyaan.
     *
     * Delapan belas pertanyaan yang sama persis dengan Bab IV, dikelompokkan
     * menjadi kata kunci sama, parafrase, salah ketik, dan di luar cakupan.
     * Harapan tiap baris ditulis eksplisit: untuk kelompok yang harus dijawab,
     * entri basis pengetahuan yang benar; untuk yang di luar cakupan, null yang
     * berarti wajib ditolak.
     */
    public function test_tabel_48_ketahanan_terhadap_variasi_pertanyaan(): void
    {
        $magang   = 'Bagaimana cara mengajukan magang di SIMAMA?';
        $seminar  = 'Apa saja syarat agar bisa mengajukan seminar magang?';
        $laporan  = 'Bagaimana cara mengunggah laporan akhir magang?';
        $sandi    = 'Saya lupa kata sandi, bagaimana cara reset password?';
        $bimbing  = 'Bagaimana cara mengajukan bimbingan ke dosen pembimbing?';
        $absensi  = 'Bagaimana absensi seminar dengan QR Code dan berita acara?';

        // [kelompok, pertanyaan, entri KB yang benar (null = wajib ditolak)]
        $uji = [
            ['Kata kunci sama', $magang,  $magang],
            ['Kata kunci sama', $seminar, $seminar],
            ['Kata kunci sama', $laporan, $laporan],
            ['Kata kunci sama', $sandi,   $sandi],
            ['Kata kunci sama', 'Bagaimana cara mengajukan bimbingan?', $bimbing],
            ['Kata kunci sama', 'Bagaimana absensi seminar dengan QR?', $absensi],
            ['Parafrase',   'Saya ingin daftar magang, mulai dari mana?',    $magang],
            ['Parafrase',   'Gimana caranya upload laporan?',                $laporan],
            ['Parafrase',   'Password saya hilang',                          $sandi],
            ['Parafrase',   'Mau konsultasi sama dosen pembimbing caranya?', $bimbing],
            ['Parafrase',   'Absen seminar pakai apa?',                      $absensi],
            ['Parafrase',   'Syarat seminar apa saja ya?',                   $seminar],
            ['Salah ketik', 'bgaimana cara mngajukan magang',                $magang],
            ['Salah ketik', 'cara upload laporan akhr',                      $laporan],
            ['Salah ketik', 'lupa pasword gimana',                           $sandi],
            ['Luar cakupan', 'Berapa harga tiket kereta ke Jakarta?',        null],
            ['Luar cakupan', 'Siapa presiden Indonesia?',                    null],
            ['Luar cakupan', 'Resep nasi goreng enak',                       null],
        ];

        $baris = [];
        $sesuai = 0;
        $gagal = [];

        foreach ($uji as $i => [$kelompok, $pertanyaan, $harapan]) {
            $hasil = $this->chatbot->answer($pertanyaan);

            $cocok = $harapan === null
                ? ! $hasil['found']
                : ($hasil['found'] && $hasil['question'] === $harapan);

            $sesuai += $cocok ? 1 : 0;
            if (! $cocok) {
                $gagal[] = $i + 1;
            }

            $jenis = ['faq' => 'FAQ', 'recommendation' => 'Rekomendasi', 'fallback' => 'Fallback'];

            $baris[] = sprintf('%2d | %-15s | %-52s | %-11s | %.4f | %s',
                $i + 1, $kelompok, mb_strimwidth($pertanyaan, 0, 52),
                $jenis[$hasil['type']] ?? $hasil['type'], $hasil['score'],
                $cocok ? 'Sesuai' : 'Tidak sesuai');
        }

        fwrite(STDERR, "\n\n=== TABEL 4.8 — Ketahanan Chatbot terhadap Variasi Pertanyaan ===\n"
            . "No | Kelompok | Pertanyaan Uji | Jenis Keluaran | Nilai Kemiripan | Keterangan\n"
            . implode("\n", $baris)
            . sprintf("\n>>> %d/%d sesuai = %.2f%%\n", $sesuai, count($uji),
                $sesuai / count($uji) * 100));

        // Nomor 11 "Absen seminar pakai apa?" dulu gagal karena stemmer tidak
        // menyatukan "absen" dengan "absensi", sehingga kuerinya menyusut jadi
        // "seminar" saja. Kata "absen" kini terdaftar pada entri absensi QR,
        // dan seluruh baris harus lulus.
        $this->assertSame([], $gagal,
            'Ada baris Tabel 4.8 yang gagal: ' . implode(', ', $gagal));
        $this->assertSame(18, $sesuai);
    }

    /**
     * Nama kota saja tidak boleh memicu rekomendasi.
     *
     * Sebelum penjagaan ini ada, "Cuaca Semarang hari ini" mencetak cosine
     * 0,4313 ke korpus lowongan pada data produksi lalu dijawab dengan daftar
     * tempat magang — hanya karena kata "Semarang" ada di kolom lokasi.
     */
    public function test_nama_kota_saja_tidak_memicu_rekomendasi(): void
    {
        foreach (['Cuaca Semarang hari ini', 'Ada apa di Jakarta?',
                  'Kuliner khas Yogyakarta'] as $pertanyaan) {
            $hasil = $this->chatbot->answer($pertanyaan);

            $this->assertFalse($hasil['found'],
                "\"{$pertanyaan}\" dijawab sebagai {$hasil['type']} "
                . "(skor {$hasil['score']}), padahal hanya menyebut nama tempat.");
        }

        // Pencarian yang memang menyebut kota tetap harus berjalan.
        $hasil = $this->chatbot->answer('cari magang di Semarang');
        $this->assertTrue($hasil['found'], 'Pencarian magang berdasarkan kota ikut terblokir.');
        $this->assertNotEmpty($hasil['recommendations']);
    }
}
