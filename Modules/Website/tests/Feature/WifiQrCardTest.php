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
            ->get(route('website.admin.testimonials.wifi-qr'));

        $response->assertOk();
        $response->assertSee('Review QR Cards');

        foreach (Testimonial::TYPES as $type) {
            $url = route('website.guest-feedback', ['type' => $type]);

            $this->assertStringContainsString('type='.$type, $url);
            $response->assertSee($url);
        }
    }

    public function test_admin_can_generate_cards_for_a_specific_branch(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('website.admin.testimonials.wifi-qr', ['branch' => 'Asokoro']));

        $response->assertOk();
        foreach (Testimonial::TYPES as $type) {
            $url = route('website.guest-feedback', ['type' => $type, 'branch' => 'Asokoro']);
            $this->assertStringContainsString('type='.$type, $url);
            $this->assertStringContainsString('branch=Asokoro', $url);
            $response->assertSee($url);
        }
    }

    public function test_review_qr_links_carry_no_network_data(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('website.admin.testimonials.wifi-qr'));

        $response->assertOk();
        $response->assertDontSee('ssid');
        $response->assertDontSee('apmac');
    }
}
