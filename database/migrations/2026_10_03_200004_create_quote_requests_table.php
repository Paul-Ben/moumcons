<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** PRD §13 — Quote Request (Flow B). Statuses: App\Enums\QuoteStatus. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();              // QTE-...
            $table->string('name');
            $table->string('organization')->nullable();
            $table->string('email');
            $table->string('phone', 30);
            $table->foreignId('business_division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('project_title');
            $table->string('location')->nullable();
            $table->text('requirements');
            $table->string('estimated_quantity')->nullable();       // quantity / size
            $table->date('desired_start_date')->nullable();
            $table->date('desired_completion_date')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('attachment')->nullable();               // "private" disk
            $table->boolean('consent')->default(false);
            $table->string('status')->default('new')->index();      // App\Enums\QuoteStatus
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
