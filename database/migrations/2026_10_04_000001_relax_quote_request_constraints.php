<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD §13 — fixes quote_requests schema drift:
 * - service_id is optional in the request form but the column is NOT NULL
 * - status default 'new' is not a valid QuoteStatus value ('requested' first)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->change();
            $table->string('status')->default('requested')->change();
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->string('status')->default('new')->change();
        });
    }
};
