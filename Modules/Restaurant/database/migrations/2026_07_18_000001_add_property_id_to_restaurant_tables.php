<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'restaurant_tables',
            'restaurant_menu_categories',
            'restaurant_menu_items',
            'restaurant_orders',
            'restaurant_payments',
            'restaurant_customers',
            'restaurant_stock_items',
            'restaurant_settings',
        ];

        foreach ($tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            if (! Schema::hasColumn($tbl, 'property_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->foreignId('property_id')->nullable()->after('id')->constrained()->nullOnDelete();
                    $table->index('property_id');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'restaurant_tables',
            'restaurant_menu_categories',
            'restaurant_menu_items',
            'restaurant_orders',
            'restaurant_payments',
            'restaurant_customers',
            'restaurant_stock_items',
            'restaurant_settings',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['property_id']);
                $table->dropIndex(['property_id']);
                $table->dropColumn('property_id');
            });
        }
    }
};
