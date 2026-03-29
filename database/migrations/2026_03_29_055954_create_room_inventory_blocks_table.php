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
        Schema::create('room_inventory_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('blocked_count')->default(0); // Number of rooms to block
            $table->enum('block_type', ['maintenance', 'stop_sell', 'manual', 'overbooking_protection'])->default('manual');
            $table->integer('min_stay')->nullable(); // Minimum night stay restriction
            $table->integer('max_stay')->nullable(); // Maximum night stay restriction
            $table->boolean('stop_sell')->default(false); // Stop selling this room type
            $table->boolean('closed_to_arrival')->default(false); // No check-ins on these dates
            $table->boolean('closed_to_departure')->default(false); // No check-outs on these dates
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for fast lookups
            $table->index(['room_type_id', 'start_date', 'end_date']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_inventory_blocks');
    }
};
