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
        if (!Schema::hasTable('banquet_payments')) {
            Schema::create('banquet_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('banquet_order_id')->constrained('banquet_orders')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->string('payment_method'); // Cash, Transfer, POS, Cheque
                $table->string('reference')->nullable(); // Transaction ID or Receipt No
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banquet_payments');
    }
};
