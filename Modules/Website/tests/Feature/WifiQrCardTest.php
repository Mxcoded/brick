<?php

namespace Modules\Website\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\Testimonial;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WifiQrCardTest extends TestCase
{
    use DatabaseTransactions;

    private function adminUser(): User
    {
        $role = Role::firstOrCreate([
            'name' => 'website_admin_test',
            'guard_name' => 'web',
        ]);

        foreach ([
            'access_website_dashboard',
            'website.testimonials.read',
        ] as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            if (! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::create([
            'name' => 'QR Admin',
            'email' => 'qr-admin-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'type' => 'staff',
            'status' => 'active',
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_generate_all_three_review_qr_cards(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('website.admin.testimonials.wifi-qr', [
                'site' => 'Asokoro',
                'ssid' => 'Brickspoint-Guest',
                'ap' => 'Lobby Guest Wi-Fi',
                'band' => '5 GHz',
                'apmac' => '64:d1:54:aa:bb:cc',
            ]));

        $response->assertOk();
        $response->assertSee('Review QR Cards');

        foreach (Testimonial::TYPES as $type) {
            $url = route('website.guest-feedback', [
                'type' => $type,
                'source' => 'qrcode',
                'site' => 'Asokoro',
                'ssid' => 'Brickspoint-Guest',
                'ap' => 'Lobby Guest Wi-Fi',
                'band' => '5 GHz',
                'apmac' => '64:d1:54:aa:bb:cc',
            ]);

            $this->assertStringContainsString('type='.$type, $url);
            $this->assertStringContainsString('source=qrcode', $url);
            $this->assertStringContainsString('ssid=Brickspoint-Guest', $url);
            $this->assertStringContainsString('ap=Lobby%20Guest%20Wi-Fi', $url);
            $this->assertStringContainsString('site=Asokoro', $url);
            $this->assertStringContainsString('band=5%20GHz', $url);
            $response->assertSee($url);
        }
    }

    public function test_admin_wifi_qr_prefills_from_most_recent_wifi_testimonial(): void
    {
        Testimonial::create([
            'guest_name' => 'Wi-Fi Guest',
            'email' => 'wifi-guest@example.com',
            'text' => 'Great stay.',
            'rating' => 5,
            'type' => 'stay',
            'location' => 'Asokoro',
            'ssid' => 'Brickspoint-Guest',
            'ap_name' => 'Lobby Guest Wi-Fi',
            'ap_mac' => '64:d1:54:aa:bb:cc',
            'wifi_meta' => ['radio_id' => '1'],
            'approved' => true,
        ]);

        $response = $this->actingAs($this->adminUser())
            ->get(route('website.admin.testimonials.wifi-qr'));

        $response->assertOk();
        $response->assertSee('Asokoro');
        $response->assertSee('Brickspoint-Guest');
        $response->assertSee('Lobby Guest Wi-Fi');
        $response->assertSee('5 GHz');
    }
}
