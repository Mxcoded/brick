<?php

namespace Modules\Frontdeskcrm\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->date('business_date');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('status'); // running, completed, failed
            $table->integer('rooms_occupied')->default(0);
            $table->decimal('total_revenue_posted', 15, 2)->default(0);
            $table->integer('charges_posted')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_audit_logs');
    }
};
