<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurant_payments', 'finance_posted')) {
                $table->boolean('finance_posted')->default(false)->after('notes');
            }
            if (! Schema::hasColumn('restaurant_payments', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('finance_posted');
            }
            if (! Schema::hasColumn('restaurant_payments', 'refund_reason')) {
                $table->string('refund_reason')->nullable()->after('refunded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_payments', function (Blueprint $table) {
            $table->dropColumn(['finance_posted', 'refunded_at', 'refund_reason']);
        });
    }
};
