<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('the_class')->nullable()->change();
            $table->string('study_program')->nullable()->change();
            $table->string('major')->nullable()->change();
            $table->string('academic_year')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('the_class')->nullable(false)->change();
            $table->string('study_program')->nullable(false)->change();
            $table->string('major')->nullable(false)->change();
            $table->string('academic_year')->nullable(false)->change();
        });
    }
};
