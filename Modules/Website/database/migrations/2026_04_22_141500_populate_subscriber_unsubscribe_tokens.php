<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Generate unsubscribe tokens for any subscribers that don't have one
        $subscribers = DB::table('newsletter_subscribers')
            ->whereNull('unsubscribe_token')
            ->orWhere('unsubscribe_token', '')
            ->get();

        foreach ($subscribers as $subscriber) {
            DB::table('newsletter_subscribers')
                ->where('id', $subscriber->id)
                ->update(['unsubscribe_token' => Str::random(64)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
