<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            $table->string('party_role')->default('client'); // hotel | client
            $table->string('party_name');
            $table->string('position')->nullable();
            $table->string('signature_type')->default('click'); // click | draw | upload | typed
            $table->text('signature_data')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('verification')->nullable(); // email / otp reference
            $table->string('hash', 64)->nullable();
            $table->timestamps();

            $table->foreign('agreement_id')->references('id')->on('agreements')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_signatures');
    }
};
