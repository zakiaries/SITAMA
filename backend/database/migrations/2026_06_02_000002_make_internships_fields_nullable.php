<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->foreignId('lecturer_id')->nullable()->change();
            $table->foreignId('company_id')->nullable()->change();
            $table->string('position')->nullable()->change();
            $table->date('start_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->foreignId('lecturer_id')->nullable(false)->change();
            $table->foreignId('company_id')->nullable(false)->change();
            $table->string('position')->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
        });
    }
};
