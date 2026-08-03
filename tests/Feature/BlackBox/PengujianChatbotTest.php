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
        $uji = [
            ['Bagaimana cara mengajukan magang?',            'Magang'],
            ['Apa saja syarat mengajukan seminar?',          'Seminar'],
            ['Bagaimana cara mengunggah laporan akhir?',     'Laporan'],
            ['Saya ingin daftar magang, mulai dari mana?',   'Magang'],   // parafrase no. 1
            ['Kapan saya boleh seminar?',                    'Seminar'],  // parafrase no. 2
            ['Saya lupa kata sandi, harus bagaimana?',       'Akun'],
        ];

        $baris = [];
        $sesuai = 0;

        foreach ($uji as $no => [$pertanyaan, $kategoriHarapan]) {
            $hasil = $this->chatbot->answer($pertanyaan);

            $cocok = $hasil['found'] && $hasil['category'] === $kategoriHarapan;
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
}
