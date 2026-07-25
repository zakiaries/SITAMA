<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bentuk awal seminar (per mahasiswa). Direstrukturisasi jadi model sesi-grup
        // oleh migrasi 2026_07_09 & 2026_07_22 (tambah lecturer_id, status draft, dll).
        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('program');
            $table->date('date')->nullable();
            $table->string('time')->nullable();
            $table->string('location')->nullable();
            $table->string('organizer')->nullable();
            $table->text('description')->nullable();
            $table->string('qr_code')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('seminar_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['registered', 'attended', 'completed'])->default('registered');
            $table->timestamps();

            $table->unique(['seminar_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_registrations');
        Schema::dropIfExists('seminars');
    }
};
