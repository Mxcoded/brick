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
        // Update contact_messages table
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('email');
            $table->boolean('is_archived')->default(false)->after('status');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->foreignId('archived_by')->nullable()->after('archived_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('last_reply_at')->nullable()->after('archived_by');

            // Index for performance
            $table->index(['status', 'is_archived']);
            $table->index('is_archived');
        });

        // Create replies table
        Schema::create('contact_message_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_message_id')->constrained('contact_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing'); // outgoing = staff reply, incoming = guest reply
            $table->boolean('is_read')->default(false);
            $table->string('email_message_id')->nullable(); // For tracking email threads
            $table->timestamps();

            $table->index(['contact_message_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_message_replies');

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex(['status', 'is_archived']);
            $table->dropIndex(['is_archived']);
            $table->dropForeign(['archived_by']);
            $table->dropColumn([
                'subject',
                'is_archived',
                'archived_at',
                'archived_by',
                'last_reply_at',
            ]);
        });
    }
};
