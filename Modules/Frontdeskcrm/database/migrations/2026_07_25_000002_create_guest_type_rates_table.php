<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_type_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_type_id')->constrained('guest_types')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->decimal('rate', 12, 2)->comment('Negotiated rate per night');
            $table->date('valid_from')->nullable()->comment('Contract start date');
            $table->date('valid_to')->nullable()->comment('Contract end date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['guest_type_id', 'room_type_id', 'valid_from'], 'guest_type_rates_unique');
            $table->index(['guest_type_id', 'is_active']);
            $table->index(['room_type_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_type_rates');
    }
};
