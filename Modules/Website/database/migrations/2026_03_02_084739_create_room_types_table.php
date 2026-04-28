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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->integer('capacity');
            $table->string('size')->nullable();     // e.g. "45 sqm"
            $table->string('bed_type')->nullable(); // e.g. "King Size"
            $table->text('description')->nullable();

            // Media
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();

            // Display & Status
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        // Update amenity_room pivot table to reference room_types
        Schema::create('amenity_room_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amenity_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->unique(['amenity_id', 'room_type_id']);
        });

        // Update room_images to reference room_types
        Schema::create('room_type_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->string('image_url');
            $table->string('path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_type_images');
        Schema::dropIfExists('amenity_room_type');
        Schema::dropIfExists('room_types');
    }
};
