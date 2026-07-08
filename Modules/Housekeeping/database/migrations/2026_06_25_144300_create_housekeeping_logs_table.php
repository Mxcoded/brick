<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housekeeping_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_unit_id')->constrained('room_units')->cascadeOnDelete();
            $table->foreignId('cleaned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_from', ['dirty', 'cleaning', 'clean', 'inspected']);
            $table->enum('status_to', ['dirty', 'cleaning', 'clean', 'inspected']);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housekeeping_logs');
    }
};
