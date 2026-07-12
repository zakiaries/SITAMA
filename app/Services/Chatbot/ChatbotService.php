<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Cache;

/**
 * Mesin chatbot rekomendasi SITAMA.
 *
 * Chatbot ini bersifat retrieval-based: dari pertanyaan pengguna, sistem
 * mencari entri basis pengetahuan yang paling relevan menggunakan pembobotan
 * TF-IDF dan Cosine Similarity, lalu merekomendasikan jawabannya.
 *
 * Alur pada saat objek dibuat (fit sekali):
 *   1. Setiap entri KB dipraproses menjadi dokumen token.
 *   2. Vectorizer mempelajari IDF dari seluruh dokumen.
 *   3. Tiap dokumen diubah menjadi vektor TF-IDF dan disimpan.
 *
 * Alur menjawab (answer):
 *   1. Kueri pengguna dipraproses dengan pipeline yang sama.
 *   2. Kueri diubah menjadi vektor TF-IDF.
 *   3. Hitung cosine similarity kueri terhadap semua dokumen.
 *   4. Ambil skor tertinggi; jika >= ambang, kembalikan jawabannya beserta
 *      beberapa entri lain sebagai saran. Jika di bawah ambang, kembalikan
 *      pesan fallback tetapi tetap sertakan saran pertanyaan terdekat.
 */
class ChatbotService
{
    /**
     * Ambang minimal cosine similarity agar sebuah jawaban dianggap relevan.
     *
     * Dari pengujian: pertanyaan yang benar-benar relevan berskor >= ~0.40,
     * sedangkan pertanyaan menyimpang yang hanya berbagi satu kata umum
     * (mis. "tempat") berskor di sekitar 0.20. Ambang 0.25 berada di celah
     * antara keduanya sehingga menolak kecocokan semu tanpa membuang
     * pertanyaan yang sah.
     */
    private const THRESHOLD = 0.25;

    private TextPreprocessor $preprocessor;
    private TfIdfVectorizer $vectorizer;

    /**
     * Entri KB (data mentah).
     *
     * @var array<int, array{pertanyaan:string, kata_kunci:string, jawaban:string, kategori:string}>
     */
    private array $entries;

    /**
     * Vektor TF-IDF tiap dokumen KB, sejajar indeksnya dengan $entries.
     *
     * @var array<int, array<string, float>>
     */
    private array $documentVectors = [];

    public function __construct(?TextPreprocessor $preprocessor = null, ?TfIdfVectorizer $vectorizer = null)
    {
        $this->preprocessor = $preprocessor ?? new TextPreprocessor();
        $this->vectorizer   = $vectorizer ?? new TfIdfVectorizer();
        $this->entries      = KnowledgeBase::entries();

        $this->fit();
    }

    /**
     * Bangun korpus terpraproses, latih IDF, lalu vektorkan tiap dokumen.
     *
     * Hasil pelatihan (IDF + vektor dokumen) di-cache dengan kunci berbasis
     * hash isi KB, sehingga praproses + vektorisasi hanya dijalankan sekali
     * dan otomatis dilatih ulang ketika isi basis pengetahuan berubah.
     */
    private function fit(): void
    {
        $signature = md5(json_encode($this->entries));
        $cacheKey  = 'chatbot.tfidf.' . $signature;

        $model = Cache::remember($cacheKey, now()->addDay(), function () {
            $documents = [];
            foreach ($this->entries as $entry) {
                $documents[] = $this->preprocessor->process(
                    $entry['pertanyaan'] . ' ' . $entry['kata_kunci']
                );
            }

            $this->vectorizer->fit($documents);

            $vectors = [];
            foreach ($documents as $tokens) {
                $vectors[] = $this->vectorizer->transform($tokens);
            }

            return ['idf' => $this->vectorizer->export(), 'vectors' => $vectors];
        });

        $this->vectorizer->import($model['idf']);
        $this->documentVectors = $model['vectors'];
    }

    /**
     * Cari jawaban paling relevan untuk pertanyaan pengguna.
     *
     * @return array{
     *     found: bool,
     *     answer: string,
     *     score: float,
     *     question: string|null,
     *     category: string|null,
     *     suggestions: array<int, array{question:string}>
     * }
     */
    public function answer(string $query): array
    {
        $tokens = $this->preprocessor->process($query);
        $queryVector = $this->vectorizer->transform($tokens);

        // Hitung skor kemiripan terhadap seluruh dokumen KB.
        $scores = [];
        foreach ($this->documentVectors as $index => $docVector) {
            $scores[$index] = $this->vectorizer->cosine($queryVector, $docVector);
        }

        arsort($scores); // urutkan menurun berdasarkan skor
        $ranked = array_keys($scores);

        $bestIndex = $ranked[0] ?? null;
        $bestScore = $bestIndex !== null ? $scores[$bestIndex] : 0.0;

        $found = $bestIndex !== null && $bestScore >= self::THRESHOLD;

        // Susun saran: entri berskor tinggi berikutnya (maks 3), skor > 0.
        $suggestions = [];
        foreach (array_slice($ranked, 1) as $index) {
            if ($scores[$index] <= 0.0 || count($suggestions) >= 3) {
                continue;
            }
            $suggestions[] = ['question' => $this->entries[$index]['pertanyaan']];
        }

        if ($found) {
            $entry = $this->entries[$bestIndex];

            return [
                'found'       => true,
                'answer'      => $entry['jawaban'],
                'score'       => round($bestScore, 4),
                'question'    => $entry['pertanyaan'],
                'category'    => $entry['kategori'],
                'suggestions' => $suggestions,
            ];
        }

        // Fallback: tidak ada entri yang cukup mirip.
        return [
            'found'       => false,
            'answer'      => 'Maaf, saya belum menemukan jawaban yang cukup relevan untuk pertanyaan itu. '
                . 'Coba gunakan kata kunci lain (mis. "syarat seminar", "cara ajukan magang", "upload laporan"), '
                . 'atau hubungi Kaprodi/admin prodi untuk bantuan lebih lanjut.',
            'score'       => round($bestScore, 4),
            'question'    => null,
            'category'    => null,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Daftar pertanyaan populer untuk ditawarkan sebagai pintasan di UI.
     *
     * @return string[]
     */
    public function popularQuestions(): array
    {
        return [
            'Bagaimana cara mengajukan magang?',
            'Apa saja syarat mengajukan seminar?',
            'Bagaimana cara mengunggah laporan akhir?',
            'Bagaimana cara mengisi logbook?',
            'Bagaimana cara melihat nilai magang saya?',
            'Saya lupa kata sandi, harus bagaimana?',
        ];
    }
}
