<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('message');                       // pertanyaan mahasiswa
            $table->text('answer');                        // jawaban yang diberikan chatbot
            $table->decimal('score', 6, 4)->default(0);    // cosine similarity tertinggi
            $table->string('matched_question', 500)->nullable(); // entri KB yang cocok (null bila fallback)
            $table->string('category', 100)->nullable();
            $table->boolean('is_answered')->default(false); // true bila skor >= ambang
            $table->timestamps();

            $table->index('is_answered');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_logs');
    }
};
