<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_ledger_account_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('transaction_type');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('registration_id')->references('id')->on('registrations')->nullOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_ledger_transactions');
    }
};
