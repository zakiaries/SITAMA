<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Judul halaman tak boleh tercetak dua kali.
 *
 * Tiap layout mencetak `$title` di topbar. Bila halamannya menulis ulang teks
 * yang sama sebagai `.page-title` di badan halaman, judulnya muncul dua kali
 * persis, satu tepat di bawah yang lain — itu yang terlihat di halaman FAQ
 * Chatbot dan Riwayat & Statistik Chatbot milik Kaprodi.
 *
 * Penyapunya membandingkan `$title` dengan tiap `.page-title` di berkas yang
 * sama dan hanya menyalahkan yang PERSIS sama. Judul badan yang lebih panjang
 * dan menerangkan — topbar "Bimbingan" dengan badan "Daftar Bimbingan" —
 * sengaja dibiarkan: itu pilihan tampilan yang dipakai berulang di portal
 * mahasiswa, bukan kekeliruan, dan penjaga yang melarangnya akan menyuruh
 * orang merusak yang sudah betul.
 */
class JudulTakTercetakDuaKaliTest extends FeatureTestCase
{
    /** @return list<array{berkas:string,judul:string}> */
    private function judulGanda(): array
    {
        $temuan  = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($iterator as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $isi = file_get_contents($file->getPathname());

            if (! preg_match('/\$title\s*=\s*(.+?);\s*@endphp/s', $isi, $m)) {
                continue;
            }

            preg_match_all('/\'([^\']+)\'|"([^"]+)"/', $m[1], $lit);
            $literal = array_filter(array_merge($lit[1], $lit[2]));

            preg_match_all('/class="page-title"[^>]*>(.*?)<\/div>/s', $isi, $pt);

            foreach ($pt[1] as $judulBadan) {
                $teks = trim(preg_replace('/\s+/', ' ', strip_tags($judulBadan)));
                $teks = str_replace('&amp;', '&', $teks);

                /* Dua bentuk yang sama-sama harus tertangkap:
                   (a) judul badan berupa TEKS POLOS yang sama persis dengan
                       $title — kasus halaman Riwayat & Statistik;
                   (b) judul badan berupa EKSPRESI Blade yang memuat literal
                       yang sama dengan $title — kasus halaman Tambah/Edit FAQ,
                       yang lolos dari versi pertama penjaga ini karena
                       teksnya bukan kalimat melainkan `{{ $item ? '...' }}`. */
                preg_match_all('/\'([^\']+)\'|"([^"]+)"/', $judulBadan, $litBadan);
                $literalBadan = array_filter(array_merge($litBadan[1], $litBadan[2]));

                foreach ($literal as $l) {
                    $l = trim($l);

                    if ($l === '') {
                        continue;
                    }

                    /* Mengulang SEBAGIAN pun dihitung ganda: topbar "Bimbingan"
                       dengan badan "Daftar Bimbingan" tetap membuat kata yang
                       sama tercetak dua kali, satu tepat di bawah yang lain.
                       Keputusan user 9 Agt 2026, setelah kelima halaman
                       semacam itu ikut diminta dibetulkan. */
                    $samaPersis  = $l === $teks || str_contains($teks, $l);
                    $samaLiteral = false;

                    foreach (array_map('trim', $literalBadan) as $lb) {
                        if ($lb === $l || str_contains($lb, $l)) {
                            $samaLiteral = true;
                            break;
                        }
                    }

                    if ($samaPersis || $samaLiteral) {
                        $temuan[] = [
                            'berkas' => str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                            'judul'  => $l,
                        ];
                    }
                }
            }
        }

        return $temuan;
    }

    public function test_tak_ada_halaman_yang_mencetak_judulnya_dua_kali(): void
    {
        $temuan = $this->judulGanda();

        $pesan = implode("\n", array_map(
            fn ($t) => "  {$t['berkas']}: \"{$t['judul']}\" dicetak topbar DAN badan halaman",
            $temuan
        ));

        $this->assertSame([], $temuan,
            "Judul halaman tercetak dua kali:\n{$pesan}\n"
            . 'Hapus .page-title-nya; topbar sudah mencetak $title.');
    }

    /** Halaman yang dilaporkan memang sudah bersih, dan judulnya tetap ada di topbar. */
    public function test_halaman_chatbot_kaprodi_bersih(): void
    {
        $kaprodi = $this->userByUsername('kaprodi');

        foreach ([
            'kaprodi.chatbot.logs'   => 'Riwayat',
            'kaprodi.chatbot.create' => 'FAQ Chatbot',
        ] as $rute => $penggalan) {
            $html = $this->actingAs($kaprodi)->get(route($rute))->assertOk()->getContent();

            $this->assertStringContainsString($penggalan, $html,
                "Halaman {$rute} kehilangan judulnya sama sekali.");
        }
    }
}
