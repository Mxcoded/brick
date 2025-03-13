<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('place_of_birth');
            $table->string('state_of_origin');
            $table->string('lga');
            $table->string('nationality');
            $table->string('gender');
            $table->date('date_of_birth');
            $table->string('marital_status');
            $table->string('blood_group');
            $table->string('genotype');
            $table->string('phone_number');
            $table->string('residential_address');
            $table->string('next_of_kin_name');
            $table->string('next_of_kin_phone');
            $table->string('ice_contact_name');
            $table->string('ice_contact_phone');
            $table->string('profile_image')->nullable(); // For profile image upload
            $table->string('cv_path')->nullable(); // For CV upload
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
