<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Important for SEO URLs
            $table->decimal('price', 10, 2);  // "price" not "price_per_night" to match Booking model
            $table->integer('capacity');
            $table->string('size')->nullable();     // e.g. "45 sqm"
            $table->string('bed_type')->nullable(); // e.g. "King Size"

            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // Stored as JSON array ["Wifi", "Pool"]

            // MEDIA COLUMNS
            $table->string('image_url')->nullable(); // Primary/Featured Image
            $table->string('video_url')->nullable(); // YouTube/Vimeo Link

            $table->string('status')->default('available'); // available, maintenance, booked
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes(); // Protect against accidental deletion
        });
    }

    public function down(): void
    {
        // FIX: Disable checks to allow dropping referenced table
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('rooms');
        Schema::enableForeignKeyConstraints();
    }
};
