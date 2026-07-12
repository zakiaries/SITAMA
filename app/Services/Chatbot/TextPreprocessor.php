<?php

namespace App\Services\Chatbot;

/**
 * Praproses teks Bahasa Indonesia untuk pipeline TF-IDF.
 *
 * Tahapan (dijalankan sama persis untuk dokumen korpus maupun kueri pengguna
 * agar hasil pencocokan konsisten):
 *   1. Case folding  — semua huruf dijadikan huruf kecil.
 *   2. Cleaning      — buang tanda baca/karakter non-alfanumerik.
 *   3. Tokenizing    — pecah kalimat menjadi token per kata.
 *   4. Stopword removal — buang kata umum yang tidak bermakna (dan, yang, ke, ...).
 *   5. Stemming ringan — buang imbuhan umum agar variasi kata bertemu pada
 *      akar yang sama (mis. "mengajukan"/"pengajuan" -> "ajukan").
 */
class TextPreprocessor
{
    /**
     * Stemmer Sastrawi (algoritma Nazief-Adriani) bila library tersedia,
     * atau null → fallback ke stemmer ringan bawaan.
     *
     * @var \Sastrawi\Stemmer\Stemmer|null
     */
    private $stemmer = null;

    public function __construct()
    {
        if (class_exists(\Sastrawi\Stemmer\StemmerFactory::class)) {
            $this->stemmer = (new \Sastrawi\Stemmer\StemmerFactory())->createStemmer();
        }
    }

    /**
     * Daftar stopword Bahasa Indonesia (kata umum tanpa makna pembeda).
     *
     * @var string[]
     */
    private const STOPWORDS = [
        'yang', 'untuk', 'pada', 'ke', 'para', 'namun', 'menurut', 'antara',
        'dia', 'dua', 'ia', 'seperti', 'jika', 'jika', 'sehingga', 'kembali',
        'dan', 'ini', 'itu', 'atau', 'juga', 'dari', 'dengan', 'akan', 'adalah',
        'di', 'oleh', 'karena', 'saya', 'aku', 'kamu', 'kita', 'kami', 'anda',
        'mereka', 'nya', 'ku', 'mu', 'sudah', 'belum', 'masih', 'saja', 'lagi',
        'agar', 'supaya', 'apa', 'apakah', 'bagaimana', 'kenapa', 'mengapa',
        'gimana', 'dimana', 'kapan', 'siapa', 'mana', 'yaitu', 'yakni', 'ada',
        'tidak', 'bukan', 'tanpa', 'bila', 'kalau', 'agar', 'serta', 'maupun',
        'tersebut', 'dalam', 'atas', 'bawah', 'setelah', 'sebelum', 'saat',
        'ketika', 'sambil', 'hingga', 'sampai', 'tentang', 'terhadap', 'bagi',
        'sebagai', 'secara', 'lebih', 'paling', 'sangat', 'agak', 'cukup',
        'bisa', 'dapat', 'boleh', 'harus', 'perlu', 'mau', 'ingin', 'hendak',
        'ya', 'iya', 'oke', 'nah', 'kok', 'sih', 'dong', 'deh', 'toh', 'pun',
        'punya', 'milik', 'buat', 'pakai', 'guna', 'jadi', 'menjadi', 'saya',
        'ku', 'aku', 'gitu', 'begitu', 'begini', 'nih', 'tuh', 'yg', 'utk',
        'dgn', 'tdk', 'gak', 'ga', 'nggak', 'engga', 'enggak', 'per', 'itu',
        // Kata generik (termasuk temporal) yang tidak membedakan topik FAQ.
        // "hari" penting: stemmer meringkas "harian" -> "hari", sehingga tanpa
        // ini kueri seperti "cuaca hari ini" salah cocok ke entri logbook.
        'hari', 'ini', 'now', 'sekarang', 'tadi', 'nanti', 'kemarin',
    ];

    /**
     * Normalisasi singkatan & salah ketik umum → bentuk baku.
     * Diterapkan per token sebelum stopword removal & stemming, agar variasi
     * penulisan mahasiswa tetap cocok dengan basis pengetahuan.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'pw' => 'password', 'pass' => 'password', 'pwd' => 'password',
        'logbok' => 'logbook', 'logbuk' => 'logbook',
        'sertipikat' => 'sertifikat', 'sertif' => 'sertifikat',
        'magan' => 'magang', 'magng' => 'magang',
        'bimbngan' => 'bimbingan', 'bimbngn' => 'bimbingan', 'bmbingan' => 'bimbingan',
        'seminr' => 'seminar', 'seminars' => 'seminar',
        'nilay' => 'nilai', 'nilé' => 'nilai',
        'laporn' => 'laporan', 'lapran' => 'laporan',
        'daftr' => 'daftar', 'dftar' => 'daftar',
        'profl' => 'profil', 'propil' => 'profil',
        'notif' => 'notifikasi', 'notip' => 'notifikasi',
    ];

    /**
     * Imbuhan awalan (prefiks) yang aman dibuang saat stemming.
     * Diurutkan dari yang paling panjang agar dicocokkan lebih dulu.
     *
     * @var string[]
     */
    private const PREFIXES = [
        'meng', 'meny', 'peng', 'peny', 'mem', 'men', 'pem', 'pen',
        'ber', 'ter', 'per', 'di', 'ke', 'se', 'me',
    ];

    /**
     * Imbuhan akhiran (sufiks) yang aman dibuang saat stemming.
     *
     * @var string[]
     */
    private const SUFFIXES = ['kannya', 'annya', 'kan', 'nya', 'lah', 'kah', 'an', 'i'];

    /**
     * Jalankan seluruh pipeline praproses dan kembalikan daftar token bersih.
     *
     * @return string[]
     */
    public function process(string $text): array
    {
        // 1. Case folding
        $text = mb_strtolower($text, 'UTF-8');

        // 2. Cleaning — sisakan huruf, angka, dan spasi
        $text = preg_replace('/[^a-z0-9\s]/u', ' ', $text);

        // 3. Tokenizing
        $tokens = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $result = [];
        foreach ($tokens as $token) {
            // Normalisasi singkatan / salah ketik umum ke bentuk baku.
            $token = self::ALIASES[$token] ?? $token;

            // Buang token yang terlalu pendek (1 huruf) atau angka murni.
            if (mb_strlen($token) < 2 || ctype_digit($token)) {
                continue;
            }

            // 4. Stopword removal
            if (in_array($token, self::STOPWORDS, true)) {
                continue;
            }

            // 5. Stemming — Sastrawi (Nazief-Adriani) bila ada, jika tidak
            //    pakai stemmer ringan bawaan.
            $result[] = $this->stemmer
                ? $this->stemmer->stem($token)
                : $this->lightStem($token);
        }

        return $result;
    }

    /**
     * Stemming ringan (fallback): buang satu awalan + satu akhiran yang umum.
     *
     * Konservatif — hanya membuang imbuhan bila akar yang tersisa masih
     * cukup panjang (>= 3 huruf), sehingga kata pendek tidak rusak.
     */
    private function lightStem(string $word): string
    {
        // Kata pendek dibiarkan apa adanya.
        if (mb_strlen($word) <= 4) {
            return $word;
        }

        $stemmed = $word;

        // Buang satu akhiran.
        foreach (self::SUFFIXES as $suffix) {
            $len = mb_strlen($suffix);
            if (mb_strlen($stemmed) - $len >= 3 && str_ends_with($stemmed, $suffix)) {
                $stemmed = mb_substr($stemmed, 0, mb_strlen($stemmed) - $len);
                break;
            }
        }

        // Buang satu awalan.
        foreach (self::PREFIXES as $prefix) {
            $len = mb_strlen($prefix);
            if (mb_strlen($stemmed) - $len >= 3 && str_starts_with($stemmed, $prefix)) {
                $stemmed = mb_substr($stemmed, $len);
                break;
            }
        }

        return $stemmed;
    }
}
