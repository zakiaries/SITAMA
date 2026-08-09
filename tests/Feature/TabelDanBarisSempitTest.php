<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Tabel adalah satu-satunya bagian yang tak bisa dilipat: enam kolom tetap
 * enam kolom. Yang bisa diatur cuma nasibnya saat tak muat — digeser mendatar,
 * atau dipotong diam-diam.
 *
 * Dulu tabel lowongan Kaprodi memakai `overflow:hidden`, dan yang terpotong
 * justru kolom Aksi di ujung kanan: tombol Ubah & Hapus tak terjangkau sama
 * sekali dari ponsel, tanpa petunjuk apa pun bahwa masih ada isi di kanan.
 *
 * Template CETAK sengaja dikecualikan. Lembar nilai dan berita acara disalin
 * persis dari form resmi Polines lalu dirender jadi PDF; lebar kertasnya tetap,
 * tak ada layar sempit di sana, dan menambahkan pembungkus geser pada mereka
 * justru berisiko menggeser tata letak yang harus sama dengan formnya.
 */
class TabelDanBarisSempitTest extends FeatureTestCase
{
    /** Semua blade, sebagai path yang sudah dinormalkan. */
    private function semuaBlade(): array
    {
        $daftar  = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($iterator as $file) {
            if (str_ends_with($file->getFilename(), '.blade.php')) {
                $daftar[] = str_replace('\\', '/', $file->getPathname());
            }
        }

        sort($daftar);

        return $daftar;
    }

    /**
     * Blade yang berakhir sebagai PDF, bukan sebagai halaman.
     *
     * Dikenali dari isinya (`<html`/`@page`), lalu ditelusuri satu tingkat ke
     * partial yang mereka @include — `_dokumen.blade.php` tak punya penanda
     * sendiri karena ia cuma potongan kop surat, dan mendaftarkannya manual
     * berarti daftar itu akan basi begitu ada template cetak baru.
     */
    private function templateCetak(): array
    {
        $cetak = [];
        $blade = $this->semuaBlade();

        foreach ($blade as $path) {
            $isi = file_get_contents($path);

            if (str_contains($isi, '<html') || str_contains($isi, '@page')) {
                $cetak[$path] = true;
            }
        }

        foreach (array_keys($cetak) as $path) {
            preg_match_all("/@include\(\s*'([^']+)'/", file_get_contents($path), $cocok);

            foreach ($cocok[1] as $nama) {
                $anak = resource_path('views/' . str_replace('.', '/', $nama) . '.blade.php');
                $anak = str_replace('\\', '/', $anak);

                if (is_file($anak)) {
                    $cetak[$anak] = true;
                }
            }
        }

        return $cetak;
    }

    /**
     * Tiap tabel di halaman web harus duduk di dalam wadah yang bisa digeser.
     *
     * Penyapu, bukan daftar tetap: tabel berikutnya akan lahir setelah tes ini
     * ditulis, dan pembungkusnya justru bagian yang paling gampang lupa disalin.
     */
    public function test_tiap_tabel_web_bisa_digeser_mendatar(): void
    {
        $cetak     = $this->templateCetak();
        $diperiksa = 0;

        foreach ($this->semuaBlade() as $path) {
            if (isset($cetak[$path])) {
                continue;
            }

            // Komentar blade dibuang lebih dulu supaya kalimat penjelas yang
            // kebetulan menyebut "overflow-x:auto" tak terhitung sebagai bukti.
            $isi = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($path));

            $pos = 0;

            while (($tabel = strpos($isi, '<table', $pos)) !== false) {
                $sebelum = substr($isi, 0, $tabel);
                $div     = strrpos($sebelum, '<div');

                $pembungkus = $div === false ? '' : substr($sebelum, $div);
                $pembungkus = preg_replace('/\s+/', '', $pembungkus);

                $this->assertStringContainsString('overflow-x:auto', $pembungkus, sprintf(
                    '%s: ada <table> yang wadah terdekatnya tak bisa digeser mendatar. '
                    . 'Bungkus dengan overflow-x:auto, kalau tidak kolom paling kanan '
                    . '(biasanya kolom Aksi) tak terjangkau dari ponsel.',
                    basename($path)
                ));

                $diperiksa++;
                $pos = $tabel + 6;
            }
        }

        $this->assertGreaterThan(0, $diperiksa,
            'Tak ada tabel web yang terperiksa — penyapunya kemungkinan salah sasaran.');
    }

    /** Template cetak memang harus TERLEWAT, bukan kebetulan lolos. */
    public function test_template_cetak_dikecualikan(): void
    {
        $cetak = array_map('basename', array_keys($this->templateCetak()));

        foreach (['pdf-dosen.blade.php', 'pdf-industri.blade.php',
                  'berita-acara-pdf.blade.php', '_dokumen.blade.php'] as $berkas) {
            $this->assertContains($berkas, $cetak,
                "{$berkas} harus dikenali sebagai template cetak supaya tak dipaksa "
                . 'memakai pembungkus geser — tata letaknya harus sama dengan form resmi.');
        }
    }

    public function test_baris_label_menumpuk_di_layar_paling_sempit(): void
    {
        $css = preg_replace('/\s+/', '', file_get_contents(public_path('css/simama.css')));

        $this->assertStringContainsString('.info-row{flex-direction:column;', $css,
            'Baris label-nilai tak menumpuk di layar sempit; labelnya memakan 150px.');
        $this->assertStringContainsString('.info-key{width:auto;}', $css,
            'Label masih dipatok lebarnya walau barisnya sudah menumpuk.');
    }

    public function test_kata_panjang_tak_menggeser_halaman(): void
    {
        $css = preg_replace('/\s+/', '', file_get_contents(public_path('css/simama.css')));

        $this->assertStringContainsString('.content{overflow-wrap:break-word;}', $css,
            'Alamat surel atau nama berkas yang panjang akan memaksa SELURUH halaman '
            . 'bisa digeser mendatar, bukan cuma kata itu sendiri.');
    }
}
