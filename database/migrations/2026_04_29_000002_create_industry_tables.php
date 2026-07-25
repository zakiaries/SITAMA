<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // job_listings bentuk awal (bergantung company). Diubah jadi gaya pengumuman
        // + kolom bidang oleh migrasi 2026_06_14 & 2026_07_19.
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('division')->nullable();
            $table->text('description')->nullable();
            $table->json('skills')->nullable();
            $table->string('location')->nullable();
            $table->string('job_type')->default('On-site');
            $table->integer('quota')->default(1);
            $table->integer('duration_months')->default(3);
            $table->string('pic_email')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamps();
        });

        // applications: alur lamaran lama (legacy; kolom type ditambah 2026_05_31).
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();

            $table->unique(['student_id', 'job_listing_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('job_listings');
    }
};
