<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

/**
 * Ubah sandi lewat halaman Profil, keempat peran.
 *
 * Dilaporkan sebagai "konfirmasi sandi berbeda tetap diterima". Ternyata server
 * memang MENOLAK dan sandinya tak pernah berubah — yang hilang adalah pesan
 * penolakannya: halaman profil keempat peran tak menampilkan galat sama sekali,
 * jadi pengguna dikembalikan ke halaman yang sama tanpa penanda apa pun dan
 * menyimpulkan sandinya sudah berganti. Lalu ia tak bisa masuk.
 */
class ProfilSandiTest extends FeatureTestCase
{
    /** @return array<string, array{string, string}> username => [username, rute] */
    private function peran(): array
    {
        return [
            'mahasiswa'         => ['3.34.23.2.01', 'mahasiswa.profile.update'],
            'dosen'             => ['dosen1',       'dosen.profile.update'],
            'pembimbing industri' => ['industri1',  'dosen-industri.profile.update'],
            'kaprodi'           => ['kaprodi',      'kaprodi.profile.update'],
        ];
    }

    public function test_konfirmasi_tak_cocok_ditolak_di_semua_peran(): void
    {
        foreach ($this->peran() as $label => [$username, $rute]) {
            $user = $this->userByUsername($username);

            $this->actingAs($user)->put(route($rute), [
                'name'                  => $user->name,
                'email'                 => $user->email,
                'password'              => 'sandibaru123',
                'password_confirmation' => 'sandilain456',
            ])->assertSessionHasErrors('password');

            $this->assertFalse(
                Hash::check('sandibaru123', $user->fresh()->password),
                "Sandi {$label} tak boleh berubah saat konfirmasinya beda."
            );
        }
    }

    /** Inti bug yang dilaporkan: penolakannya harus TERLIHAT. */
    public function test_pesan_penolakan_tampil_di_halaman_profil(): void
    {
        $rute = [
            'mahasiswa.profile'         => 'mahasiswa.profile.update',
            'dosen.profile'             => 'dosen.profile.update',
            'dosen-industri.profile'    => 'dosen-industri.profile.update',
            'kaprodi.profile'           => 'kaprodi.profile.update',
        ];

        foreach ($this->peran() as [$username, $ruteUpdate]) {
            $user    = $this->userByUsername($username);
            $halaman = array_search($ruteUpdate, $rute, true);

            $this->actingAs($user)->put(route($ruteUpdate), [
                'name'                  => $user->name,
                'email'                 => $user->email,
                'password'              => 'sandibaru123',
                'password_confirmation' => 'sandilain456',
            ]);

            // Menguji PESAN galatnya, bukan sekadar kata "konfirmasi" — label
            // "Konfirmasi Password" ada di halaman itu bahkan saat tak ada
            // galat sama sekali, jadi mencarinya akan lolos tanpa membuktikan
            // apa pun.
            $this->actingAs($user)->get(route($halaman))
                ->assertOk()
                ->assertSee('Konfirmasi password tidak cocok.');
        }
    }

    /**
     * Galat harus muncul DI DALAM dialog yang terbuka kembali, bukan di halaman
     * di belakangnya — kalau tidak, pengguna melihat pesan merah sementara
     * formulir yang ia isi sudah lenyap, dan harus mengetik ulang semuanya.
     */
    public function test_dialog_terbuka_kembali_dan_isian_dipertahankan(): void
    {
        $user = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($user)->put(route('mahasiswa.profile.update'), [
            'name'                  => 'Nama Yang Baru Diketik',
            'email'                 => $user->email,
            'password'              => 'sandibaru123',
            'password_confirmation' => 'sandilain456',
        ]);

        $this->actingAs($user)->get(route('mahasiswa.profile'))
            ->assertOk()
            ->assertSee("getElementById('modal-edit-profile').classList.add('open')", false)
            ->assertSee('Nama Yang Baru Diketik', false);
    }

    public function test_konfirmasi_cocok_mengganti_sandi(): void
    {
        foreach ($this->peran() as [$username, $rute]) {
            $user = $this->userByUsername($username);

            $this->actingAs($user)->put(route($rute), [
                'name'                  => $user->name,
                'email'                 => $user->email,
                'password'              => 'sandibaru123',
                'password_confirmation' => 'sandibaru123',
            ])->assertSessionHasNoErrors();

            $this->assertTrue(Hash::check('sandibaru123', $user->fresh()->password));
        }
    }

    public function test_sandi_terlalu_pendek_ditolak(): void
    {
        $user = $this->userByUsername('3.34.23.2.01');

        $this->actingAs($user)->put(route('mahasiswa.profile.update'), [
            'name'                  => $user->name,
            'email'                 => $user->email,
            'password'              => '12345',
            'password_confirmation' => '12345',
        ])->assertSessionHasErrors('password');

        $this->assertFalse(Hash::check('12345', $user->fresh()->password));
    }
}
