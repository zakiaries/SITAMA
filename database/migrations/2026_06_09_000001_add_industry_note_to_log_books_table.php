<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('log_books', function (Blueprint $table) {
            // Catatan dari pembimbing industri, terpisah dari lecturer_note (dosen kampus).
            $table->text('industry_note')->nullable()->after('lecturer_note');
        });
    }

    public function down()
    {
        Schema::table('log_books', function (Blueprint $table) {
            $table->dropColumn('industry_note');
        });
    }
};
