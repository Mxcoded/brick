<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waiter_shifts', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('property_id');
        });

        Schema::table('restaurant_stock_movements', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('property_id');
        });

        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->dropUnique('restaurant_settings_key_unique');
            $table->unique(['property_id', 'key']);
        });

        // Backfill property_id from parent relationships
        DB::table('waiter_shifts')
            ->join('users', 'waiter_shifts.user_id', '=', 'users.id')
            ->join('property_user', function ($join) {
                $join->on('property_user.user_id', '=', 'users.id')
                    ->where('property_user.is_default', true);
            })
            ->whereNull('waiter_shifts.property_id')
            ->update(['waiter_shifts.property_id' => DB::raw('property_user.property_id')]);

        DB::table('restaurant_stock_movements')
            ->join('restaurant_stock_items', 'restaurant_stock_movements.restaurant_stock_item_id', '=', 'restaurant_stock_items.id')
            ->whereNull('restaurant_stock_movements.property_id')
            ->whereNotNull('restaurant_stock_items.property_id')
            ->update(['restaurant_stock_movements.property_id' => DB::raw('restaurant_stock_items.property_id')]);
    }

    public function down(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->dropUnique(['property_id', 'key']);
            $table->unique('key');
        });

        Schema::table('restaurant_stock_movements', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id']);
            $table->dropColumn('property_id');
        });

        Schema::table('waiter_shifts', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id']);
            $table->dropColumn('property_id');
        });
    }
};
