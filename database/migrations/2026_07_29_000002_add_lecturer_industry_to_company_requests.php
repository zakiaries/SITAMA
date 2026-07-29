<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mahasiswa boleh memilih pembimbing industri yang SUDAH terdaftar (mis. dari
 * mahasiswa sebelumnya di perusahaan yang sama) alih-alih selalu mendaftar baru.
 * Bila diisi, saat Kaprodi approve PIC langsung dipakai tanpa buat akun/aktivasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_requests', function (Blueprint $table) {
            $table->foreignId('lecturer_industry_id')->nullable()->after('company_id')
                ->constrained('lecturers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('company_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lecturer_industry_id');
        });
    }
};
