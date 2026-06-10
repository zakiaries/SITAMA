<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');

            $table->string('company_name');
            $table->string('company_address')->nullable();
            $table->string('company_field')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();

            $table->string('pic_name');
            $table->string('pic_email')->nullable();
            $table->string('pic_phone')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('created_lecturer_id')->nullable()->constrained('lecturers')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_requests');
    }
};
