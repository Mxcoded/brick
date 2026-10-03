<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Service charge used to exist only as an invisible term inside grand_total.
 *
 * posSubmitOrder() added it to the total and then dropped it on the floor, so
 * nothing could report it, nothing could refund it, and once the rate was
 * changed there was no way to decompose a historic order. These two columns give
 * the figure a home of its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurant_orders', 'service_charge')) {
                $table->decimal('service_charge', 12, 2)->default(0)->after('grand_total');
            }
            if (! Schema::hasColumn('restaurant_orders', 'service_charge_rate')) {
                $table->decimal('service_charge_rate', 5, 2)->default(0)->after('service_charge');
            }
        });

        // Recover the amount on orders placed before this migration. The POS
        // formula was subtotal - discount + vat + service_charge, so the
        // difference is exact. GREATEST guards the max(0, ...) clamp: an order
        // whose total was floored at zero would otherwise backfill negative.
        DB::statement(
            'UPDATE restaurant_orders
             SET service_charge = GREATEST(ROUND(grand_total - (subtotal - discount + vat), 2), 0)
             WHERE grand_total > 0'
        );

        // service_charge_rate stays 0 on purpose. The amount is recoverable from
        // the arithmetic; the percentage that produced it is not, since the
        // rate is a single global setting that has been edited over time.
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            foreach (['service_charge_rate', 'service_charge'] as $column) {
                if (Schema::hasColumn('restaurant_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
