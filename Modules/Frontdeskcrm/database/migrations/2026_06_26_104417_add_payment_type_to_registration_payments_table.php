<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('registration_payments', 'payment_type')) {
                $table->string('payment_type', 30)->default('payment');
            }
            if (! Schema::hasColumn('registration_payments', 'payment_category')) {
                $table->string('payment_category', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('registration_payments', function (Blueprint $table) {
            if (Schema::hasColumn('registration_payments', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
            if (Schema::hasColumn('registration_payments', 'payment_category')) {
                $table->dropColumn('payment_category');
            }
        });
    }
};
