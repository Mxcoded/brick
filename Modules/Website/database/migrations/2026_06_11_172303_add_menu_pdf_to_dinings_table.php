<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dinings', function (Blueprint $table) {
            $table->text('menu_pdf')->nullable()->after('menu_link');
        });
    }

    public function down(): void
    {
        Schema::table('dinings', function (Blueprint $table) {
            $table->dropColumn('menu_pdf');
        });
    }
};
