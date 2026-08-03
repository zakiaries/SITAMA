<?php

namespace Tests\Feature;

use App\Models\ChatbotKnowledge;
use App\Models\Company;
use App\Models\JobListing;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Support\Facades\Cache;
use Tests\FeatureTestCase;

/**
 * Chatbot tak bisa menjawab pertanyaan yang disalin persis dari basis
 * pengetahuannya sendiri.
 *
 * "Bagaimana cara mengajukan magang di SIMAMA?" adalah entri KB nomor 4, tapi
 * dijawab dengan daftar lowongan. Penyebabnya pemicu 'magang di ' di
 * looksLikeRecommendation(): "di" kata depan biasa, jadi kalimat tanya
 * prosedural ikut tertangkap dan dibelokkan ke jalur rekomendasi — yang pulang
 * SEBELUM skor FAQ sempat diperiksa. Model TF-IDF sudah menghitung 0,63 untuk
 * pertanyaan itu, lalu diabaikan.
 *
 * Yang paling merugikan: pertanyaan bersalah ketik skornya wajar-wajar saja
 * (0,35–0,44), sehingga menambal dengan "dahulukan FAQ bila skor ≥ 0,5" hanya
 * menyelamatkan kalimat yang rapi dan meninggalkan yang berantakan — padahal
 * toleransi salah ketik justru kekuatan yang diandalkan sistem ini.
 *
 * Perbaikannya membuang pemicunya, tanpa menyentuh TF-IDF maupun cosine.
 */
class ChatbotTakSalahRuteTest extends FeatureTestCase
{
    private ChatbotService $chatbot;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->siapkanLowongan();
        $this->siapkanFaq();

        $this->chatbot = new ChatbotService();
    }

    /** Lowongan supaya jalur rekomendasi benar-benar punya isi. */
    private function siapkanLowongan(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'PT Uji Rekomendasi'],
            ['verification_status' => 'verified']
        );

        $daftar = [
            ['Back End Developer', 'Back End', 'Semarang', 'php laravel mysql api backend'],
            ['UI/UX Designer',     'UI/UX',    'Semarang', 'figma wireframe prototype uiux desain'],
            ['Data Analyst',       'Data',     'Jakarta',  'sql python visualisasi data analisis'],
        ];

        foreach ($daftar as [$judul, $bidang, $lokasi, $skills]) {
            JobListing::create([
                'company_id'   => $company->id,
                'company_name' => $company->name,
                'title'        => $judul,
                'bidang'       => $bidang,
                'location'     => $lokasi,
                'description'  => "Lowongan {$judul} di {$lokasi}.",
                'skills'       => explode(' ', $skills),
                'status'       => 'active',
            ]);
        }
    }

    /** Entri KB yang jadi korban: bunyinya sendiri memuat "magang di". */
    private function siapkanFaq(): void
    {
        ChatbotKnowledge::firstOrCreate(
            ['pertanyaan' => 'Bagaimana cara mengajukan magang di SIMAMA?'],
            [
                'kata_kunci' => 'ajukan magang daftar magang pengajuan magang bukti penerimaan',
                'jawaban'    => 'Buka menu Ajukan Magang, isi data perusahaan dan pembimbing industri, '
                    . 'lalu unggah bukti penerimaan. Kaprodi akan mereview pengajuanmu.',
                'kategori'   => 'Magang',
            ]
        );
    }

    // ── Pertanyaan prosedural tak boleh dibelokkan ──────────────────────────

    /** @dataProvider pertanyaanProsedural */
    public function test_pertanyaan_prosedural_dijawab_faq(string $pertanyaan): void
    {
        $hasil = $this->chatbot->answer($pertanyaan);

        $this->assertSame('faq', $hasil['type'],
            "\"{$pertanyaan}\" dijawab sebagai {$hasil['type']}, seharusnya FAQ.");
        $this->assertTrue($hasil['found']);
    }

    public static function pertanyaanProsedural(): array
    {
        return [
            // Persis bunyi entri KB — kasus yang dilaporkan.
            'sama persis dengan entri KB' => ['Bagaimana cara mengajukan magang di SIMAMA?'],
            // Salah ketik: skornya rendah, jadi tambalan berbasis ambang gagal di sini.
            'salah ketik'                 => ['bgaimana cara mngajukan magang di simama'],
            'tidak baku'                  => ['gimana cara daftar magang di simama'],
            // Tanpa kata depan "di" — dulu pun sudah benar, dijaga agar tetap.
            'tanpa kata depan'            => ['Bagaimana cara mengajukan magang?'],
        ];
    }

    // ── Maksud pencarian tetap terbaca ──────────────────────────────────────

    /** @dataProvider permintaanRekomendasi */
    public function test_permintaan_rekomendasi_tetap_dikenali(string $pertanyaan): void
    {
        $hasil = $this->chatbot->answer($pertanyaan);

        $this->assertSame('recommendation', $hasil['type'],
            "\"{$pertanyaan}\" dijawab sebagai {$hasil['type']}, seharusnya rekomendasi.");
    }

    public static function permintaanRekomendasi(): array
    {
        return [
            'kata rekomendasi'  => ['Rekomendasikan tempat magang bidang Back End'],
            'kata cari'         => ['Cari magang UI/UX di Semarang'],
            'kata mau'          => ['Saya mau magang di Semarang'],
            'kata lowongan'     => ['lowongan magang data'],
            // Dijaga khusus: 'magang di mana' TIDAK boleh ikut terbuang bersama
            // 'magang di ' — bunyinya mirip tapi maksudnya jelas mencari tempat.
            'magang di mana'    => ['magang di mana saja yang tersedia?'],
            'magang dimana'     => ['magang dimana ya enaknya'],
        ];
    }

    /** Pemicu yang dibuang harus tetap tidak ada — ini inti perbaikannya. */
    public function test_pemicu_kata_depan_tidak_dihidupkan_lagi(): void
    {
        $berkas = file_get_contents(app_path('Services/Chatbot/ChatbotService.php'));

        $this->assertStringNotContainsString("'magang di ',", $berkas,
            "Pemicu 'magang di ' hidup lagi — pertanyaan prosedural akan dibelokkan "
            . 'ke rekomendasi seperti semula.');
    }

    /** TF-IDF & cosine tak ikut diubah: ambangnya harus tetap seperti di Bab 3. */
    public function test_ambang_tidak_bergeser(): void
    {
        $kelas = new \ReflectionClass(ChatbotService::class);

        $this->assertSame(0.25, $kelas->getConstant('FAQ_THRESHOLD'));
        $this->assertSame(0.18, $kelas->getConstant('JOB_THRESHOLD'));
    }
}
