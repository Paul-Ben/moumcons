<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD §9/§22/§27 — CMS pages and the leadership team.
 *
 * System pages (About, Mission & Vision, Leadership, University Relationship,
 * Privacy, Terms) carry a fixed `key` that their route resolves; editors change
 * their content but cannot delete them or move their URL. Other pages are
 * served at /pages/{slug}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable()->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary', 300)->nullable();
            $table->longText('content')->nullable();       // rich text
            $table->string('hero_image')->nullable();
            $table->string('status')->default('draft')->index(); // App\Enums\PageStatus
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('leadership_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leadership_members');
        Schema::dropIfExists('pages');
    }
};
