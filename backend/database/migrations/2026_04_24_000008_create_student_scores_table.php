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
            $table->foreignId('detailed_assessment_component_id')->constrained('detailed_assessment_components')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['internship_id', 'detailed_assessment_component_id'], 'scores_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scores');
    }
};
