<?php

use App\Models\Property;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Property::count() > 0) {
            return;
        }

        Property::create([
            'name' => 'Brickspoint Asokoro',
            'slug' => 'asokoro',
            'domain' => 'asokoro',
            'code' => 'BRK-A',
            'city' => 'Abuja',
            'state' => 'Federal Capital Territory',
            'country' => 'Nigeria',
            'address' => '24 Yedseram Street, Asokoro',
            'contact_email' => 'rsv@brickspoint.com',
            'contact_phone' => '+234 809 999 9627',
            'is_headquarters' => true,
            'is_active' => true,
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
        ]);

        Property::create([
            'name' => 'Brickspoint Wuse',
            'slug' => 'wuse',
            'domain' => 'wuse',
            'code' => 'BRK-W',
            'city' => 'Abuja',
            'state' => 'Federal Capital Territory',
            'country' => 'Nigeria',
            'address' => '15 Mike Akhigbe Way, Wuse II',
            'contact_email' => 'rsv@brickspoint.com',
            'contact_phone' => '+234 809 999 9627',
            'is_headquarters' => false,
            'is_active' => true,
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
        ]);
    }

    public function down(): void
    {
        Property::whereIn('slug', ['asokoro', 'wuse'])->delete();
    }
};
