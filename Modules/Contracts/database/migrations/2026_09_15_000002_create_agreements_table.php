<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_number')->unique();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('title');
            $table->string('type')->index();
            $table->string('status')->default('draft')->index();
            $table->string('currency', 10)->default('NGN');
            $table->unsignedSmallInteger('current_version')->default(1);
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->decimal('value_amount', 15, 2)->nullable();
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('department')->nullable();
            $table->text('notes')->nullable();
            $table->json('commercial_terms')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('agreement_templates')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['status', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
