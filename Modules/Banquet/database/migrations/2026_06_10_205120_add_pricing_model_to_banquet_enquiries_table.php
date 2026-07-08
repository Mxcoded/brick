<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banquet_enquiries', function (Blueprint $table) {
            $table->string('pricing_model')->nullable()->after('hear_about_us');
        });
    }

    public function down(): void
    {
        Schema::table('banquet_enquiries', function (Blueprint $table) {
            $table->dropColumn('pricing_model');
        });
    }
};
