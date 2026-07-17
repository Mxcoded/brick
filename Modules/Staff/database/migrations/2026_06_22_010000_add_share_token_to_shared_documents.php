<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Staff\Models\SharedDocument;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shared_documents', function (Blueprint $table) {
            $table->string('share_token', 36)->unique()->nullable()->after('downloads_count');
        });

        // Backfill tokens for existing records
        SharedDocument::whereNull('share_token')->each(function ($doc) {
            $doc->update(['share_token' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('shared_documents', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
    }
};
