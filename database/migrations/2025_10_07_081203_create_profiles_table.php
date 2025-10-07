<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dev_profile', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->text('short_bio');
            $table->longText('long_description')->comment('story');
            $table->longText('professional_summary');
            $table->foreignId('avatar_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('resume_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->string('github_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('website_url')->nullable();
            $table->tinyInteger('years_experience')->nullable();
            $table->json('languages')->nullable();
            $table->string('university')->nullable();
            $table->string('degree')->nullable();
            $table->date('start_date_uni')->nullable();
            $table->date('end_date_uni')->nullable();
            $table->string('degree_url')->nullable();
            $table->string('diploma_thesis_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dev_profile');
    }
};
