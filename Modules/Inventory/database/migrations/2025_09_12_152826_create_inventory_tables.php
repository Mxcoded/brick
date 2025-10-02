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
        // Central table for all stores/locations.
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        // New table to store supplier details.
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        // Central catalog of all items. This is not location-specific.
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('category')->nullable();
            $table->decimal('price', 8, 2)->nullable(); // New field for item price
            $table->string('unit_of_measurement')->nullable(); // New field for the measurement unit (e.g., 'kg', 'pcs')
            $table->decimal('unit_value', 8, 2)->nullable(); // New field for the value of the unit (e.g., 1 for 1kg)
            $table->timestamps();
        });

        // This is the core table that links items to specific stores and tracks their quantity.
        // It has been updated to track items by lot number, allowing for different expiry dates.
        Schema::create('store_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('lot_number')->nullable(); // Unique identifier for a specific batch
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('total_cost', 10, 2)->default(0); // New field for total cost of the lot
            $table->date('expiry_date')->nullable(); // Expiry date for this specific lot
            $table->timestamps();

            // Enforce that each item/lot can only have one entry per store.
            $table->unique(['store_id', 'item_id', 'lot_number']);
        });

        // Log of all item transfers between stores.
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_store_id')->nullable()->constrained('stores');
            $table->foreignId('to_store_id')->nullable()->constrained('stores');
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Usage logs, now linked to a specific store.
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity_used');
            $table->string('used_for');
            $table->string('technician_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('store_items');
        Schema::dropIfExists('items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('stores');
    }
};
    