<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'rejected'])->default('pending')->after('academic_year');
        });

        // Existing students are already verified — mark them active
        DB::table('students')->update(['status' => 'active']);
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
