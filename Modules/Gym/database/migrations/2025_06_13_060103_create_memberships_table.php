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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->enum('package_type', ['individual', 'couple']);
            $table->enum('subscription_plan', ['monthly', 'quarterly', '6months', 'yearly']);
            $table->enum('personal_trainer', ['yes', 'no']);
            $table->integer('sessions')->nullable();
            $table->decimal('total_cost', 10, 2);
            $table->date('start_date');
            $table->date('next_billing_date');
            $table->unsignedBigInteger('created_by');
            $table->date('registration_date');
            $table->boolean('terms_agreed')->default(false);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       

        Schema::dropIfExists('memberships');
    }
};
