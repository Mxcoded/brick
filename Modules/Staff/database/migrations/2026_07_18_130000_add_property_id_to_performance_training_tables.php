<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('performance_reviews')) {
            if (! Schema::hasColumn('performance_reviews', 'property_id')) {
                Schema::table('performance_reviews', function (Blueprint $table) {
                    $table->foreignId('property_id')->nullable()->after('id')->constrained()->nullOnDelete();
                });
            } elseif (! Schema::hasIndex('performance_reviews', 'performance_reviews_property_id_foreign')) {
                Schema::table('performance_reviews', function (Blueprint $table) {
                    $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('training_records')) {
            if (! Schema::hasColumn('training_records', 'property_id')) {
                Schema::table('training_records', function (Blueprint $table) {
                    $table->foreignId('property_id')->nullable()->after('id')->constrained()->nullOnDelete();
                });
            } elseif (! Schema::hasIndex('training_records', 'training_records_property_id_foreign')) {
                Schema::table('training_records', function (Blueprint $table) {
                    $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('employee_skills')) {
            if (! Schema::hasColumn('employee_skills', 'property_id')) {
                Schema::table('employee_skills', function (Blueprint $table) {
                    $table->foreignId('property_id')->nullable()->after('id')->constrained()->nullOnDelete();
                });
            } elseif (! Schema::hasIndex('employee_skills', 'employee_skills_property_id_foreign')) {
                Schema::table('employee_skills', function (Blueprint $table) {
                    $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
                });
            }
        }
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
