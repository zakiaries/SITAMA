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
}
