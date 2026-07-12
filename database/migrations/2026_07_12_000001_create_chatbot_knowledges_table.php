<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_knowledges', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan', 500);   // pertanyaan representatif (ditampilkan)
            $table->text('kata_kunci');           // variasi kata/frasa untuk memperkaya TF-IDF
            $table->text('jawaban');
            $table->string('kategori', 100)->default('Umum');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_knowledges');
    }
};
