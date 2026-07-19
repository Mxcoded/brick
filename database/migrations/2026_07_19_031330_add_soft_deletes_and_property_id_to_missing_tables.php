<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['guests', 'registrations', 'restaurant_menu_items'];
        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && ! Schema::hasColumn($tbl, 'deleted_at')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('restaurant_menu_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
