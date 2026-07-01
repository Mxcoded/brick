<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('restaurant_stock_items')) {
            Schema::create('restaurant_stock_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('unit');
                $table->decimal('stock_quantity', 12, 3)->default(0);
                $table->decimal('min_stock_level', 12, 3)->default(0);
                $table->decimal('unit_cost', 12, 2)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('restaurant_recipe_items')) {
            Schema::create('restaurant_recipe_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('restaurant_menu_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('restaurant_stock_item_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity', 12, 3);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('restaurant_stock_movements')) {
            Schema::create('restaurant_stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('restaurant_stock_item_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->decimal('quantity', 12, 3);
                $table->decimal('unit_cost', 12, 2)->nullable();
                $table->text('reference')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_stock_movements');
        Schema::dropIfExists('restaurant_recipe_items');
        Schema::dropIfExists('restaurant_stock_items');
    }
};
