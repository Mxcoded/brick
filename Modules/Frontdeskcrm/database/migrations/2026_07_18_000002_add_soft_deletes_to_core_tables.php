<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['registrations', 'guests', 'restaurant_payments', 'employees'];
        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && ! Schema::hasColumn($tbl, 'deleted_at')) {
                Schema::table($tbl, fn (Blueprint $table) => $table->softDeletes());
            }
        }
    }

    public function down(): void
    {
        Schema::table('registrations', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('guests', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('restaurant_payments', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('employees', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
