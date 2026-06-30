<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('restaurant_menu_categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->dropForeign(['restaurant_menu_categories_id']);
        });

        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->foreignId('restaurant_menu_categories_id')
                ->nullable()
                ->change();
        });

        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->foreign('restaurant_menu_categories_id')
                ->references('id')
                ->on('restaurant_menu_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->dropForeign(['restaurant_menu_categories_id']);
        });

        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->foreignId('restaurant_menu_categories_id')
                ->change();
        });

        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->foreign('restaurant_menu_categories_id')
                ->references('id')
                ->on('restaurant_menu_categories')
                ->onDelete('cascade');
        });

        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('restaurant_menu_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
