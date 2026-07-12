<?php

namespace App\Services\Chatbot;

/**
 * Vectorizer TF-IDF + perhitungan Cosine Similarity (implementasi murni PHP).
 *
 * Alur:
 *   - fit()        : pelajari kosakata (vocabulary) dan nilai IDF dari korpus.
 *   - transform()  : ubah daftar token menjadi vektor bobot TF-IDF.
 *   - cosine()     : hitung kemiripan arah antara dua vektor (0..1).
 *
 * Rumus yang dipakai:
 *   TF(t,d)  = jumlah kemunculan term t pada dokumen d
 *   IDF(t)   = ln( (1 + N) / (1 + df(t)) ) + 1        (smoothed IDF, ala scikit-learn)
 *   bobot    = TF(t,d) * IDF(t)
 *   cosine   = (A · B) / (|A| * |B|)
 *
 * IDF yang di-smoothing dipilih agar tidak ada term bernilai nol walau muncul
 * di semua dokumen, dan aman dari pembagian nol pada korpus kecil.
 */
class TfIdfVectorizer
{
    /**
     * Nilai IDF per term. term => idf
     *
     * @var array<string, float>
     */
    private array $idf = [];

    /**
     * Pelajari kosakata dan IDF dari korpus.
     *
     * @param array<int, string[]> $documents Daftar dokumen; tiap dokumen berupa array token.
     */
    public function fit(array $documents): void
    {
        $n = count($documents);
        $documentFrequency = [];

        foreach ($documents as $tokens) {
            // Hitung tiap term sekali per dokumen (document frequency).
            foreach (array_unique($tokens) as $term) {
                $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;
            }
        }

        $this->idf = [];
        foreach ($documentFrequency as $term => $df) {
            $this->idf[$term] = log((1 + $n) / (1 + $df)) + 1;
        }
    }

    /**
     * Ubah daftar token menjadi vektor bobot TF-IDF.
     * Term yang tidak dikenal (tidak ada di korpus) diabaikan.
     *
     * @param  string[] $tokens
     * @return array<string, float> term => bobot
     */
    public function transform(array $tokens): array
    {
        $termFrequency = [];
        foreach ($tokens as $term) {
            $termFrequency[$term] = ($termFrequency[$term] ?? 0) + 1;
        }

        $vector = [];
        foreach ($termFrequency as $term => $tf) {
            if (isset($this->idf[$term])) {
                $vector[$term] = $tf * $this->idf[$term];
            }
        }

        return $vector;
    }

    /**
     * Cosine similarity antara dua vektor jarang (sparse) berbentuk map term => bobot.
     * Mengembalikan nilai 0..1 (0 = tidak mirip, 1 = arah identik).
     *
     * @param array<string, float> $a
     * @param array<string, float> $b
     */
    public function cosine(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        // Dot product hanya pada term yang beririsan.
        $dot = 0.0;
        foreach ($a as $term => $weight) {
            if (isset($b[$term])) {
                $dot += $weight * $b[$term];
            }
        }

        if ($dot == 0.0) {
            return 0.0;
        }

        $normA = sqrt(array_sum(array_map(fn($w) => $w * $w, $a)));
        $normB = sqrt(array_sum(array_map(fn($w) => $w * $w, $b)));

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / ($normA * $normB);
    }
}
