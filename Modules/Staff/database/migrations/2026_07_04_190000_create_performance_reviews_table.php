<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('review_date');
            $table->enum('review_period', ['quarterly', 'annual', 'probation'])->default('annual');
            $table->unsignedTinyInteger('rating_punctuality')->default(3);
            $table->unsignedTinyInteger('rating_teamwork')->default(3);
            $table->unsignedTinyInteger('rating_communication')->default(3);
            $table->unsignedTinyInteger('rating_quality')->default(3);
            $table->unsignedTinyInteger('rating_initiative')->default(3);
            $table->decimal('overall_score', 4, 2)->default(0);
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('comments')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
