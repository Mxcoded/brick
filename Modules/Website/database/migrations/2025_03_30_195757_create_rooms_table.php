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
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Financial & Specs
            $table->decimal('price', 10, 2);
            $table->integer('capacity')->default(2);
            $table->string('size')->nullable();
            $table->string('bed_type')->nullable();

            // Media & Features
            $table->string('video_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('amenities')->nullable();

            // Status & Maintenance
            $table->string('status')->default('available')->index();

            $table->timestamps();
            $table->softDeletes();
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
