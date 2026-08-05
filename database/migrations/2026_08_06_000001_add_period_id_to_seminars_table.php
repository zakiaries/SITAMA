<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Periode magang yang diseminarkan.
 *
 * Berita acara mencantumkan semester & tahun akademik, dan selama ini keduanya
 * DIHITUNG DARI TANGGAL SEMINAR — dengan logika kalender yang disalin ulang di
 * dalam Blade. Itu keliru: seminar berlangsung SESUDAH magang, kadang di
 * semester berikutnya. Mahasiswa yang magang pada Genap 2025/2026 lalu
 * seminarnya jatuh Agustus 2026 akan menerima berita acara bertuliskan "Gasal
 * 2026/2027" — periode yang bukan miliknya, di dokumen yang ditandatangani.
 *
 * Periodenya kini disimpan, bukan ditebak dari tanggal.
 *
 * Nullable karena sesi bisa lahir dari mahasiswa yang belum berperiode (data
 * lama), dan karena tak boleh menggagalkan pembuatan sesi bila periodenya
 * kebetulan tak diketahui.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->after('program')
                ->constrained()->nullOnDelete();
        });

        // Sesi yang sudah ada: ambil periode dari penyaji pertamanya.
        DB::table('seminars')->whereNull('period_id')->orderBy('id')->each(function ($seminar) {
            $periodId = DB::table('seminar_presenters')
                ->join('students', 'students.id', '=', 'seminar_presenters.student_id')
                ->where('seminar_presenters.seminar_id', $seminar->id)
                ->orderBy('seminar_presenters.id')
                ->value('students.period_id');

            if ($periodId) {
                DB::table('seminars')->where('id', $seminar->id)->update(['period_id' => $periodId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });
    }
};
