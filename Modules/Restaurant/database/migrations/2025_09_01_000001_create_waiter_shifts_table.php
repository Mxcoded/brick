<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiter_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->dateTime('clock_in');
            $table->dateTime('clock_out')->nullable();
            $table->decimal('starting_cash', 10, 2)->nullable()->default(0);
            $table->decimal('ending_cash', 10, 2)->nullable();
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->string('status')->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiter_shifts');
    }
};
