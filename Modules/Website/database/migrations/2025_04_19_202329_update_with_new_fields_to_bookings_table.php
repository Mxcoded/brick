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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_company', 255)->nullable()->after('guest_phone');
            $table->text('guest_address')->nullable()->after('guest_company');
            $table->string('guest_nationality', 100)->nullable()->after('guest_address');
            $table->string('guest_id_type', 50)->nullable()->after('guest_nationality');
            $table->string('guest_id_number', 100)->nullable()->after('guest_id_type');
            $table->integer('number_of_guests')->default(1)->after('check_out');
            $table->integer('number_of_children')->default(0)->after('number_of_guests');
            $table->text('special_requests')->nullable()->after('number_of_children');
            $table->decimal('total_price', 10, 2)->after('special_requests');
            $table->decimal('deposit_amount', 10, 2)->nullable()->after('total_price');
            $table->string('payment_status', 50)->default('pending')->after('deposit_amount');
            $table->string('payment_method', 50)->nullable()->after('payment_status');
            $table->string('source', 50)->nullable()->after('payment_method');
            $table->time('check_in_time')->nullable()->after('source');
            $table->time('check_out_time')->nullable()->after('check_in_time');
            $table->unsignedBigInteger('assigned_staff_id')->nullable()->after('check_out_time');
            $table->string('room_status', 50)->nullable()->after('assigned_staff_id');
            $table->unsignedBigInteger('created_by')->nullable()->after('room_status');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->timestamp('cancelled_at')->nullable()->after('updated_by');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');

            // Foreign keys
         
            $table->foreign('assigned_staff_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            
        });
    }
};
