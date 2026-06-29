<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_activated')->default(true)->after('role');
        });

        // Akun lecturer_industry yang sudah ada dianggap sudah aktif
        // (dibuat manual sebelum fitur aktivasi ada)
        DB::table('users')
            ->where('role', 'lecturer_industry')
            ->update(['is_activated' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_activated');
        });
    }
};
