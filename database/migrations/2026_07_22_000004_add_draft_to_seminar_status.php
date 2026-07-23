<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambah nilai 'draft' pada enum seminars.status (sesi baru dibuat dosen dalam
 * status draft sebelum jadwal ditetapkan). Nilai lama dipertahankan agar data
 * lama tetap valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE seminars MODIFY status ENUM('draft','pending','scheduled','completed','cancelled','rejected') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE seminars MODIFY status ENUM('pending','scheduled','completed','cancelled','rejected') NOT NULL DEFAULT 'pending'");
    }
};
