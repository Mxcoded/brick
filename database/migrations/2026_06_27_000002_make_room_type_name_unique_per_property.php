<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['slug']);
            $table->unique(['property_id', 'name']);
            $table->unique(['property_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropUnique(['property_id', 'name']);
            $table->dropUnique(['property_id', 'slug']);
            $table->unique('name');
            $table->unique('slug');
        });
    }
};
