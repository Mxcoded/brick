<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->enum('category', ['technical', 'soft', 'language', 'certification', 'other'])->default('technical');
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->decimal('years_experience', 4, 1)->nullable();
            $table->date('last_used_date')->nullable();
            $table->boolean('is_certified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'skill_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
    }
};
