<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customers Table
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable(); // Optional full name
                $table->string('email')->unique();
                $table->string('phone')->unique();
                $table->string('organization')->nullable();
                $table->timestamps();
            });
        }
            // Banquet Orders Table
           if(!Schema::hasTable('banquet_orders')) {
            Schema::create('banquet_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_id')->unique(); // Example: 0001/2025
                $table->date('preparation_date')->default(now());
                $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');

                // Customer Info (still kept for order-specific overrides)
                $table->string('contact_person_name');
                $table->string('department')->nullable();
                $table->string('contact_person_phone');
                $table->string('contact_person_email');
                $table->string('referred_by')->nullable();
                $table->string('contact_person_name_ii')->nullable();
                $table->string('contact_person_phone_ii')->nullable();
                $table->string('contact_person_email_ii')->nullable();

                // Financial Tracking
                $table->decimal('total_revenue', 10, 2)->default(0.00);
                $table->decimal('expenses', 10, 2)->default(0.00);
                $table->decimal('profit_margin', 10, 2)->nullable();

                $table->timestamps();
            });
        }
            // Banquet Order Days Table
            if(!Schema::hasTable('banquet_order_days')) {
            Schema::create('banquet_order_days', function (Blueprint $table) {
                $table->id();
                $table->foreignId('banquet_order_id')->constrained()->onDelete('cascade');
                $table->date('event_date');
                $table->text('event_description')->nullable();
                $table->integer('guest_count');
                $table->enum('event_status', ['Pending', 'Confirmed', 'Cancelled'])->default('Pending');
                $table->enum('event_type', ['Wedding', 'Conference', 'Meeting', 'Banquet', 'Other'])->default('Other');

                // Location and Time
                $table->string('room');
                $table->string('setup_style');
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('duration_minutes')->nullable();
                $table->timestamps();
            });
        }
            // Banquet Order Menu Items Table
            if(!Schema::hasTable('banquet_order_menu_items')) {
            Schema::create('banquet_order_menu_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('banquet_order_day_id')->constrained()->onDelete('cascade');
                $table->string('meal_type'); // Breakfast, Lunch, etc.
                $table->json('menu_items'); // Example: ["Ghana Jollof", "Rice and Beans"]
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->json('dietary_restrictions')->nullable();
                $table->timestamps();
            });
        }
        
    }

    public function down()
    {
        Schema::dropIfExists('banquet_order_menu_items');
        Schema::dropIfExists('banquet_order_days');
        Schema::dropIfExists('banquet_orders');
        Schema::dropIfExists('customers');
    }
};
