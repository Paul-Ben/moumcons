<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 5 — "Key Capabilities" list shown on the division detail page
 * (prototype-docs/business-detail.html). Managed via CMS in Module 9.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_division_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_capabilities');
    }
};
