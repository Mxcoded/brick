<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_clauses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('title');
            $table->text('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('agreement_id')->references('id')->on('agreements')->cascadeOnDelete();
            $table->foreign('template_id')->references('id')->on('agreement_templates')->cascadeOnDelete();

            $table->index(['agreement_id', 'version']);
            $table->index('template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_clauses');
    }
};
