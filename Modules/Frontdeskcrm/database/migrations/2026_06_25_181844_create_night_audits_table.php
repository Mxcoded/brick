<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_audits', function (Blueprint $table) {
            $table->id();
            $table->date('audit_date')->unique();
            $table->string('status', 20)->default('open');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('started_by')->constrained('users');
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->unsignedSmallInteger('checked_in_count')->default(0);
            $table->unsignedSmallInteger('occupancy_count')->default(0);
            $table->unsignedSmallInteger('total_rooms')->default(0);
            $table->decimal('occupancy_percentage', 5, 2)->default(0);
            $table->decimal('room_revenue', 12, 2)->default(0);
            $table->decimal('extra_revenue', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('total_payments', 12, 2)->default(0);
            $table->unsignedSmallInteger('charges_posted')->default(0);
            $table->unsignedSmallInteger('payments_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('night_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_audit_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('auditable');
            $table->string('action', 50);
            $table->string('description', 255);
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_audit_logs');
        Schema::dropIfExists('night_audits');
    }
};
