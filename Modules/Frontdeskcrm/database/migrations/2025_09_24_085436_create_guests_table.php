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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('full_name');
            $table->string('nationality')->nullable();
            $table->string('contact_number')->unique();
            $table->date('birthday')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('occupation')->nullable();
            $table->string('company_name')->nullable();
            $table->text('home_address')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->timestamp('last_visit_at')->nullable();
            $table->integer('visit_count')->default(1);
            $table->boolean('opt_in_data_save')->default(true);
            $table->timestamps();

            $table->index(['email', 'contact_number', 'full_name', 'last_visit_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
