<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('restaurant_order_items') && ! Schema::hasColumn('restaurant_order_items', 'split_group')) {
            Schema::table('restaurant_order_items', function (Blueprint $table) {
                $table->string('split_group', 10)->nullable()->after('instructions');
            });
        }

        if (Schema::hasTable('restaurant_payments')) {
            if (! Schema::hasColumn('restaurant_payments', 'registration_id')) {
                Schema::table('restaurant_payments', function (Blueprint $table) {
                    $table->unsignedBigInteger('registration_id')->nullable()->after('restaurant_order_id');
                });
            }
            if (! Schema::hasColumn('restaurant_payments', 'charge_type_id')) {
                Schema::table('restaurant_payments', function (Blueprint $table) {
                    $table->unsignedBigInteger('charge_type_id')->nullable()->after('registration_id');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('restaurant_payments', function (Blueprint $table) {
            $table->dropColumn(['registration_id', 'charge_type_id']);
        });

        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->dropColumn('split_group');
        });
    }
};
