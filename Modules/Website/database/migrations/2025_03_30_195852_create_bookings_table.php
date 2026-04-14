<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique();

            // Relationships
            $table->foreignId('room_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('guest_profile_id')->nullable()->index();

            // Guest Snapshot
            $table->string('guest_name');
            $table->string('guest_email')->index();
            $table->string('guest_phone')->nullable();

            // Schedule
            $table->date('check_in_date')->index();
            $table->date('check_out_date')->index();
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);

            // Financials
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();

            // State Management
            $table->string('status')->default('pending')->index();
            $table->string('confirmation_token')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        // FIX: Disable checks to allow dropping table
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bookings');
        Schema::enableForeignKeyConstraints();
    }
};
