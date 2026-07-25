<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            // lecturer_id & company_id nullable (menggabungkan make_internships_fields_nullable).
            $table->foreignId('lecturer_id')->nullable()->constrained('lecturers')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_industry_id')->nullable()->constrained('lecturers')->nullOnDelete();
            $table->string('position')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_finished')->default(false);
            $table->text('performance_notes')->nullable();
            $table->string('performance_notes_by')->nullable();
            $table->date('performance_notes_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
