<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique();
            $table->foreignId('requester_id')->constrained('users');
            $table->string('department')->nullable();
            $table->string('urgency')->default('normal');
            $table->text('justification');
            $table->string('status')->default('draft');
            $table->string('current_role')->nullable();
            $table->string('gl_code')->nullable();
            $table->string('cost_center')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->string('invoice_path')->nullable();
            $table->json('pricing_details')->nullable();
            $table->text('procurement_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
