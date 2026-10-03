<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** PRD §11 — Service fields, verbatim (+ category link). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_division_id')->constrained('business_divisions')->cascadeOnDelete();
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('service_type')->nullable();
            $table->string('pricing_type')->default('quote_required'); // App\Enums\PricingType
            $table->decimal('starting_price', 12, 2)->nullable();      // only shown when pricing_type allows
            $table->boolean('featured')->default(false)->index();
            $table->string('status')->default('active')->index();      // App\Enums\ServiceStatus
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
