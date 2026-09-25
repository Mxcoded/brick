<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (! Schema::hasColumn('testimonials', 'location')) {
                $table->string('location')->nullable()->after('event_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'location')) {
                $table->dropColumn(['location']);
            }
        });
    }
};
