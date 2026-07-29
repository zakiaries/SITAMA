<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rubrik terpisah per penilai + bobot (rework nilai agar sesuai form resmi).
 * - scorer_type: rubrik ini untuk dosen pembimbing ('lecturer') atau pembimbing
 *   industri ('lecturer_industry').
 * - weight: bobot komponen dalam persen (dipakai form dosen: Proposal 20, Laporan 80;
 *   null/0 = tanpa bobot khusus, mis. rata-rata polos untuk industri).
 * - order: urutan tampil.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_components', function (Blueprint $table) {
            $table->enum('scorer_type', ['lecturer', 'lecturer_industry'])->default('lecturer')->after('name');
            $table->decimal('weight', 5, 2)->nullable()->after('scorer_type');
            $table->integer('order')->default(0)->after('weight');
        });

        Schema::table('detailed_assessment_components', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_components', function (Blueprint $table) {
            $table->dropColumn(['scorer_type', 'weight', 'order']);
        });

        Schema::table('detailed_assessment_components', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
