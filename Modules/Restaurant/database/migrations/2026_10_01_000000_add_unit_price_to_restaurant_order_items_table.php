<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Freeze the price each line was sold at.
     *
     * Order lines only referenced the menu item, so every document re-priced
     * itself from the live menu: reprinting a receipt after a price change
     * showed the new figure, and could contradict the subtotal frozen on the
     * order header. A receipt is a legal document, so the price it was issued
     * at has to be the price it shows.
     *
     * Existing rows are backfilled from the current menu price. That is the
     * best available answer for history we no longer hold, and it is the same
     * figure those documents have been showing all along, so nothing visibly
     * moves. The column is nullable so that a line whose menu item has since
     * been deleted keeps its history instead of vanishing from the backfill.
     */
    public function up(): void
    {
        if (! Schema::hasTable('restaurant_order_items') || Schema::hasColumn('restaurant_order_items', 'unit_price')) {
            return;
        }

        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 2)->nullable()->after('quantity');
        });

        DB::table('restaurant_order_items as oi')
            ->leftJoin('restaurant_menu_items as mi', 'mi.id', '=', 'oi.restaurant_menu_item_id')
            ->update(['oi.unit_price' => DB::raw('mi.price')]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('restaurant_order_items') || ! Schema::hasColumn('restaurant_order_items', 'unit_price')) {
            return;
        }

        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
};
