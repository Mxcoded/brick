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
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('location', 100); // User-entered location
            $table->dateTime('complaint_datetime');
            $table->text('nature_of_complaint');
            $table->string('lodged_by', 100);
            $table->string('received_by', 100)->nullable();
            $table->decimal('cost_of_fixing', 10, 2)->nullable();
            $table->date('completion_date')->nullable();
            $table->enum('status', ['new', 'in_progress', 'completed', 'cancelled'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
