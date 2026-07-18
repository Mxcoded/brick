<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurant_orders', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('restaurant_orders', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('tracking_status');
            }
            if (! Schema::hasColumn('restaurant_orders', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('restaurant_orders', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('discount');
            }
            if (! Schema::hasColumn('restaurant_orders', 'vat')) {
                $table->decimal('vat', 12, 2)->default(0)->after('discount_type');
            }
            if (! Schema::hasColumn('restaurant_orders', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(0)->after('vat');
            }
            if (! Schema::hasColumn('restaurant_orders', 'grand_total')) {
                $table->decimal('grand_total', 12, 2)->default(0)->after('vat_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $columns = ['shift_id', 'subtotal', 'discount', 'discount_type', 'vat', 'vat_rate', 'grand_total'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('restaurant_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
