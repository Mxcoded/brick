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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_number')->unique();
            $table->date('date');
            $table->unsignedBigInteger('created_by'); // General Manager (User ID)
            $table->text('description');
            $table->enum('priority', ['high', 'medium', 'low']);
            $table->date('deadline');
            $table->boolean('is_completed')->default(false);
            $table->date('completion_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('non_completion_reason')->nullable();
            $table->boolean('is_successful')->nullable();
            $table->boolean('meets_expectations')->nullable();
            $table->text('gm_notes')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // // Drop dependent table first to avoid foreign key constraint error
        // Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('tasks');
    }
};
