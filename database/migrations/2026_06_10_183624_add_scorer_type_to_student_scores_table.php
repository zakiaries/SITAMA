<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('student_scores', 'scorer_type')) {
                $table->enum('scorer_type', ['lecturer', 'lecturer_industry'])
                    ->default('lecturer')
                    ->after('detailed_assessment_component_id');
            }
        });

        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropForeign('student_scores_internship_id_foreign');
            $table->dropUnique('scores_unique');
        });

        Schema::table('student_scores', function (Blueprint $table) {
            $table->unique(
                ['internship_id', 'detailed_assessment_component_id', 'scorer_type'],
                'scores_unique'
            );
            $table->foreign('internship_id')->references('id')->on('internships')
                ->onDelete('cascade')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropForeign('student_scores_internship_id_foreign');
            $table->dropUnique('scores_unique');
        });

        Schema::table('student_scores', function (Blueprint $table) {
            $table->unique(['internship_id', 'detailed_assessment_component_id'], 'scores_unique');
            $table->foreign('internship_id')->references('id')->on('internships')
                ->onDelete('cascade')->onUpdate('restrict');
            $table->dropColumn('scorer_type');
        });
    }
};
