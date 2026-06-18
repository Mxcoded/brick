<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banquet_enquiries', function (Blueprint $table) {
            $table->dropColumn('pricing_model');
            $table->string('catering_option')->nullable()->after('hear_about_us');
        });
        Schema::table('banquet_enquiries', function (Blueprint $table) {
            $table->dropColumn('catering_required');
        });
    }

    public function down(): void
    {
        Schema::table('banquet_enquiries', function (Blueprint $table) {
            $table->boolean('catering_required')->default(false);
        });
        Schema::table('banquet_enquiries', function (Blueprint $table) {
            $table->dropColumn('catering_option');
            $table->string('pricing_model')->nullable()->after('hear_about_us');
        });
    }
};
