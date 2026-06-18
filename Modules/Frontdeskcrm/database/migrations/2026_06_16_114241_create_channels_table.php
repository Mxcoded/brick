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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->nullable()->comment('booking.com, expedia, agoda, direct, etc.');
            $table->text('api_key')->nullable();
            $table->string('api_endpoint')->nullable();
            $table->string('webhook_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_sync_at')->nullable();
            $table->string('last_sync_status')->nullable()->comment('success, failed, never');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('channel_room_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_unit_id')->constrained('room_units')->cascadeOnDelete();
            $table->string('external_room_id')->nullable();
            $table->string('external_room_name')->nullable();
            $table->timestamps();

            $table->unique(['channel_id', 'room_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
