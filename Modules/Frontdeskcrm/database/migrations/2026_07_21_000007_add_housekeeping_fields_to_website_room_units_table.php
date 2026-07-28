<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_units', function (Blueprint $table) {
            $table->enum('housekeeping_status', ['clean', 'dirty', 'inspected', 'out_of_service'])->default('clean')->after('status');
            $table->dateTime('last_cleaned_at')->nullable()->after('housekeeping_status');
            $table->foreignId('last_cleaned_by')->nullable()->constrained('users')->nullOnDelete()->after('last_cleaned_at');
        });
    }

    public function down(): void
    {
        Schema::table('room_units', function (Blueprint $table) {
            $table->dropForeign(['last_cleaned_by']);
            $table->dropColumn(['housekeeping_status', 'last_cleaned_at', 'last_cleaned_by']);
        });
    }
};
