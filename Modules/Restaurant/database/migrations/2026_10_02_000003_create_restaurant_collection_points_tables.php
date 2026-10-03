<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Collection points: the physical terminals money is taken on.
 *
 * Deliberately a different thing from a sales point. A sales point answers "which
 * counter sold this", and credits an income account. A collection point answers
 * "whose till did it land in", and is debited an asset account. A cafe's counter
 * taking a card payment through the Monipoint at the front desk is one sale, one
 * revenue account and one bank account, and forcing those onto a single row
 * would mean either duplicating the cafe per terminal or losing which bank the
 * money went to.
 *
 * A payment therefore points at both, and they are allowed to disagree: the same
 * revenue account can be collected through several terminals, and a terminal can
 * serve more than one counter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_collection_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Printed on shift reports and used in exports.
            $table->string('code', 50)->unique();
            // The asset account this terminal's takings are banked into. Left
            // null, Finance's own payment-method mapping decides, which is how
            // cash keeps landing in the till account and card in the bank.
            $table->string('asset_account_code', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });

        // The terminal a waiter works on, so every payment they take for the
        // rest of the shift is reconciled against the same bank account.
        Schema::table('waiter_shifts', function (Blueprint $table) {
            $table->foreignId('collection_point_id')
                ->nullable()
                ->after('sales_point_id')
                ->constrained('restaurant_collection_points')
                ->nullOnDelete();
        });

        // Snapshotted on the order and not read back from the shift: a refund
        // months later has to credit the revenue and debit the bank account the
        // original payment really used, even if the terminal has been retired
        // or re-pointed since.
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->foreignId('collection_point_id')
                ->nullable()
                ->after('sales_point_id')
                ->constrained('restaurant_collection_points')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_point_id');
        });

        Schema::table('waiter_shifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_point_id');
        });

        Schema::dropIfExists('restaurant_collection_points');
    }
};
