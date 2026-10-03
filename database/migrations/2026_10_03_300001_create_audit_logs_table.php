<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD §27 (audit_logs) / Master Doc §14 — administrative audit trail.
 * Append-only log of authenticated actions across the admin area and
 * triage workflows. Never exposed on public routes (PRD §32).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100)->index();          // e.g. 'login', 'logout', 'enquiry.updated'
            $table->string('description')->nullable();       // human-readable summary
            $table->nullableMorphs('subject');               // audited model (subject_type/subject_id)
            $table->json('properties')->nullable();          // changed attributes / context payload
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
