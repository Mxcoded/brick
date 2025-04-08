<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenity_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->onDelete('cascade')
                ->name('fk_amenity_room_room_id');
            $table->foreignId('amenity_id')
                ->constrained('amenities')
                ->onDelete('cascade')
                ->name('fk_amenity_room_amenity_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_room');
    }
};