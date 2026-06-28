<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event', 50)->unique(); // pre_arrival_confirmation, check_in_welcome, checkout_receipt, review_request, re_engagement, birthday
            $table->string('name');
            $table->text('sms_body')->nullable();
            $table->text('whatsapp_body')->nullable();
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('guest_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->nullOnDelete();
            $table->string('channel', 20); // email, sms, whatsapp
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status', 20)->default('pending'); // pending, sent, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_messages');
        Schema::dropIfExists('message_templates');
    }
};