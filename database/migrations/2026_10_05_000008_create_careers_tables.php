<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD §17/§27 — job openings (`careers`) and applications. CVs are stored on
 * the non-public "documents" disk and only staff with view-applications can
 * download them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_division_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->string('employment_type')->default('full_time'); // App\Enums\EmploymentType
            $table->string('summary', 300)->nullable();
            $table->text('description')->nullable();       // rich text
            $table->text('responsibilities')->nullable();  // rich text
            $table->text('qualifications')->nullable();    // rich text
            $table->text('requirements')->nullable();      // rich text
            $table->date('application_deadline')->nullable();
            $table->string('status')->default('open')->index(); // App\Enums\JobStatus
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();       // APP-...
            $table->foreignId('career_id')->constrained('careers')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->text('qualifications')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('cv_path');                       // "documents" disk
            $table->string('cv_original_name');
            $table->boolean('consent')->default(false);
            $table->string('status')->default('received')->index(); // App\Enums\ApplicationStatus
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['career_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('careers');
    }
};
