<?php

namespace Modules\Frontdeskcrm\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_code_id')->constrained('rate_codes')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('rate', 12, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('available_rooms')->nullable();
            $table->timestamps();

            $table->unique(['rate_code_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_calendar');
    }
};
