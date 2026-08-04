<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

/**
 * Halaman Data Dosen Kaprodi menampilkan seluruh dosen dalam satu daftar
 * panjang tanpa cara memisahkan D3 Teknik Informatika dari D4 Teknologi
 * Rekayasa Komputer — Kaprodi harus menggulir 26 kartu dan mengingat sendiri
 * siapa mengajar di mana.
 *
 * Penyebabnya prodi dosen memang tak pernah disimpan: opsi --prodi di
 * simama:impor-dosen hanya memilih daftar mana yang diulang dan label apa yang
 * dicetak ke terminal.
 */
class FilterProdiDosenTest extends FeatureTestCase
{
    private function buatDosen(string $nama, string $nip, ?string $prodi): Lecturer
    {
        $user = User::create([
            'name' => $nama, 'username' => $nip, 'email' => $nip . '@simama.local',
            'password' => Hash::make('rahasia123'), 'role' => 'lecturer', 'is_activated' => true,
        ]);

        return Lecturer::create(['user_id' => $user->id, 'study_program' => $prodi]);
    }

    private function siapkan(): void
    {
        $this->buatDosen('BUDI IK SATU',   '111111111111111111', 'Teknik Informatika');
        $this->buatDosen('CITRA IK DUA',   '222222222222222222', 'Teknik Informatika');
        $this->buatDosen('DEWI TRK SATU',  '333333333333333333', 'Teknologi Rekayasa Komputer');
        $this->buatDosen('EKO TANPA PRODI', '444444444444444444', null);
    }

    private function buka(array $query = [])
    {
        return $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.dosen.index', $query));
    }

    public function test_tanpa_penyaring_semua_dosen_tampil(): void
    {
        $this->siapkan();

        $this->buka()->assertOk()
            ->assertSee('BUDI IK SATU')->assertSee('DEWI TRK SATU')->assertSee('EKO TANPA PRODI');
    }

    public function test_menyaring_prodi_d3(): void
    {
        $this->siapkan();

        $this->buka(['prodi' => 'Teknik Informatika'])->assertOk()
            ->assertSee('BUDI IK SATU')
            ->assertSee('CITRA IK DUA')
            ->assertDontSee('DEWI TRK SATU')
            ->assertDontSee('EKO TANPA PRODI');
    }

    public function test_menyaring_prodi_d4(): void
    {
        $this->siapkan();

        $this->buka(['prodi' => 'Teknologi Rekayasa Komputer'])->assertOk()
            ->assertSee('DEWI TRK SATU')
            ->assertDontSee('BUDI IK SATU');
    }

    /** Yang prodinya belum diisi harus tetap bisa ditemukan, bukan tersembunyi. */
    public function test_menyaring_yang_belum_diisi(): void
    {
        $this->siapkan();

        $this->buka(['prodi' => 'kosong'])->assertOk()
            ->assertSee('EKO TANPA PRODI')
            ->assertDontSee('BUDI IK SATU');
    }

    public function test_penyaring_dan_pencarian_bekerja_bersama(): void
    {
        $this->siapkan();

        $this->buka(['prodi' => 'Teknik Informatika', 'search' => 'CITRA'])->assertOk()
            ->assertSee('CITRA IK DUA')
            ->assertDontSee('BUDI IK SATU');
    }

    /** Pencarian NIP sudah lama bekerja, tapi placeholder-nya tak menyebutkannya. */
    public function test_pencarian_nip_bekerja_dan_disebut_di_placeholder(): void
    {
        $this->siapkan();

        $this->buka(['search' => '333333333333333333'])->assertOk()
            ->assertSee('DEWI TRK SATU')
            ->assertDontSee('BUDI IK SATU');

        $this->buka()->assertOk()->assertSee('Cari nama atau NIP');
    }

    public function test_daftar_terurut_menurut_nama(): void
    {
        $this->siapkan();

        $html = $this->buka()->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'DEWI TRK SATU'),
            strpos($html, 'BUDI IK SATU'),
            'Daftar tak terurut menurut nama.'
        );
    }

    /** Tab pembimbing industri tak boleh ikut disaring prodi. */
    public function test_tab_industri_tak_menampilkan_penyaring_prodi(): void
    {
        $this->siapkan();

        $html = $this->buka(['tab' => 'industri'])->assertOk()->getContent();

        $this->assertStringNotContainsString('Belum diisi', $html);
    }

    public function test_label_prodi_tampil_di_kartu(): void
    {
        $this->siapkan();

        $this->buka()->assertOk()
            ->assertSee('Teknik Informatika (D3)')
            ->assertSee('Teknologi Rekayasa Komputer (D4)');
    }

    // ── Prodi juga tampil di halaman profil ─────────────────────────────────

    public function test_prodi_tampil_di_profil_dosen_sendiri(): void
    {
        $dosen = $this->buatDosen('FIRA TRK', '555555555555555555', 'Teknologi Rekayasa Komputer');

        $this->actingAs($dosen->user)->get('/dosen/profile')->assertOk()
            ->assertSee('Program Studi')
            ->assertSee('Teknologi Rekayasa Komputer (D4)')
            ->assertSee('NIP / Username');
    }

    public function test_profil_jujur_saat_prodi_belum_diisi(): void
    {
        $dosen = $this->buatDosen('GILANG TANPA PRODI', '666666666666666666', null);

        $this->actingAs($dosen->user)->get('/dosen/profile')->assertOk()
            ->assertSee('Belum diisi');
    }

    public function test_prodi_tampil_di_detail_dosen_versi_kaprodi(): void
    {
        $dosen = $this->buatDosen('HANI IK', '777777777777777777', 'Teknik Informatika');

        $this->actingAs($this->userByUsername('kaprodi'))
            ->get(route('kaprodi.dosen.detail', $dosen))->assertOk()
            ->assertSee('Teknik Informatika (D3)');
    }

    // ── Sumber datanya: perintah impor ──────────────────────────────────────

    /** Menjalankan ulang impor-dosen mengisi prodi akun yang sudah ada. */
    public function test_impor_dosen_mengisi_prodi_akun_lama(): void
    {
        $dosen = $this->buatDosen('SUKAMTO, S.Kom., M.T.', '197101172003121001', null);

        $this->artisan('simama:impor-dosen', ['--prodi' => 'ik'])->assertSuccessful();

        $this->assertSame('Teknik Informatika', $dosen->fresh()->study_program,
            'Prodi akun lama tak terisi, padahal ini satu-satunya jalan mengisinya tanpa SQL manual.');
    }

    public function test_impor_dosen_menyimpan_prodi_akun_baru(): void
    {
        $this->artisan('simama:impor-dosen', ['--prodi' => 'ti', '--password' => 'rahasia123'])
            ->assertSuccessful();

        $dosen = Lecturer::whereHas('user', fn ($u) => $u->where('username', '198407192019031008'))->first();

        $this->assertNotNull($dosen);
        $this->assertSame('Teknologi Rekayasa Komputer', $dosen->study_program);
    }
}
