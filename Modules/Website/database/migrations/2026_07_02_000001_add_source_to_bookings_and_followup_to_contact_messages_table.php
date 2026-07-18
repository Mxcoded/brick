<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('source', 50)->nullable()->after('admin_notes')
                ->comment('Lead source: website, direct, referral, google, social, etc.');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('last_reply_at')
                ->constrained('users')->nullOnDelete();
            $table->string('follow_up_status', 20)->nullable()->after('assigned_to')
                ->default('pending')
                ->comment('pending, followed_up, closed');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn('follow_up_status');
        });
    }
};
