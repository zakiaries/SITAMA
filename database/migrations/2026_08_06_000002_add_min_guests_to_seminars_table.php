<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jumlah audiens minimal per sesi seminar.
 *
 * Sebelumnya tetap 15 untuk semua sesi (Seminar::MIN_GUESTS), padahal tiap
 * dosen punya pertimbangan berbeda: sesi dengan satu penyaji tak menuntut
 * audiens sebanyak sesi dengan enam penyaji.
 *
 * Default 15 supaya sesi yang sudah ada tetap memakai angka yang berlaku saat
 * dibuat, dan supaya dosen yang tak mengisinya tetap dapat aturan lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_guests')->default(15)->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn('min_guests');
        });
    }
};
