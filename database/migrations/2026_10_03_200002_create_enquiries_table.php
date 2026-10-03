<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** PRD §21 — Enquiry fields, verbatim. All enquiries are persisted here. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();          // human-friendly triage ref (ENQ-...)
            $table->string('name');
            $table->string('organization')->nullable();
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('subject');
            $table->foreignId('business_division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->string('attachment')->nullable();           // stored on the "private" disk
            $table->string('status')->default('new')->index();  // App\Enums\EnquiryStatus
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority', 10)->default('normal')->index(); // App\Enums\Priority
            $table->text('internal_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
