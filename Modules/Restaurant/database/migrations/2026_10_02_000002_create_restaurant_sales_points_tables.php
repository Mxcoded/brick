<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A sales point is a place inside the venue that sells food: a cafe counter, a
 * restaurant floor, a poolside kiosk. A property can run several off one
 * kitchen, and until now every one of them posted into 4100 Restaurant Revenue,
 * so turnover per outlet could only be guessed at from the till slips.
 *
 * Two links make it real:
 *
 * - waiter_shifts.sales_point_id is where the waiter was standing when they
 *   clocked in, which is the outlet the whole shift's orders belong to.
 * - restaurant_orders.sales_point_id is a snapshot taken when the order is
 *   placed, because the order has to keep saying which outlet it came from even
 *   after the shift is closed and the waiter moves to another counter.
 *
 * revenue_account_code is nullable and deliberately so: a sales point with none
 * behaves exactly as before, crediting the module's own revenue account. That
 * makes adding the first sales point a reporting change rather than a ledger
 * change, and only re-pointing its account moves money.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('restaurant_sales_points')) {
            return;
        }

        Schema::create('restaurant_sales_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Shown on the docket and used in exports, so it has to be a short
            // stable token rather than the display name.
            $table->string('code', 50)->unique();
            // A chart-of-accounts code. Null means "use the module revenue
            // account", which keeps the ledger unchanged for a sales point that
            // is only there for reporting.
            $table->string('revenue_account_code', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('restaurant_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurant_orders', 'sales_point_id')) {
                $table->foreignId('sales_point_id')
                    ->nullable()
                    ->after('source_id')
                    ->constrained('restaurant_sales_points')
                    ->nullOnDelete();
            }
        });

        Schema::table('waiter_shifts', function (Blueprint $table) {
            if (! Schema::hasColumn('waiter_shifts', 'sales_point_id')) {
                $table->foreignId('sales_point_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('restaurant_sales_points')
                    ->nullOnDelete();
            }
        });

        // Backfill is deliberately a no-op. Orders and shifts placed before this
        // migration have no outlet to attribute, and guessing "Main Restaurant"
        // at them would put invented revenue on a historical report. The ledger
        // is unaffected: null falls back to the module revenue account, which is
        // exactly what those orders already posted.
    }

    public function down(): void
    {
        Schema::table('waiter_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('waiter_shifts', 'sales_point_id')) {
                $table->dropConstrainedForeignId('sales_point_id');
            }
        });

        Schema::table('restaurant_orders', function (Blueprint $table) {
            if (Schema::hasColumn('restaurant_orders', 'sales_point_id')) {
                $table->dropConstrainedForeignId('sales_point_id');
            }
        });

        Schema::dropIfExists('restaurant_sales_points');
    }
};
