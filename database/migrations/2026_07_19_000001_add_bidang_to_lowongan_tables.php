<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            if (! Schema::hasColumn('job_listings', 'bidang')) {
                $table->string('bidang', 100)->nullable()->after('division');
            }
        });

        Schema::table('company_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('company_requests', 'bidang')) {
                $table->string('bidang', 100)->nullable()->after('position');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            if (Schema::hasColumn('job_listings', 'bidang')) {
                $table->dropColumn('bidang');
            }
        });

        Schema::table('company_requests', function (Blueprint $table) {
            if (Schema::hasColumn('company_requests', 'bidang')) {
                $table->dropColumn('bidang');
            }
        });
    }
};
