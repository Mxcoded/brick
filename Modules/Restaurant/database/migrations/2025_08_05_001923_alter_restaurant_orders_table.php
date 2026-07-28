<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            if (Schema::hasColumn('restaurant_orders', 'restaurant_table_id') && ! Schema::hasColumn('restaurant_orders', 'type')) {
                $table->dropForeign(['restaurant_table_id']);
                $table->foreignId('restaurant_table_id')->nullable()->change();
            }

            if (! Schema::hasColumn('restaurant_orders', 'type')) {
                $table->string('type')->default('table')->after('restaurant_table_id');
            }
            if (! Schema::hasColumn('restaurant_orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('type');
            }
            if (! Schema::hasColumn('restaurant_orders', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_name');
            }
            if (! Schema::hasColumn('restaurant_orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('customer_phone');
            }
            if (! Schema::hasColumn('restaurant_orders', 'tracking_status')) {
                $table->string('tracking_status')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $columns = ['type', 'customer_name', 'customer_phone', 'delivery_address', 'tracking_status'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('restaurant_orders', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('restaurant_orders', 'restaurant_table_id')) {
                $table->foreignId('restaurant_table_id')->nullable(false)->change();
            }
        });
    }
};
