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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // Guest Details (link to guest profile)
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->foreignId('guest_type_id')->nullable()->constrained('guest_types')->onDelete('set null');
            $table->foreignId('booking_source_id')->nullable()->constrained('booking_sources')->onDelete('set null');
            $table->foreignId('finalized_by_agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title')->nullable();
            $table->string('full_name');
            $table->string('nationality')->nullable();
            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('contact_number');
            

            $table->string('email')->nullable(); // Unique constraint should be on guests table
            $table->string('occupation')->nullable();
            $table->string('company_name')->nullable();
            $table->text('home_address')->nullable();

            // Booking Information
            $table->string('room_allocation')->nullable();
            $table->decimal('room_rate', 10, 2)->nullable();
            $table->boolean('bed_breakfast')->default(false);
            $table->date('check_in')->nullable();
            $table->tinyInteger('no_of_guests')->default(1);
            $table->date('check_out')->nullable();
            $table->tinyInteger('no_of_nights')->nullable();
            $table->enum('payment_method', ['cash', 'pos', 'transfer'])->nullable()->default('cash');

            // Group Bookings
            $table->foreignId('parent_registration_id')->nullable()->constrained('registrations')->onDelete('cascade');
            $table->boolean('is_group_lead')->default(false);

            // Emergency Contact
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_contact')->nullable();

            // Status & Lifecycle
            $table->enum('stay_status', [
                'draft_by_guest', // **FIX**: New default
                'checked_in',
                'checked_out',
                'no_show',
                'cancelled'
            ])->default('draft_by_guest'); // **CRITICAL FIX**
            $table->decimal('total_amount', 10, 2)->nullable(); // Calc: rate * nights + fees
            $table->date('checkout_date')->nullable();
            $table->integer('review_rating')->nullable()->unsigned(); // 1-5
            $table->text('review_comment')->nullable();

            // Agreement & Signatures
            $table->boolean('agreed_to_policies')->default(false);
            $table->string('guest_signature')->nullable();
            $table->timestamp('registration_date')->useCurrent();
            $table->string('front_desk_agent')->nullable();  // Nullable for guest drafts

            $table->timestamps();

            // Indexes
            $table->index(['guest_id', 'guest_type_id', 'booking_source_id', 'parent_registration_id'], 'reg_guest_type_booking_group_idx');
            $table->index(['check_in', 'check_out', 'stay_status', 'checkout_date'], 'reg_checkin_checkout_status_idx');
            $table->index(['full_name', 'contact_number'], 'reg_name_contact_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
