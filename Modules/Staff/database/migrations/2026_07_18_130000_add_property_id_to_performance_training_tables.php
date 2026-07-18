<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
        });

        Schema::table('training_records', function (Blueprint $table) {
            $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
        });

        Schema::table('employee_skills', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->index();
            $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn('property_id');
        });

        Schema::table('training_records', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn('property_id');
        });

        Schema::table('employee_skills', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn('property_id');
        });
    }
};
