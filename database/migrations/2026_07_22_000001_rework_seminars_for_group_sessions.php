<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah model seminar: dari "1 seminar = 1 mahasiswa" menjadi
 * "1 sesi = 1 dosen pembimbing + banyak mahasiswa penyaji".
 *
 * - lecturer_id : dosen pemilik/penyaksi sesi.
 * - witnessed_at: waktu dosen mengesahkan (status jadi completed).
 * - student_id  : dibuat nullable (legacy; sesi baru tidak memakainya).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            if (! Schema::hasColumn('seminars', 'lecturer_id')) {
                $table->unsignedBigInteger('lecturer_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('seminars', 'witnessed_at')) {
                $table->timestamp('witnessed_at')->nullable()->after('access_token');
            }
        });

        // Jadikan student_id nullable tanpa perlu doctrine/dbal.
        DB::statement('ALTER TABLE seminars MODIFY student_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            foreach (['lecturer_id', 'witnessed_at'] as $col) {
                if (Schema::hasColumn('seminars', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
