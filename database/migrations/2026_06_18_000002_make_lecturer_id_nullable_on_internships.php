<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Dosen pembimbing bisa belum ditentukan saat magang baru disetujui;
        // kaprodi menugaskannya belakangan lewat menu Data Mahasiswa.
        DB::statement('ALTER TABLE internships MODIFY lecturer_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE internships MODIFY lecturer_id BIGINT UNSIGNED NOT NULL');
    }
};
