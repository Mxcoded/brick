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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_id');
            $table->decimal('payment_amount', 10, 2);
            $table->datetime('payment_date');
            $table->enum('payment_status', ['paid', 'partial', 'pending', 'overdue']);
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'pos', 'crypto']);
            $table->enum('payment_type', ['full', 'partial'])->default('full');
            $table->decimal('remaining_balance', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
            // $table->unique(['membership_id', 'payment_date'], 'payments_membership_id_payment_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
