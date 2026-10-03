<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** PRD §10 — Business Division fields, verbatim. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('category')->nullable();   // mega-menu group (Design System §19)
            $table->string('status')->default('active')->index(); // App\Enums\DivisionStatus
            $table->boolean('featured')->default(false)->index();
            $table->string('icon')->nullable();       // icon key for <x-icon>
            $table->string('hero_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('location')->nullable();
            $table->string('operating_hours')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_divisions');
    }
};
