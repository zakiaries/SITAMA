<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('detailed_assessment_component_id')->constrained()->cascadeOnDelete();
            // Penilai: dosen pembimbing ('lecturer') atau pembimbing industri
            // ('lecturer_industry'). Wajib ada agar skor dua penilai tidak saling menimpa.
            $table->enum('scorer_type', ['lecturer', 'lecturer_industry'])->default('lecturer');
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            // Satu skor per (internship, komponen rinci, penilai).
            $table->unique(['internship_id', 'detailed_assessment_component_id', 'scorer_type'], 'scores_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scores');
    }
};
