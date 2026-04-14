<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // --- RELATIONSHIPS ---
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null');

            // ✅ BOOKING ID (Merged from 2026_01_21 migration)
            // Must be nullable for Walk-ins.
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');

            $table->foreignId('guest_type_id')->nullable()->constrained('guest_types')->onDelete('set null');
            $table->foreignId('booking_source_id')->nullable()->constrained('booking_sources')->onDelete('set null');
            $table->foreignId('finalized_by_agent_id')->nullable()->constrained('users')->onDelete('set null');

            // --- GUEST SNAPSHOT ---
            $table->string('title')->nullable();
            $table->string('full_name');
            $table->string('nationality')->nullable();
            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('company_name')->nullable();
            $table->text('home_address')->nullable();

            // --- STAY DETAILS ---
            // ✅ ROOM ID (Merged from 2025_12_02 migration)
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();

            $table->string('room_allocation')->nullable(); // Snapshot name
            $table->decimal('room_rate', 10, 2)->nullable();
            $table->boolean('bed_breakfast')->default(false);

            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            // Removed duplicate 'checkout_date' column

            $table->tinyInteger('no_of_guests')->default(1);
            $table->tinyInteger('no_of_nights')->nullable();
            $table->string('payment_method')->nullable()->default('cash');

            // ✅ BILLING TYPE (Merged from 2025_10_20 migration)
            $table->string('billing_type')->default('consolidate');

            // --- GROUP LOGIC ---
            $table->foreignId('parent_registration_id')->nullable()->constrained('registrations')->onDelete('cascade');
            $table->boolean('is_group_lead')->default(false);

            // --- EMERGENCY CONTACT ---
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_contact')->nullable();

            // --- STATUS & LIFECYCLE ---
            $table->enum('stay_status', [
                'draft_by_guest',
                'reserved',      // ✅ Added for Future Bookings
                'checked_in',
                'checked_out',
                'no_show',
                'cancelled'
            ])->default('draft_by_guest');

            $table->decimal('total_amount', 10, 2)->nullable();

            // ✅ CHECKOUT AUDIT (Merged from 2025_12_02 migration)
            $table->timestamp('actual_checkout_at')->nullable();
            $table->foreignId('checked_out_by_agent_id')->nullable()->constrained('users')->onDelete('set null');

            // --- REVIEWS ---
            $table->integer('review_rating')->nullable()->unsigned();
            $table->text('review_comment')->nullable();

            // --- AGREEMENTS ---
            $table->boolean('agreed_to_policies')->default(false);
            $table->text('guest_signature')->nullable(); // ✅ Changed to TEXT
            $table->timestamp('registration_date')->useCurrent();
            $table->string('front_desk_agent')->nullable();

            $table->timestamps();

            // Indexes for Performance
            $table->index(['guest_id', 'stay_status']);
            $table->index(['check_in', 'check_out']);
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
