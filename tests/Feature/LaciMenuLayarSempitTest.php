<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

/**
 * Di layar ponsel sidebar 240px memakan hampir seluruh lebar layar, sehingga
 * isi halaman tak kebagian tempat. Karena itu di bawah 900px sidebar berubah
 * jadi laci yang digeser masuk lewat tombol di topbar.
 *
 * Mekanismenya CSS murni (kotak centang + label), BUKAN JavaScript. Ini bukan
 * selera: simama-anim.js berhenti di baris pertamanya bila pengguna menyalakan
 * "kurangi animasi" di setelan ponselnya. Kalau tombol menu menumpang di sana,
 * justru orang itulah yang kehilangan satu-satunya jalan membuka menu — dan
 * kerusakannya tak akan terlihat saat diuji di perangkat biasa.
 */
class LaciMenuLayarSempitTest extends FeatureTestCase
{
    /** Peran => [username fixture, nama rute dashboard]. */
    private const PORTAL = [
        'mahasiswa'      => ['3.34.23.2.01', 'mahasiswa.dashboard'],
        'dosen'          => ['dosen1',       'dosen.dashboard'],
        'dosen-industri' => ['industri1',    'dosen-industri.dashboard'],
        'kaprodi'        => ['kaprodi',      'kaprodi.dashboard'],
    ];

    private function css(): string
    {
        return preg_replace('/\s+/', '', file_get_contents(public_path('css/simama.css')));
    }

    /** Keempat portal harus benar-benar merender lacinya, bukan cuma punya CSS-nya. */
    public function test_tiap_portal_merender_kendali_laci(): void
    {
        foreach (self::PORTAL as $peran => [$username, $rute]) {
            $html = $this->actingAs($this->userByUsername($username))
                ->get(route($rute))
                ->assertOk()
                ->getContent();

            $this->assertStringContainsString('id="nav-toggle"', $html,
                "Portal {$peran} kehilangan kotak centang laci — menu tak bisa dibuka di HP.");
            $this->assertStringContainsString('class="nav-open-btn"', $html,
                "Portal {$peran} kehilangan tombol menu di topbar.");
            $this->assertStringContainsString('class="nav-scrim"', $html,
                "Portal {$peran} kehilangan kelambu — laci jadi tak bisa ditutup.");
        }
    }

    /**
     * Urutan di dalam .app menentukan hidup-matinya fitur ini.
     *
     * Pemilih `~` hanya menjangkau saudara yang datang SESUDAHNYA, jadi kotak
     * centang wajib berdiri sebelum sidebar dan kelambu. Kalau suatu saat ada
     * yang merapikan layout lalu memindahkannya ke bawah, lacinya berhenti
     * bekerja tanpa satu pun galat — hanya tombol yang diam saat ditekan.
     */
    public function test_kotak_centang_berdiri_sebelum_sidebar_dan_kelambu(): void
    {
        foreach (array_keys(self::PORTAL) as $peran) {
            $blade = file_get_contents(resource_path("views/layouts/{$peran}.blade.php"));

            $centang = strpos($blade, 'id="nav-toggle"');
            $sidebar = strpos($blade, ".sidebar')");
            $kelambu = strpos($blade, 'class="nav-scrim"');

            $this->assertNotFalse($centang, "layouts/{$peran}: kotak centang laci tak ditemukan.");
            $this->assertNotFalse($sidebar, "layouts/{$peran}: include sidebar tak ditemukan.");
            $this->assertNotFalse($kelambu, "layouts/{$peran}: kelambu tak ditemukan.");

            $this->assertLessThan($sidebar, $centang,
                "layouts/{$peran}: kotak centang harus SEBELUM sidebar, kalau tidak `~` tak menjangkaunya.");
            $this->assertLessThan($kelambu, $centang,
                "layouts/{$peran}: kotak centang harus SEBELUM kelambu, kalau tidak `~` tak menjangkaunya.");
        }
    }

    /** Tombol menunjuk kotak centang lewat for=, jadi namanya harus cocok persis. */
    public function test_tombol_menunjuk_kotak_centang_yang_benar(): void
    {
        foreach (array_keys(self::PORTAL) as $peran) {
            $topbar = file_get_contents(resource_path("views/components/{$peran}/topbar.blade.php"));

            $this->assertStringContainsString('for="nav-toggle"', $topbar,
                "components/{$peran}/topbar: tombol menu tak menunjuk kotak centang mana pun.");
        }
    }

    public function test_laci_hanya_muncul_di_layar_sempit(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('@media(max-width:900px)', $css,
            'Breakpoint laci hilang.');
        $this->assertStringContainsString('.nav-open-btn{display:none;}', $css,
            'Tombol menu harus tersembunyi di desktop, kalau tidak topbar lama ikut bergeser.');
        $this->assertStringContainsString('.nav-toggle:checked~.sidebar', $css,
            'Aturan yang menggeser sidebar masuk hilang — tombol jadi tak berefek.');
    }

    /**
     * Penjaga inti: laci TIDAK boleh bergantung pada simama-anim.js, yang
     * berhenti lebih awal saat pengguna memilih "kurangi animasi".
     */
    public function test_laci_tak_bergantung_pada_javascript_animasi(): void
    {
        $js = file_get_contents(public_path('js/simama-anim.js'));

        $this->assertStringNotContainsString('nav-toggle', $js,
            'Laci tak boleh dikendalikan simama-anim.js — berkas itu berhenti lebih awal '
            . 'bagi pengguna yang memilih "kurangi animasi", sehingga menu jadi tak bisa dibuka.');
        $this->assertStringNotContainsString('nav-open-btn', $js,
            'Tombol menu tak boleh dikendalikan simama-anim.js (lihat alasan di atas).');
    }
}
