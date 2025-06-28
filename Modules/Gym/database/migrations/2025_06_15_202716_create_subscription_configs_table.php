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
        Schema::create('subscription_configs', function (Blueprint $table) {
            $table->id();
            $table->decimal('monthly_fee', 10, 2);
            $table->decimal('quarterly_fee', 10, 2);
            $table->decimal('six_months_fee', 10, 2);
            $table->decimal('yearly_fee', 10, 2);
            $table->decimal('session_fee', 10, 2); // Fee per personal trainer session
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_configs');
    }
};
