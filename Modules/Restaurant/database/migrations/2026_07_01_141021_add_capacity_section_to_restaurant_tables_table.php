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
        Schema::table('restaurant_tables', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurant_tables', 'capacity')) {
                $table->integer('capacity')->nullable()->after('number');
            }
            if (!Schema::hasColumn('restaurant_tables', 'section')) {
                $table->string('section')->nullable()->after('capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('restaurant_tables', 'capacity')) {
                $columnsToDrop[] = 'capacity';
            }
            if (Schema::hasColumn('restaurant_tables', 'section')) {
                $columnsToDrop[] = 'section';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
