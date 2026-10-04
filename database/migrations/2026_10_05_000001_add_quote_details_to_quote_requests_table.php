<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD §13/§25 — the quote staff prepare in reply to a request. Shown to the
 * customer on the tracking page and in the "quote available" email once the
 * request moves to Quote Sent; never before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->decimal('quoted_amount', 14, 2)->nullable()->after('internal_notes');
            $table->text('quote_message')->nullable()->after('quoted_amount');
            $table->date('quote_valid_until')->nullable()->after('quote_message');
            $table->timestamp('quote_sent_at')->nullable()->after('quote_valid_until');
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn(['quoted_amount', 'quote_message', 'quote_valid_until', 'quote_sent_at']);
        });
    }
};
