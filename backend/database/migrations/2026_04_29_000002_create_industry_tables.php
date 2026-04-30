<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add verification status to companies
        Schema::table('companies', function (Blueprint $table) {
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                ->default('pending')
                ->after('email');
            $table->text('rejection_reason')->nullable()->after('verification_status');
        });

        // Job listings posted by companies
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('division')->nullable();
            $table->text('description')->nullable();
            $table->json('skills')->nullable();
            $table->string('location')->nullable();
            $table->string('job_type')->default('On-site'); // On-site, WFH, Hybrid
            $table->integer('quota')->default(1);
            $table->integer('duration_months')->default(3);
            $table->string('pic_email')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamps();
        });

        // Student applications to job listings
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
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['verification_status', 'rejection_reason']);
        });
    }
};
