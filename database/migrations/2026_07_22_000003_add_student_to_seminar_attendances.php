<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Absensi audiens kini berbasis akun (wajib login), bukan form anonim +
 * tanda tangan. Tambah student_id (akun audiens), dan buat kolom lama
 * (nim/tanda tangan) nullable agar data anonim lama tetap valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seminar_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('seminar_attendances', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('seminar_id');
                $table->index('student_id');
            }
        });

        // Kolom form anonim lama jadi opsional (absensi baru memakai akun).
        DB::statement('ALTER TABLE seminar_attendances MODIFY nim VARCHAR(50) NULL');
        DB::statement('ALTER TABLE seminar_attendances MODIFY signature_path VARCHAR(255) NULL');
    }

    public function down(): void
    {
        Schema::table('seminar_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('seminar_attendances', 'student_id')) {
                $table->dropColumn('student_id');
            }
        });
    }
};
