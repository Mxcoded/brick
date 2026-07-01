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
        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->dropColumn('is_available');
        });
    }
};
