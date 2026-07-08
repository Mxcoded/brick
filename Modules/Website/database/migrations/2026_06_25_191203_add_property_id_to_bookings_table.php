<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bookings', 'property_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'property_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['property_id']);
                $table->dropColumn('property_id');
            });
        }
    }
};
