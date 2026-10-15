<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A sub-ledger ties a journal line to the person or company it belongs to, so
 * the AR/AP control account can be split back out per customer without a
 * separate book-keeping exercise. The columns are polymorphic: party_type holds
 * the owning model's class and party_id its key.
 *
 * Kept nullable because the vast majority of lines - all the revenue, tax,
 * expense and asset movements - have no sub-ledger. Only the line that touches
 * AR or AP carries a party.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_journal_lines', function (Blueprint $table) {
            $table->string('party_type', 191)->nullable()->after('account_id');
            $table->unsignedBigInteger('party_id')->nullable()->after('party_type');

            $table->index(['party_type', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::table('finance_journal_lines', function (Blueprint $table) {
            $table->dropIndex(['party_type', 'party_id']);
            $table->dropColumn(['party_type', 'party_id']);
        });
    }
};
