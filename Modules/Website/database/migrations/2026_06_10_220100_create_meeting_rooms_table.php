<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_page_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('size_sqm', 8, 2)->nullable();
            $table->unsignedInteger('boardroom')->nullable();
            $table->unsignedInteger('classroom')->nullable();
            $table->unsignedInteger('theatre')->nullable();
            $table->unsignedInteger('cocktail')->nullable();
            $table->unsignedInteger('banquet')->nullable();
            $table->unsignedInteger('cabaret')->nullable();
            $table->unsignedInteger('ushape')->nullable();
            $table->unsignedInteger('double_u')->nullable();
            $table->unsignedInteger('triple_u')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_rooms');
    }
};
