<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            // Link to Website Login
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Add fields that were in GuestProfile but missing in Guest
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('identification_type')->nullable(); // Passport, NIN
            $table->string('identification_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'city', 'state', 'zip_code', 'identification_type', 'identification_number']);
        });
    }
};
