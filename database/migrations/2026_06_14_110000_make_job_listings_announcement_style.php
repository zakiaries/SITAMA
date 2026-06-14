<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lowongan kini bisa diinput kaprodi sebagai pengumuman, tanpa akun perusahaan.
        // Lepas constraint FK dulu agar kolom bisa nullable (FK dibuat di migrasi awal).
        DB::statement('ALTER TABLE `job_listings` DROP FOREIGN KEY `job_listings_company_id_foreign`');
        DB::statement('ALTER TABLE `job_listings` MODIFY `company_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `job_listings` ADD CONSTRAINT `job_listings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE');

        Schema::table('job_listings', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('company_id');
            $table->string('pic_name')->nullable()->after('pic_email');
            $table->string('pic_phone')->nullable()->after('pic_name');
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'pic_name', 'pic_phone']);
        });

        DB::statement('ALTER TABLE `job_listings` DROP FOREIGN KEY `job_listings_company_id_foreign`');
        DB::statement('ALTER TABLE `job_listings` MODIFY `company_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `job_listings` ADD CONSTRAINT `job_listings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE');
    }
};
