<?php

namespace Modules\Admin\Tests\Feature;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\RateCode;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'access_admin_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        $perm2 = Permission::firstOrCreate(['name' => 'manage_settings', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm2)) {
            $role->givePermissionTo($perm2);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);
    }

    public function test_can_list_properties(): void
    {
        Property::factory()->count(2)->create();

        $response = $this->get(route('admin.properties.index'));

        $response->assertOk();
        $response->assertViewHas('properties');
    }

    public function test_can_create_property(): void
    {
        $response = $this->post(route('admin.properties.store'), [
            'name' => 'Test Hotel',
            'slug' => 'test-hotel',
            'code' => 'TEST',
            'address' => '123 Test St',
            'city' => 'Lagos',
            'country' => 'Nigeria',
            'contact_phone' => '08000000000',
            'contact_email' => 'test@hotel.com',
            'is_active' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('properties', ['code' => 'TEST']);
    }

    public function test_can_clone_property_data(): void
    {
        $source = Property::factory()->create();
        RoomType::factory()->count(3)->create(['property_id' => $source->id]);
        ChargeType::factory()->count(2)->create(['property_id' => $source->id]);
        RateCode::factory()->count(2)->create(['property_id' => $source->id]);

        $response = $this->post(route('admin.properties.store'), [
            'name' => 'Cloned Hotel',
            'slug' => 'cloned-hotel',
            'code' => 'CLONE',
            'address' => '456 Clone St',
            'city' => 'Abuja',
            'country' => 'Nigeria',
            'contact_phone' => '08011111111',
            'contact_email' => 'clone@hotel.com',
            'is_active' => true,
            'clone_from' => $source->id,
        ]);

        $response->assertSessionHas('success');

        $target = Property::where('code', 'CLONE')->first();
        $this->assertNotNull($target);

        $this->assertEquals(3, RoomType::where('property_id', $target->id)->count());
        $this->assertEquals(2, ChargeType::where('property_id', $target->id)->count());
        $this->assertEquals(2, RateCode::where('property_id', $target->id)->count());
    }

    public function test_can_switch_property(): void
    {
        $property = Property::factory()->create();
        $this->admin->properties()->attach($property->id);

        $response = $this->post(route('admin.properties.switch', $property));

        $response->assertRedirect();
        $this->assertEquals($property->id, session('current_property_id'));
    }
}
