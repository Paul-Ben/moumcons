<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD §21 lists the enquiry fields without a consent column, but the contact
 * form stores personal data the same way the service and quote forms do
 * (§12/§13 both record consent). This keeps the three intake flows consistent
 * and gives us a defensible record that the visitor agreed to be contacted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->boolean('consent')->default(false)->after('attachment');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('consent');
        });
    }
};
