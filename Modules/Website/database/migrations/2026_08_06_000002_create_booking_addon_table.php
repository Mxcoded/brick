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
        Schema::create('booking_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained('addons')->cascadeOnDelete();

            // Price snapshots so historical bookings survive add-on edits.
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->boolean('is_per_night')->default(false);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 10, 2);

            $table->timestamps();

            $table->unique(['booking_id', 'addon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_addon');
    }
};
