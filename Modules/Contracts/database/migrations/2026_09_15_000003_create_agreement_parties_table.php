<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_parties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            $table->string('party_role')->default('client'); // hotel | client | guarantor | other
            $table->string('legal_name');
            $table->string('entity_type')->default('company'); // individual | company
            $table->string('registration_no')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('position')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('agreement_id')->references('id')->on('agreements')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_parties');
    }
};
