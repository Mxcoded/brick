<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method'); // cash, card, mobile_money, transfer
            $table->string('reference')->nullable();
            $table->decimal('change_due', 12, 2)->default(0);
            $table->string('status')->default('completed'); // pending, completed, failed, refunded
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_payments');
    }
};
