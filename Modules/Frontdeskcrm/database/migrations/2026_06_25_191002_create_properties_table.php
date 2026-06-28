<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 10)->unique()->comment('Short code for reports (e.g. BRK, LAG)');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('currency')->default('NGN');
            $table->string('timezone')->default('Africa/Lagos');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_headquarters')->default(false);
            $table->json('settings')->nullable()->comment('Hotel-specific config (tax rate, check-in/out times, etc.)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
