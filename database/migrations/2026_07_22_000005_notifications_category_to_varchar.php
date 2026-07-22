<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * notifications.category sebelumnya ENUM('guidance','log_book','general','revisi')
 * sehingga kategori yang dipakai fitur lain (pengajuan_magang, selesai_magang,
 * seminar, dll) memicu error "Data truncated" di strict mode. Diubah ke VARCHAR
 * agar semua kategori valid dan tahan perubahan ke depan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY category VARCHAR(50) NOT NULL DEFAULT 'general'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY category ENUM('guidance','log_book','general','revisi') NOT NULL DEFAULT 'general'");
    }
};
