<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Base untuk feature test: migrasi fresh + seed fixture (TestSeeder) di DB
 * `sitama_testing`. Tiap test dibungkus transaksi & di-rollback.
 */
abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestSeeder::class);
    }

    /** Ambil user fixture berdasarkan username. */
    protected function userByUsername(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    /**
     * Kembalikan magang mahasiswa ke keadaan BERJALAN.
     *
     * Fixture mahasiswa 3.34.23.2.01 magangnya is_finished. Sejak isian
     * mahasiswa dikunci setelah selesai magang, tes yang menguji pengisian
     * (logbook, bimbingan, sertifikat) harus memakai mahasiswa yang magangnya
     * masih berjalan — di alur nyata mustahil mengisi logbook untuk magang yang
     * sudah ditutup Kaprodi.
     */
    protected function magangBerjalan(User $mahasiswa): User
    {
        $mahasiswa->student?->internships()->latest('id')->first()
            ?->update(['is_finished' => false, 'finish_requested' => false]);

        return $mahasiswa;
    }
}
