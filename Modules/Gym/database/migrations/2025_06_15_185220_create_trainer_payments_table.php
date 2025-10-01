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
        Schema::create('trainer_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainer_id');
            $table->unsignedBigInteger('membership_id')->nullable();
            $table->decimal('payment_amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_type', ['full', 'partial'])->default('full');
            $table->decimal('remaining_balance', 10, 2)->default(0.00);
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'pos', 'crypto']);
            $table->timestamps();

            $table->foreign('trainer_id')->references('id')->on('trainers')->onDelete('cascade');
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers_payments');
    }
};
