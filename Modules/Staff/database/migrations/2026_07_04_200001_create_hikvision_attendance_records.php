<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hikvision_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('original_id', 100)->unique()->comment('Hikvision machine record UID');
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->string('pin', 50)->index();
            $table->dateTime('punch_time');
            $table->string('punch_type', 20)->nullable()->comment('in / out / unknown');
            $table->json('raw_data')->nullable();
            $table->dateTime('imported_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hikvision_attendance_records');
    }
};
