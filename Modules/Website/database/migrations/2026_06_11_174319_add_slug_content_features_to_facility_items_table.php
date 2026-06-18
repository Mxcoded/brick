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
        Schema::table('facility_items', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('sort_order');
            $table->text('content')->nullable()->after('slug');
            $table->json('features')->nullable()->after('content');
            $table->boolean('is_active')->default(true)->after('features');
        });
    }

    public function down(): void
    {
        Schema::table('facility_items', function (Blueprint $table) {
            $table->dropColumn(['slug', 'content', 'features', 'is_active']);
        });
    }
};
