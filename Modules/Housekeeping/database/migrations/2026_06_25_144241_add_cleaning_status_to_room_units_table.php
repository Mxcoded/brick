<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_units', function (Blueprint $table) {
            $table->enum('cleaning_status', ['dirty', 'cleaning', 'clean', 'inspected'])->default('clean')->after('status');
            $table->timestamp('last_cleaned_at')->nullable()->after('cleaning_status');
            $table->foreignId('last_cleaned_by')->nullable()->constrained('users')->nullOnDelete()->after('last_cleaned_at');
        });
    }

    public function down(): void
    {
        Schema::table('room_units', function (Blueprint $table) {
            $table->dropForeign(['last_cleaned_by']);
            $table->dropColumn(['cleaning_status', 'last_cleaned_at', 'last_cleaned_by']);
        });
    }
};
