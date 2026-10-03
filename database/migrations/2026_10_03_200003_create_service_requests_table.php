<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** PRD §12 — Service Request (Flow A). Statuses: App\Enums\RequestStatus. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();              // SRQ-...
            $table->string('name');
            $table->string('organization')->nullable();
            $table->string('email');
            $table->string('phone', 30);
            $table->foreignId('business_division_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location')->nullable();
            $table->date('preferred_date')->nullable();
            $table->text('requirements');
            $table->string('budget_range')->nullable();
            $table->string('attachment')->nullable();               // "private" disk
            $table->boolean('consent')->default(false);             // contact-consent checkbox
            $table->string('status')->default('new')->index();      // App\Enums\RequestStatus
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
