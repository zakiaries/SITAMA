<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Cache;

/**
 * Mesin chatbot SITAMA — hybrid FAQ + rekomendasi tempat magang.
 *
 * Chatbot bersifat retrieval-based dan mengelola DUA basis pengetahuan yang
 * masing-masing di-index dengan TF-IDF:
 *   1. KB FAQ (chatbot_knowledges) — menjawab pertanyaan prosedur magang.
 *   2. KB Lowongan (job_listings)  — merekomendasikan tempat magang.
 *
 * Untuk tiap pertanyaan, sistem menghitung Cosine Similarity kueri terhadap
 * kedua korpus, mendeteksi maksud (rekomendasi vs pertanyaan), lalu menjawab
 * dari korpus yang paling relevan. Praproses (Sastrawi), TF-IDF, dan cosine
 * dipakai untuk keduanya sehingga tetap konsisten dengan judul penelitian.
 */
class ChatbotService
{
    /** Ambang cosine agar jawaban FAQ dianggap relevan. */
    private const FAQ_THRESHOLD = 0.25;

    /** Ambang cosine agar sebuah lowongan dianggap cocok direkomendasikan. */
    private const JOB_THRESHOLD = 0.18;

    /** Jumlah maksimal rekomendasi yang dikembalikan. */
    private const MAX_RECOMMENDATIONS = 4;

    /**
     * Versi index — dinaikkan bila logika praproses/vektorisasi berubah agar
     * cache lama otomatis diabaikan (kunci cache hanya berbasis isi KB).
     */
    private const INDEX_VERSION = 2;

    private TextPreprocessor $preprocessor;

    /** @var array<int, array{pertanyaan:string, kata_kunci:string, jawaban:string, kategori:string}> */
    private array $faqEntries = [];
    private TfIdfVectorizer $faqVectorizer;
    /** @var array<int, array<string, float>> */
    private array $faqVectors = [];

    /** @var array<int, array{id:int, company:string, title:string, bidang:?string, location:?string, contact:?string, text:string}> */
    private array $jobEntries = [];
    private TfIdfVectorizer $jobVectorizer;
    /** @var array<int, array<string, float>> */
    private array $jobVectors = [];

    public function __construct(?TextPreprocessor $preprocessor = null)
    {
        $this->preprocessor = $preprocessor ?? new TextPreprocessor();

        $this->faqEntries = KnowledgeBase::entries();
        $this->jobEntries = LowonganKnowledge::entries();

        [$this->faqVectorizer, $this->faqVectors] = $this->buildIndex(
            'faq', $this->faqEntries, fn ($e) => $e['pertanyaan'] . ' ' . $e['kata_kunci']
        );
        [$this->jobVectorizer, $this->jobVectors] = $this->buildIndex(
            'lowongan', $this->jobEntries, fn ($e) => $e['text']
        );
    }

    /**
     * Latih satu indeks TF-IDF dan kembalikan [vectorizer, vektor-dokumen].
     * Hasil di-cache dengan kunci berbasis hash isi korpus → otomatis dilatih
     * ulang saat data berubah (FAQ diedit kaprodi / lowongan bertambah).
     *
     * @param  array<int, array>  $entries
     * @param  callable(array):string  $docText
     * @return array{0: TfIdfVectorizer, 1: array<int, array<string,float>>}
     */
    private function buildIndex(string $prefix, array $entries, callable $docText): array
    {
        $signature = md5(json_encode($entries));
        $cacheKey  = "chatbot.$prefix.v" . self::INDEX_VERSION . ".$signature";

        $model = Cache::remember($cacheKey, now()->addDay(), function () use ($entries, $docText) {
            $documents = [];
            foreach ($entries as $entry) {
                $documents[] = $this->preprocessor->process($docText($entry));
            }

            $vectorizer = new TfIdfVectorizer();
            $vectorizer->fit($documents);

            $vectors = [];
            foreach ($documents as $tokens) {
                $vectors[] = $vectorizer->transform($tokens);
            }

            return ['idf' => $vectorizer->export(), 'vectors' => $vectors];
        });

        $vectorizer = new TfIdfVectorizer();
        $vectorizer->import($model['idf']);

        return [$vectorizer, $model['vectors']];
    }

    /**
     * Jawab pertanyaan pengguna: FAQ, rekomendasi tempat magang, atau fallback.
     *
     * @return array{
     *   type: string, found: bool, answer: string, score: float,
     *   question: string|null, category: string|null,
     *   suggestions: array<int, array{question:string}>,
     *   recommendations: array<int, array{company:string,title:string,bidang:?string,location:?string,contact:?string,score:float}>
     * }
     */
    public function answer(string $query): array
    {
        $tokens = $this->preprocessor->process($query);

        // Skor kemiripan ke kedua korpus.
        [$bestFaqIndex, $bestFaqScore, $faqScores] = $this->scoreAgainst($tokens, $this->faqVectorizer, $this->faqVectors);
        [, $bestJobScore, $jobScores] = $this->scoreAgainst($tokens, $this->jobVectorizer, $this->jobVectors);

        $recoIntent  = $this->looksLikeRecommendation($query);
        $hasListings = count($this->jobEntries) > 0;

        // 1) Maksud jelas "cari tempat magang".
        if ($recoIntent) {
            if ($hasListings && $bestJobScore > 0) {
                return $this->recommendationResponse($query, $jobScores, $bestJobScore);
            }
            return $this->recommendationEmpty($query, $hasListings);
        }

        // 2) Pertanyaan prosedur → FAQ.
        if ($bestFaqIndex !== null && $bestFaqScore >= self::FAQ_THRESHOLD) {
            return $this->faqResponse($bestFaqIndex, $bestFaqScore, $faqScores);
        }

        // 3) Tanpa kata kunci eksplisit, tapi ternyata sangat cocok ke lowongan.
        if ($hasListings && $bestJobScore >= self::JOB_THRESHOLD && $bestJobScore > $bestFaqScore) {
            return $this->recommendationResponse($query, $jobScores, $bestJobScore);
        }

        // 4) Fallback (tetap tawarkan saran FAQ terdekat).
        return $this->fallbackResponse($bestFaqScore, $faqScores);
    }

    /**
     * Hitung cosine kueri terhadap semua vektor sebuah indeks.
     *
     * @param  string[]  $tokens
     * @param  array<int, array<string,float>>  $vectors
     * @return array{0:int|null, 1:float, 2:array<int,float>}  [bestIndex, bestScore, scores]
     */
    private function scoreAgainst(array $tokens, TfIdfVectorizer $vectorizer, array $vectors): array
    {
        $queryVector = $vectorizer->transform($tokens);

        $scores = [];
        foreach ($vectors as $index => $vector) {
            $scores[$index] = $vectorizer->cosine($queryVector, $vector);
        }

        if (empty($scores)) {
            return [null, 0.0, []];
        }

        arsort($scores);
        $bestIndex = array_key_first($scores);

        return [$bestIndex, $scores[$bestIndex], $scores];
    }

    /** Deteksi maksud "mencari/merekomendasikan tempat magang". */
    private function looksLikeRecommendation(string $query): bool
    {
        $q = ' ' . mb_strtolower($query) . ' ';

        $triggers = [
            'rekomendasi', 'rekomen', 'cari magang', 'carikan', 'nyari magang',
            'mencari magang', 'cari tempat', 'tempat magang', 'lowongan',
            'magang di ', 'magang bidang', 'pengen magang', 'pengin magang',
            'mau magang', 'ingin magang', 'magang dimana', 'magang di mana',
            'saran tempat', 'saran magang', 'magang yang', 'magang untuk', 'magang buat',
        ];
        foreach ($triggers as $t) {
            if (str_contains($q, $t)) {
                return true;
            }
        }

        // Sebutan bidang juga menandakan maksud pencarian.
        $bidang = [
            'front end', 'frontend', 'back end', 'backend', 'full stack', 'fullstack',
            'mobile', 'ui/ux', 'data science', 'data analyst', 'jaringan', 'cyber',
            'security', 'multimedia', 'editor',
        ];
        foreach ($bidang as $b) {
            if (str_contains($q, $b)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int,float>  $jobScores  index => score (terurut menurun)
     */
    private function recommendationResponse(string $query, array $jobScores, float $bestScore): array
    {
        $recommendations = [];
        $lines = [];
        $rank = 0;

        foreach ($jobScores as $index => $score) {
            if ($score <= 0.0 || $rank >= self::MAX_RECOMMENDATIONS) {
                continue;
            }
            $entry = $this->jobEntries[$index];
            $rank++;

            $recommendations[] = [
                'company'  => $entry['company'],
                'title'    => $entry['title'],
                'bidang'   => $entry['bidang'],
                'location' => $entry['location'],
                'contact'  => $entry['contact'],
                'score'    => round($score, 4),
            ];

            $lines[] = $rank . '. ' . $entry['company']
                . ($entry['title'] ? ' — ' . $entry['title'] : '')
                . ($entry['location'] ? ' (' . $entry['location'] . ')' : '');
        }

        $answer = "Berikut tempat magang yang paling sesuai dengan pencarianmu:\n" . implode("\n", $lines);

        return [
            'type'            => 'recommendation',
            'found'           => true,
            'answer'          => $answer,
            'score'           => round($bestScore, 4),
            'question'        => null,
            'category'        => 'Rekomendasi Magang',
            'suggestions'     => [],
            'recommendations' => $recommendations,
        ];
    }

    private function recommendationEmpty(string $query, bool $hasListings): array
    {
        $answer = $hasListings
            ? 'Belum ada tempat magang di daftar kami yang cocok dengan pencarian itu. '
                . 'Coba kata kunci bidang lain (mis. "back end", "data", "multimedia"), '
                . 'atau lihat semua di menu Lowongan Magang.'
            : 'Daftar tempat magang belum tersedia saat ini. Silakan cek menu Lowongan Magang '
                . 'atau hubungi Kaprodi/admin prodi.';

        return [
            'type'            => 'recommendation',
            'found'           => false,
            'answer'          => $answer,
            'score'           => 0.0,
            'question'        => null,
            'category'        => 'Rekomendasi Magang',
            'suggestions'     => [],
            'recommendations' => [],
        ];
    }

    /**
     * @param  array<int,float>  $faqScores
     */
    private function faqResponse(int $bestIndex, float $bestScore, array $faqScores): array
    {
        $entry = $this->faqEntries[$bestIndex];

        return [
            'type'            => 'faq',
            'found'           => true,
            'answer'          => $entry['jawaban'],
            'score'           => round($bestScore, 4),
            'question'        => $entry['pertanyaan'],
            'category'        => $entry['kategori'],
            'suggestions'     => $this->faqSuggestions($faqScores, $bestIndex),
            'recommendations' => [],
        ];
    }

    /**
     * @param  array<int,float>  $faqScores
     */
    private function fallbackResponse(float $bestScore, array $faqScores): array
    {
        return [
            'type'            => 'fallback',
            'found'           => false,
            'answer'          => 'Maaf, saya belum menemukan jawaban yang cukup relevan. '
                . 'Untuk prosedur, coba kata kunci seperti "syarat seminar" atau "upload laporan". '
                . 'Untuk mencari tempat magang, sebutkan bidangmu, mis. "rekomendasi magang back end".',
            'score'           => round($bestScore, 4),
            'question'        => null,
            'category'        => null,
            'suggestions'     => $this->faqSuggestions($faqScores, null),
            'recommendations' => [],
        ];
    }

    /**
     * Saran pertanyaan FAQ terdekat (maks 3, skor > 0), selain yang sudah dipakai.
     *
     * @param  array<int,float>  $faqScores
     * @return array<int, array{question:string}>
     */
    private function faqSuggestions(array $faqScores, ?int $exclude): array
    {
        $suggestions = [];
        foreach ($faqScores as $index => $score) {
            if ($index === $exclude || $score <= 0.0 || count($suggestions) >= 3) {
                continue;
            }
            $suggestions[] = ['question' => $this->faqEntries[$index]['pertanyaan']];
        }
        return $suggestions;
    }

    /**
     * Pertanyaan populer untuk pintasan di UI (FAQ + contoh rekomendasi).
     *
     * @return string[]
     */
    public function popularQuestions(): array
    {
        return [
            'Rekomendasikan tempat magang bidang Back End',
            'Cari magang UI/UX di Semarang',
            'Bagaimana cara mengajukan magang?',
            'Apa saja syarat mengajukan seminar?',
            'Bagaimana cara mengunggah laporan akhir?',
            'Saya lupa kata sandi, harus bagaimana?',
        ];
    }
}
