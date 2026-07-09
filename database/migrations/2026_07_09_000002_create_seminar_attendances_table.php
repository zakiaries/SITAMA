<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daftar hadir tamu (berita acara) seminar, diisi via scan QR.
        Schema::create('seminar_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained('seminars')->onDelete('cascade');
            $table->string('name');
            $table->string('nim');
            $table->string('kelas')->nullable();
            $table->string('prodi')->nullable();
            $table->string('signature_path');
            $table->timestamps();

            $table->unique(['seminar_id', 'nim']); // satu NIM sekali tanda tangan per seminar
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_attendances');
    }
};
