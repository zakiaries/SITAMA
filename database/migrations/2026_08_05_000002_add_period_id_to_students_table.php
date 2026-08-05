<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keanggotaan mahasiswa pada satu periode magang.
 *
 * `academic_year` SENGAJA dibiarkan apa adanya untuk sementara: dashboard
 * dosen, ekspor Excel, dan beberapa endpoint mobile masih membacanya. Menghapus
 * atau mengubahnya sekarang akan merusak hal-hal itu tanpa perlu — kolom ini
 * berdampingan dulu, dan pemakainya dipindahkan bertahap.
 *
 * Nullable karena mahasiswa bisa lahir sebelum Kaprodi menetapkan periode
 * (mis. instalasi baru), dan karena kolomnya tak boleh menggagalkan pendaftaran
 * kalau periode aktif kebetulan belum ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->after('academic_year')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });
    }
};
