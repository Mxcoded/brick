<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banquet_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('company')->nullable();
            $table->string('event_type'); // Meeting, Conference, Wedding, Banquet, Party, Other
            $table->date('event_date');
            $table->integer('guest_count');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('setup_style')->nullable();
            $table->boolean('catering_required')->default(false);
            $table->text('special_requirements')->nullable();
            $table->string('venue_interest')->nullable();
            $table->string('status')->default('Pending'); // Pending, Contacted, Converted, Closed
            $table->text('admin_notes')->nullable();
            $table->foreignId('converted_to_order_id')->nullable()->constrained('banquet_orders')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banquet_enquiries');
    }
};
