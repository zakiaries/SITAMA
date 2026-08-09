<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Sebuah <select> melebar mengikuti PILIHAN TERPANJANGNYA, bukan mengikuti
 * wadahnya. Di SIMAMA sebagian besar pilihan datang dari basis data — alamat
 * lengkap lowongan, nama perusahaan seperti "Dinas Tenaga Kerja dan
 * Transmigrasi Provinsi Jawa Tengah", nama dosen — sehingga dropdown-nya
 * tumbuh melewati kartunya dan menyeret seluruh halaman jadi bisa digeser
 * mendatar.
 *
 * Ditemukan di HP pada halaman Lowongan Magang (dropdown "Semua wilayah"),
 * tetapi tujuh dropdown lain memuat data serupa dan hanya menunggu datanya
 * cukup panjang. Karena itu penjagaannya di aturan `select` global, bukan di
 * halaman yang kebetulan ketahuan lebih dulu.
 */
class DropdownTakMelebarTest extends FeatureTestCase
{
    /**
     * CSS tanpa komentar dan tanpa spasi.
     *
     * Komentarnya dibuang lebih dulu karena penjelasan di berkas ini justru
     * MENYEBUT kata `@media` saat menerangkan mengapa aturannya sengaja berada
     * di luar blok itu — dan tanpa dibuang, kalimat penjelas itulah yang
     * terbaca sebagai blok @media pertama.
     */
    private function css(): string
    {
        $css = preg_replace('#/\*.*?\*/#s', '', file_get_contents(public_path('css/simama.css')));

        return preg_replace('/\s+/', '', $css);
    }

    /** Penjaga inti: berlaku untuk SEMUA dropdown, sekarang dan yang akan datang. */
    public function test_dropdown_tak_boleh_melewati_wadahnya(): void
    {
        $this->assertStringContainsString('select{max-width:100%;}', $this->css(),
            'Aturan `select { max-width: 100% }` hilang. Tanpanya, dropdown yang '
            . 'pilihannya panjang (alamat, nama perusahaan) melebar keluar kartu dan '
            . 'membuat seluruh halaman bisa digeser mendatar di HP.');
    }

    /**
     * Aturannya sengaja BUKAN di dalam @media: dropdown yang melewati wadahnya
     * adalah cacat pada lebar berapa pun, dan aturan ini tak pernah mengubah
     * yang sudah muat.
     */
    public function test_aturannya_berlaku_di_semua_lebar(): void
    {
        $css = $this->css();

        $this->assertNotFalse(strpos($css, 'select{max-width:100%;}'),
            'Aturan select tak ditemukan.');

        foreach ($this->blokMedia($css) as $blok) {
            $this->assertStringNotContainsString('select{max-width:100%;}', $blok,
                'Aturan select berada di dalam blok @media — seharusnya berlaku di semua '
                . 'lebar layar, sebab dropdown meluber bukan masalah khusus layar sempit.');
        }
    }

    /**
     * Isi tiap blok @media.
     *
     * Kurungnya dihitung manual karena blok @media memuat blok lain di
     * dalamnya. Versi awal tes ini memakai jalan pintas "aturannya harus muncul
     * sebelum @media PERTAMA" — dan itu patah begitu ada blok @media baru
     * ditambahkan di posisi yang lebih awal dalam berkas, padahal aturan select
     * sendiri tak bergeser sedikit pun.
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

    public function test_deretan_penyaring_menumpuk_di_layar_sempit(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('.filter-form{flex-direction:column;align-items:stretch;}', $css,
            'Deretan penyaring tak menumpuk di layar sempit — kendali yang berdesakan '
            . 'setengah-setengah membuat isi dropdown terpotong di tengah kata.');
    }

    /** Halaman yang melaporkan masalahnya memang memakai kelas itu. */
    public function test_halaman_lowongan_memakai_deretan_penyaring(): void
    {
        $html = $this->actingAs($this->userByUsername('3.34.23.2.01'))
            ->get(route('mahasiswa.lowongan'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="filter-form"', $html,
            'Formulir penyaring Lowongan Magang tak memakai .filter-form.');
        $this->assertStringContainsString('name="location"', $html,
            'Dropdown wilayah hilang dari halaman Lowongan Magang.');
    }
}
