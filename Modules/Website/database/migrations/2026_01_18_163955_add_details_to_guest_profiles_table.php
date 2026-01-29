<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            // Address / Location (To match Controller inputs)
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();

            // Identification (To match Controller inputs)
            $table->string('identification_type')->nullable(); // Passport, NIN, Drivers License
            $table->string('identification_number')->nullable();

            // CRM Alignment Fields (To match Frontdeskcrm Guest Model)
            $table->string('title')->nullable(); // Mr, Mrs, etc.
            $table->string('gender')->nullable();
            $table->date('birthday')->nullable();
            $table->string('nationality')->nullable(); // Can map to country
            $table->string('emergency_contact')->nullable(); // Helpful for check-in
        });
    }

    public function down(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'city',
                'state',
                'country',
                'zip_code',
                'identification_type',
                'identification_number',
                'title',
                'gender',
                'birthday',
                'nationality',
                'emergency_contact'
            ]);
        });
    }
};
