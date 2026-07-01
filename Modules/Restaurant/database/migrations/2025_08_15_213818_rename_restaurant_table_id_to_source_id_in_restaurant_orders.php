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
        if (!Schema::hasColumn('restaurant_orders', 'restaurant_table_id') || Schema::hasColumn('restaurant_orders', 'source_id')) {
            return;
        }

        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->renameColumn('restaurant_table_id', 'source_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('restaurant_orders', 'source_id') || Schema::hasColumn('restaurant_orders', 'restaurant_table_id')) {
            return;
        }

        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->renameColumn('source_id', 'restaurant_table_id');
        });
    }
};
