<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banquet_enquiries', function (Blueprint $table) {
            $table->boolean('accommodation_required')->default(false)->after('catering_required');
            $table->integer('rooms_required')->nullable()->after('accommodation_required');
            $table->date('arrival_date')->nullable()->after('rooms_required');
            $table->date('departure_date')->nullable()->after('arrival_date');
            $table->boolean('parking_required')->default(false)->after('departure_date');
            $table->boolean('site_inspection_required')->default(false)->after('parking_required');
            $table->string('hear_about_us')->nullable()->after('site_inspection_required');
        });
    }

    public function down(): void
    {
        Schema::table('banquet_enquiries', function (Blueprint $table) {
            $table->dropColumn([
                'accommodation_required',
                'rooms_required',
                'arrival_date',
                'departure_date',
                'parking_required',
                'site_inspection_required',
                'hear_about_us',
            ]);
        });
    }
};
