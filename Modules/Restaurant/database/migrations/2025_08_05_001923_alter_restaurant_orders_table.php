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
            $table->foreignId('restaurant_table_id')->nullable()->change(); // Make nullable for online orders
            $table->string('type')->default('table')->after('restaurant_table_id'); // 'table' or 'online'
            $table->string('customer_name')->nullable()->after('type');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->text('delivery_address')->nullable()->after('customer_phone');
            $table->string('tracking_status')->nullable()->after('status'); // e.g., 'preparing', 'ready', 'delivered'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropColumn(['type', 'customer_name', 'customer_phone', 'delivery_address', 'tracking_status']);
            $table->foreignId('restaurant_table_id')->nullable(false)->change(); // Revert to not nullable
        });
    }
};
