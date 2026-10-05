<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD §15/§27 — training programmes. Online registration and payment are
 * future scope (§4.2); for now visitors register interest, which becomes an
 * enquiry in the triage queue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_division_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary', 300)->nullable();
            $table->text('description')->nullable();        // rich text
            $table->string('course_category')->nullable();
            $table->string('trainer')->nullable();
            $table->string('duration')->nullable();         // free text, e.g. "3 days"
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('registration_deadline')->nullable();
            $table->string('delivery_mode')->default('physical'); // App\Enums\DeliveryMode
            $table->string('venue')->nullable();
            $table->decimal('fee', 12, 2)->nullable();      // null = free / on request
            $table->unsignedInteger('capacity')->nullable();
            $table->text('curriculum')->nullable();         // rich text
            $table->text('requirements')->nullable();       // rich text
            $table->text('certificate_info')->nullable();
            $table->string('status')->default('upcoming')->index(); // App\Enums\TrainingStatus
            $table->string('featured_image')->nullable();
            $table->boolean('featured')->default(false)->index();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_programmes');
    }
};
