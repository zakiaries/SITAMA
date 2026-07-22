<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penyaji dalam satu sesi seminar (banyak mahasiswa per sesi) sekaligus
 * menyimpan ketersediaan tanggal yang mereka isi ("petisi" penjadwalan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_presenters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_id');
            $table->unsignedBigInteger('student_id');
            $table->text('available_dates')->nullable(); // ketersediaan tanggal dari mahasiswa
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['seminar_id', 'student_id']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_presenters');
    }
};
