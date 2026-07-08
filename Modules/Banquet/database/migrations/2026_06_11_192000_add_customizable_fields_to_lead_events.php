<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_events', function (Blueprint $table) {
            $table->string('hero_image')->nullable()->after('organizer');
            $table->string('hero_subtitle')->nullable()->after('hero_image');
            $table->string('form_heading')->nullable()->after('hero_subtitle');
            $table->text('form_subtext')->nullable()->after('form_heading');
            $table->string('thank_you_message')->nullable()->after('form_subtext');
        });
    }

    public function down(): void
    {
        Schema::table('lead_events', function (Blueprint $table) {
            $table->dropColumn(['hero_image', 'hero_subtitle', 'form_heading', 'form_subtext', 'thank_you_message']);
        });
    }
};
