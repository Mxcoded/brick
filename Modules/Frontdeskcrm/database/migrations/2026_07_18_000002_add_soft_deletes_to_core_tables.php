<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('guests', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('restaurant_payments', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('employees', fn (Blueprint $table) => $table->softDeletes());
    }

    public function down(): void
    {
        Schema::table('registrations', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('guests', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('restaurant_payments', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('employees', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
