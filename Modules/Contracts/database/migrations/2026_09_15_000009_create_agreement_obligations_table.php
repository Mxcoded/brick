<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_obligations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            $table->string('obligation_type'); // payment | service | delivery | entitlement | renewal | other
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('responsible_party')->default('client'); // hotel | client | other
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending'); // pending | in_progress | completed | overdue | cancelled
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('agreement_id')->references('id')->on('agreements')->cascadeOnDelete();
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_obligations');
    }
};
