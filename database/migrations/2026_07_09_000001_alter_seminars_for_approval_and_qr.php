<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Seminar mahasiswa kini butuh ACC Kaprodi: 'pending' (menunggu),
        // 'scheduled' (disetujui), 'rejected' (ditolak, ajukan ulang).
        DB::statement("ALTER TABLE `seminars` MODIFY `status` ENUM('pending','scheduled','completed','cancelled','rejected') NOT NULL DEFAULT 'pending'");

        Schema::table('seminars', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->string('access_token', 64)->nullable()->unique()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'access_token']);
        });

        DB::statement("ALTER TABLE `seminars` MODIFY `status` ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled'");
    }
};
