<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD §19/§22/§27 — central media library. Images are optimised on upload
 * (resized + re-encoded) and get a thumbnail; content records store the
 * resulting public URL in their own image columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 32)->default('public');
            $table->string('path');                      // optimised image
            $table->string('thumb_path')->nullable();    // ~480px preview
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');          // bytes, after optimisation
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();      // PRD §34/§36
            $table->string('caption')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
