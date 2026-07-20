<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // paystack, stripe, ...
            $table->string('name');
            $table->string('driver');            // matches the code / driver class map
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->text('credentials')->nullable(); // encrypted at rest via model cast (stores an encrypted string)
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
