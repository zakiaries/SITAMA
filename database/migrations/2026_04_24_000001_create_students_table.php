<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Kolom profil dibuat nullable (menggabungkan migrasi make_students_fields_nullable).
            $table->string('the_class')->nullable();
            $table->string('study_program')->nullable();
            $table->string('major')->nullable();
            $table->string('academic_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
