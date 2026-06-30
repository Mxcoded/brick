<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_id')->nullable()->after('id');
            $table->decimal('subtotal', 12, 2)->default(0)->after('tracking_status');
            $table->decimal('discount', 12, 2)->default(0)->after('subtotal');
            $table->string('discount_type')->nullable()->after('discount');
            $table->decimal('vat', 12, 2)->default(0)->after('discount_type');
            $table->decimal('vat_rate', 5, 2)->default(0)->after('vat');
            $table->decimal('grand_total', 12, 2)->default(0)->after('vat_rate');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropColumn(['shift_id', 'subtotal', 'discount', 'discount_type', 'vat', 'vat_rate', 'grand_total']);
        });
    }
};
