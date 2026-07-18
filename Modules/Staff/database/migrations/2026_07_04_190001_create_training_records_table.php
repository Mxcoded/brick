<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('course_name');
            $table->string('provider')->nullable();
            $table->enum('training_type', ['internal', 'external', 'certification'])->default('internal');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('duration_hours', 6, 1)->nullable();
            $table->enum('status', ['enrolled', 'in_progress', 'completed', 'cancelled'])->default('enrolled');
            $table->string('certification_name')->nullable();
            $table->string('certification_url')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_records');
    }
};
