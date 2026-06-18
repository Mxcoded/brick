<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->constrained('lead_events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};
