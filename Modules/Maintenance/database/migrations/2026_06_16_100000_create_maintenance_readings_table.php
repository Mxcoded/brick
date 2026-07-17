<?php

namespace Modules\Maintenance\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_readings', function (Blueprint $table) {
            $table->id();
            $table->date('reading_date');
            $table->string('reading_type'); // generator, diesel_reservoir, water_tank, cold_room
            $table->string('category')->default(''); // big_gen, small_gen, freezer, fridge
            $table->decimal('reading_value', 10, 2);
            $table->decimal('capacity', 10, 2)->nullable();
            $table->decimal('calculated_value', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            $table->unique(['reading_date', 'reading_type', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_readings');
    }
};
