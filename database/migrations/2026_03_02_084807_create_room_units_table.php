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
        Schema::create('room_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->string('room_number')->unique(); // Manual input: "101", "102", "Suite A"
            $table->string('floor')->nullable();     // "1st Floor", "Ground", etc.
            $table->enum('status', ['available', 'occupied', 'maintenance', 'blocked'])->default('available');
            $table->text('notes')->nullable();       // Internal notes about the unit
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_type_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_units');
    }
};
