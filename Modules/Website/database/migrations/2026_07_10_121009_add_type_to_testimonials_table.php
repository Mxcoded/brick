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
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('type', 20)->default('stay')->after('approved');
            $table->string('dining_venue')->nullable()->after('type');
            $table->string('event_name')->nullable()->after('dining_venue');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['type', 'dining_venue', 'event_name']);
        });
    }
};
