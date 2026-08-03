<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Buang peran 'industri' yang tak pernah dipakai dari enum users.role.
 *
 * Peran ini sisa rancangan lama ketika perusahaan punya akun sendiri. Sekarang
 * tak ada satu pun kode yang membuatnya, tak ada middleware role yang
 * menyebutnya, dan tak ada satu pun rute untuknya — akun dengan peran itu akan
 * berhasil masuk lalu terdampar tanpa portal mana pun. Pembimbing industri
 * memakai peran 'lecturer_industry', yang berbeda.
 *
 * Membiarkannya di enum membuat skema berbohong soal peran apa saja yang
 * sebenarnya ada, dan mengundang orang mengisinya lewat SQL manual.
 *
 * Sengaja BERHENTI bila ternyata ada barisnya, alih-alih menebak peran
 * penggantinya: mengubah peran seseorang diam-diam bisa memberi atau mencabut
 * akses tanpa ada yang tahu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $terpakai = DB::table('users')->where('role', 'industri')->count();

        if ($terpakai > 0) {
            throw new RuntimeException(
                "Ada {$terpakai} akun berperan 'industri'. Migrasi dihentikan agar peran mereka "
                . 'tidak diubah diam-diam. Periksa dengan: '
                . "SELECT id, name, username FROM users WHERE role = 'industri'; "
                . 'lalu tentukan sendiri peran yang benar sebelum menjalankan ulang.'
            );
        }

        DB::statement(
            "ALTER TABLE `users` MODIFY `role` ENUM('student','lecturer','lecturer_industry','kaprodi') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `users` MODIFY `role` ENUM('student','lecturer','lecturer_industry','kaprodi','industri') NOT NULL"
        );
    }
};
