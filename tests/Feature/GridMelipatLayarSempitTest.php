<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Susunan banyak kolom harus melipat di layar sempit. Empat kartu angka
 * berjajar di layar 375px menyisakan ±80px per kartu — angkanya terpotong dan
 * labelnya jadi tumpukan satu huruf per baris.
 *
 * Dua penjaga di sini sengaja bersifat MENYAPU, bukan mendaftar halaman satu
 * per satu: halaman baru akan lahir setelah tes ini ditulis, dan yang paling
 * mungkin terjadi adalah orang menyalin pola grid dari halaman lama tanpa ikut
 * menyalin breakpoint-nya. Kalau tesnya cuma memeriksa daftar tetap, halaman
 * baru itu lolos diam-diam.
 */
class GridMelipatLayarSempitTest extends FeatureTestCase
{
    /** Berkas mati; isinya bawaan Laravel, bukan halaman SIMAMA. */
    private const DIABAIKAN = 'legacy/welcome.blade.php';

    /** Semua berkas yang memuat CSS: stylesheet utama + blade ber-<style>. */
    private function berkasBerCss(): array
    {
        $daftar = [public_path('css/simama.css')];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($iterator as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $path = str_replace('\\', '/', $file->getPathname());

            if (str_ends_with($path, self::DIABAIKAN)) {
                continue;
            }

            if (str_contains(file_get_contents($path), 'grid-template-columns')) {
                $daftar[] = $path;
            }
        }

        return $daftar;
    }

    /**
     * Isi tiap blok @media, dikembalikan sebagai potongan-potongan teks.
     *
     * Kurungnya dihitung manual, bukan dicocokkan regex: blok @media memuat
     * blok lain di dalamnya, sehingga `\{[^}]*\}` akan berhenti di kurung
     * tutup pertama dan mengira separuh aturannya berada di luar.
     */
    private function blokMedia(string $css): array
    {
        $blok = [];
        $pos  = 0;
        $len  = strlen($css);

        while (($mulai = strpos($css, '@media', $pos)) !== false) {
            $buka = strpos($css, '{', $mulai);

            if ($buka === false) {
                break;
            }

            $dalam = 1;
            $i     = $buka + 1;

            while ($i < $len && $dalam > 0) {
                if ($css[$i] === '{') {
                    $dalam++;
                } elseif ($css[$i] === '}') {
                    $dalam--;
                }
                $i++;
            }

            $blok[] = substr($css, $mulai, $i - $mulai);
            $pos    = $i;
        }

        return $blok;
    }

    /** Apakah nilai grid-template-columns ini berarti lebih dari satu kolom. */
    private function lebihDariSatuKolom(string $nilai): bool
    {
        $nilai = trim($nilai);

        // repeat(auto-fit/auto-fill, minmax(...)) sudah melipat sendiri.
        if (str_starts_with($nilai, 'repeat(auto')) {
            return false;
        }

        return (bool) preg_match('/repeat\(\s*[2-9]/', $nilai)
            || (bool) preg_match('/\S\s+\S/', $nilai);
    }

    /**
     * Penjaga utama: tiap susunan banyak kolom wajib punya aturan @media untuk
     * kelas yang sama, di berkas yang sama.
     */
    public function test_tiap_grid_banyak_kolom_punya_aturan_melipat(): void
    {
        $diperiksa = 0;

        foreach ($this->berkasBerCss() as $path) {
            $css   = file_get_contents($path);
            $blok  = $this->blokMedia($css);
            $luar  = str_replace($blok, '', $css);
            $dalam = implode("\n", $blok);

            /* Dibaca per aturan `selektor { isi }`, bukan disapu bebas.
               Selektornya dilarang memuat baris baru maupun kurung supaya
               pencocokan tak menyeberang keluar dari blok <style> — sempat
               terjadi: `@extends('layouts.dosen-industri')` terbaca sebagai
               selektor `.dosen-industri` milik aturan grid di bawahnya. */
            preg_match_all('/([^{}\n]+)\{([^{}]*)\}/', $luar, $cocok, PREG_SET_ORDER);

            foreach ($cocok as [, $selektor, $isi]) {
                if (! preg_match('/grid-template-columns\s*:\s*([^;}]+)/', $isi, $m)) {
                    continue;
                }

                if (! $this->lebihDariSatuKolom($m[1])) {
                    continue;
                }

                preg_match_all('/\.([\w-]+)/', $selektor, $kelas);

                if ($kelas[1] === []) {
                    continue; // aturan tanpa kelas — tak ada yang bisa disasar @media
                }

                $tertangani = false;

                foreach ($kelas[1] as $k) {
                    if (str_contains($dalam, '.' . $k)) {
                        $tertangani = true;
                        break;
                    }
                }

                $this->assertTrue($tertangani, sprintf(
                    '%s: "%s" disusun banyak kolom (%s) tapi tak punya aturan @media '
                    . 'yang melipatnya — di layar ponsel isinya akan terjepit.',
                    basename($path), trim($selektor), trim($m[1])
                ));

                $diperiksa++;
            }
        }

        $this->assertGreaterThan(5, $diperiksa,
            'Penyapunya tak menemukan grid sama sekali — polanya kemungkinan sudah tak cocok.');
    }

    /**
     * Grid banyak kolom tak boleh ditulis sebagai atribut style= di elemennya.
     *
     * Atribut inline tak bisa dijangkau @media mana pun, jadi ia akan bertahan
     * empat kolom sampai ke layar tersempit. Kalau butuh dua kolom, pakai kelas
     * bersama .grid-duo yang sudah membawa breakpoint-nya.
     */
    public function test_tak_ada_grid_kolom_tetap_yang_ditulis_inline(): void
    {
        foreach ($this->berkasBerCss() as $path) {
            preg_match_all(
                '/style="[^"]*grid-template-columns\s*:\s*([^;"]+)/',
                file_get_contents($path),
                $cocok,
                PREG_SET_ORDER
            );

            foreach ($cocok as [, $nilai]) {
                $this->assertFalse($this->lebihDariSatuKolom($nilai), sprintf(
                    '%s: grid "%s" ditulis sebagai atribut style= sehingga tak bisa '
                    . 'dilipat @media. Pakai kelas .grid-duo, atau repeat(auto-fit, minmax(...)).',
                    basename($path), trim($nilai)
                ));
            }
        }
    }

    /** Kelas bersama di stylesheet utama. */
    public function test_kelas_bersama_melipat(): void
    {
        $css = preg_replace('/\s+/', '', file_get_contents(public_path('css/simama.css')));

        $this->assertStringContainsString('.grid-2{grid-template-columns:1fr;}', $css,
            'Grid dua kolom bersama tak melipat jadi satu kolom.');
        $this->assertStringContainsString('.grid-duo{grid-template-columns:1fr;}', $css,
            'Baris dua kolom formulir tak melipat jadi satu kolom.');
        $this->assertStringContainsString('.grid-4{grid-template-columns:repeat(2,1fr);}', $css,
            'Kartu angka harus turun jadi dua berjajar di layar sempit.');
    }

    /** Halaman yang dulu memakai grid inline harus benar-benar memakai kelasnya. */
    public function test_halaman_formulir_memakai_kelas_bersama(): void
    {
        $halaman = [
            'kaprodi/lowongan/form',
            'kaprodi/mahasiswa/detail',
            'kaprodi/pengajuan-magang/index',
            'mahasiswa/ajukan-magang/index',
        ];

        foreach ($halaman as $h) {
            $this->assertStringContainsString('class="grid-duo"',
                file_get_contents(resource_path("views/{$h}.blade.php")),
                "{$h}: baris dua kolomnya tak memakai .grid-duo.");
        }
    }
}
