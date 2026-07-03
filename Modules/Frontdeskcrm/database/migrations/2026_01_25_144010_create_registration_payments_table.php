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
        Schema::create('registration_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method'); // Cash, POS, Transfer, etc.
            $table->string('reference')->nullable(); // Receipt number or Transaction ID
            $table->text('notes')->nullable(); // "Deposit for room", "Bar bill", etc.
            $table->foreignId('received_by')->nullable()->constrained('users'); // Audit trail
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_payments');
    }
};
