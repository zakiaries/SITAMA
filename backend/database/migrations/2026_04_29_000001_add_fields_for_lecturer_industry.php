<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_books', function (Blueprint $table) {
            $table->string('category')->nullable()->after('date');
        });

        Schema::table('internships', function (Blueprint $table) {
            $table->text('performance_notes')->nullable()->after('is_finished');
            $table->string('performance_notes_by')->nullable()->after('performance_notes');
            $table->date('performance_notes_date')->nullable()->after('performance_notes_by');
        });
    }

    public function down(): void
    {
        Schema::table('log_books', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn(['performance_notes', 'performance_notes_by', 'performance_notes_date']);
        });
    }
};
