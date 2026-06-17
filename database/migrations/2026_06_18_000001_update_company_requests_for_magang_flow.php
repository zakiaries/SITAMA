<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_requests', function (Blueprint $table) {
            $table->string('proof_file')->nullable()->after('company_email');
            $table->string('position')->nullable()->after('proof_file');
            $table->date('start_date')->nullable()->after('position');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null')->after('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('company_requests', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['proof_file', 'position', 'start_date', 'company_id']);
        });
    }
};
