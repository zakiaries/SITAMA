<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Data awal minimum agar aplikasi bisa dipakai di server bersih:
     *  - 1 akun Kaprodi (superadmin) untuk bootstrap; ia lalu membuat akun dosen,
     *    approve mahasiswa, dsb.
     *  - Rubrik penilaian (data referensi wajib).
     *  - FAQ chatbot.
     *
     * Kredensial kaprodi diambil dari .env (SEED_KAPRODI_*), dengan default yang
     * WAJIB diganti sebelum go-live. Idempoten.
     */
    public function run(): void
    {
        $username = env('SEED_KAPRODI_USERNAME', 'kaprodi');
        $email    = env('SEED_KAPRODI_EMAIL', 'kaprodi@example.ac.id');
        $password = env('SEED_KAPRODI_PASSWORD', 'ubah-password-ini');

        $kaprodi = User::firstOrCreate(
            ['username' => $username],
            [
                'name'     => env('SEED_KAPRODI_NAME', 'Kaprodi'),
                'email'    => $email,
                'password' => Hash::make($password),
                'role'     => 'kaprodi',
            ]
        );

        $this->call([
            RubrikPenilaianSeeder::class,
            ChatbotKnowledgeSeeder::class,
        ]);

        if ($kaprodi->wasRecentlyCreated) {
            $this->command->newLine();
            $this->command->info("Akun Kaprodi awal dibuat: username='{$username}'");
            $this->command->warn("Password default: '{$password}' — GANTI segera lewat menu profil / .env (SEED_KAPRODI_PASSWORD).");
        } else {
            $this->command->info("Akun Kaprodi '{$username}' sudah ada — dilewati.");
        }
    }
}
