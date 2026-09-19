<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->tinyInteger('cleanliness')->nullable()->after('rating');
            $table->tinyInteger('wifi_rating')->nullable()->after('cleanliness');
            $table->tinyInteger('staff_rating')->nullable()->after('wifi_rating');
            $table->tinyInteger('food_rating')->nullable()->after('staff_rating');
            $table->tinyInteger('maintenance_rating')->nullable()->after('food_rating');
            $table->string('location')->nullable()->after('event_name');
            $table->string('ap_name')->nullable()->after('location');
            $table->string('ap_mac')->nullable()->after('ap_name');
            $table->string('ssid')->nullable()->after('ap_mac');
            $table->string('client_mac')->nullable()->after('ssid');
            $table->string('client_ip')->nullable()->after('client_mac');
            $table->string('portal_session')->nullable()->after('client_ip');
            $table->json('wifi_meta')->nullable()->after('portal_session');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn([
                'cleanliness',
                'wifi_rating',
                'staff_rating',
                'food_rating',
                'maintenance_rating',
                'location',
                'ap_name',
                'ap_mac',
                'ssid',
                'client_mac',
                'client_ip',
                'portal_session',
                'wifi_meta',
            ]);
        });
    }
};
