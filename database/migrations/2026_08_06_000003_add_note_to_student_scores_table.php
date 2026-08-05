<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keterangan penilaian, satu per komponen per penilai.
 *
 * Kolom KETERANGAN ada di kedua form resmi Polines, dan di form pembimbing
 * industri ia benar-benar dipakai — contoh dari lapangan berisi "Sangat baik",
 * "Cukup baik dalam sikap kerja", "Sangat baik dalam menjalani tugas".
 * Sistem tak punya tempat menyimpannya, sehingga kolom itu di lembar cetak
 * terpaksa dibiarkan kosong untuk diisi tangan.
 *
 * Menempel pada `student_scores`, bukan pada magang, karena keterangannya
 * memang per komponen — dan kunci unik tabel ini sudah tepat bentuknya:
 * (internship_id, detailed_assessment_component_id, scorer_type).
 *
 * Berlaku untuk kedua penilai. Form dosen juga punya kolomnya, meski pada
 * contoh yang ada ia dibiarkan kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->string('note', 255)->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
