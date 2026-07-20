<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->string('gateway')->default('paystack');
            $table->string('gateway_reference')->nullable()->index();   // Paystack refund reference (RFR_xxx)
            $table->string('transaction_reference')->nullable()->index(); // Original payment reference (booking/group)

            $table->morphs('refundable');

            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 8)->default('NGN');
            $table->string('status')->default('pending'); // pending | processed | failed | declined

            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
