<?php

namespace Modules\Frontdeskcrm\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('charge_type'); // room, breakfast, extension, service, other
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('charge_date');
            $table->boolean('is_audited')->default(false);
            $table->foreignId('night_audit_log_id')->nullable()->constrained('night_audit_logs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_charges');
    }
};
