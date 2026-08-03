<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

/**
 * Peran 'industri' sisa rancangan lama ketika perusahaan punya akun sendiri.
 * Tak ada kode yang membuatnya, tak ada middleware role yang menyebutnya, dan
 * tak ada satu pun rute untuknya — akun berperan itu akan berhasil masuk lalu
 * terdampar tanpa portal mana pun.
 *
 * Membiarkannya di enum membuat skema berbohong soal peran apa saja yang
 * sebenarnya ada, dan mengundang orang mengisinya lewat SQL manual.
 */
class PeranIndustriSudahTiadaTest extends FeatureTestCase
{
    public function test_enum_peran_tak_lagi_memuat_industri(): void
    {
        $kolom = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'role'");

        $this->assertStringNotContainsString("'industri'", $kolom->Type,
            "Enum users.role masih memuat peran mati 'industri'.");
    }

    public function test_keempat_peran_yang_dipakai_tetap_ada(): void
    {
        $kolom = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'role'");

        foreach (['student', 'lecturer', 'lecturer_industry', 'kaprodi'] as $peran) {
            $this->assertStringContainsString("'{$peran}'", $kolom->Type,
                "Peran '{$peran}' hilang dari enum — ini yang benar-benar dipakai.");
        }
    }

    /** Basis data sendiri yang menolak, bukan sekadar kesepakatan di kode. */
    public function test_basis_data_menolak_akun_berperan_industri(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('users')->insert([
            'name'       => 'Akun Peran Mati',
            'username'   => 'peran.mati',
            'email'      => 'peran.mati@contoh.com',
            'password'   => Hash::make('rahasia123'),
            'role'       => 'industri',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Pembimbing industri memakai 'lecturer_industry' — peran yang berbeda. */
    public function test_pembimbing_industri_tak_terpengaruh(): void
    {
        $industri = $this->userByUsername('industri1');

        $this->assertSame('lecturer_industry', $industri->role);

        $this->actingAs($industri)->get('/dosen-industri/dashboard')->assertOk();
    }
}
