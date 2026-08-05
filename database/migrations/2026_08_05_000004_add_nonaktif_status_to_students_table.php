<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Status "nonaktif" untuk mahasiswa yang sedang tidak menempuh studi.
 *
 * Sebelumnya hanya ada pending / active / rejected — ketiganya tentang
 * PENDAFTARAN, bukan tentang apakah orangnya sedang kuliah. Akibatnya mahasiswa
 * yang cuti, gap year, atau tersendat tetap terhitung sebagai mahasiswa aktif:
 * ia masuk angka dashboard Kaprodi, masuk daftar bimbingan dosen, dan tetap
 * bisa mengisi logbook seolah magangnya berjalan.
 *
 * Dibedakan dari `rejected` karena maknanya berbeda: rejected berarti
 * pendaftarannya tak pernah diterima, sedangkan nonaktif berarti mahasiswanya
 * sah tapi sedang berhenti sementara — dan bisa diaktifkan lagi tanpa
 * mendaftar ulang.
 *
 * `status_note` menyimpan ALASAN, yang justru inti fiturnya: tanpa alasan,
 * setahun kemudian tak ada yang ingat kenapa seseorang dinonaktifkan, dan
 * Kaprodi berikutnya tak berani mengaktifkannya kembali.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE students MODIFY COLUMN status
             ENUM('pending','active','rejected','nonaktif') NOT NULL DEFAULT 'pending'"
        );

        Schema::table('students', function (Blueprint $table) {
            $table->text('status_note')->nullable()->after('status');
            $table->timestamp('status_changed_at')->nullable()->after('status_note');
        });
    }

    public function down(): void
    {
        // Kembalikan yang nonaktif jadi aktif dulu; kalau tidak, ALTER akan
        // memangkasnya jadi string kosong tanpa peringatan.
        DB::table('students')->where('status', 'nonaktif')->update(['status' => 'active']);

        DB::statement(
            "ALTER TABLE students MODIFY COLUMN status
             ENUM('pending','active','rejected') NOT NULL DEFAULT 'pending'"
        );

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['status_note', 'status_changed_at']);
        });
    }
};
