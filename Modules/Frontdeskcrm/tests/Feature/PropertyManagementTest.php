<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\RateCode;
use App\Models\RoomType;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;

class PropertyManagementTest extends ModuleTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createAuthenticatedUser();
    }

    public function test_can_list_properties()
    {
        Property::factory()->count(2)->create();

        $response = $this->get(route('frontdesk.properties.index'));

        $response->assertOk();
        $response->assertViewHas('properties');
    }

    public function test_can_create_property()
    {
        $response = $this->post(route('frontdesk.properties.store'), [
            'name' => 'Test Hotel',
            'slug' => 'test-hotel',
            'code' => 'TEST',
            'address' => '123 Test St',
            'city' => 'Lagos',
            'country' => 'Nigeria',
            'phone' => '08000000000',
            'email' => 'test@hotel.com',
            'is_active' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('properties', ['code' => 'TEST']);
    }

    public function test_can_clone_property_data()
    {
        $source = Property::factory()->create();
        RoomType::factory()->count(3)->create(['property_id' => $source->id]);
        ChargeType::factory()->count(2)->create(['property_id' => $source->id]);
        RateCode::factory()->count(2)->create(['property_id' => $source->id]);

        $response = $this->post(route('frontdesk.properties.store'), [
            'name' => 'Cloned Hotel',
            'slug' => 'cloned-hotel',
            'code' => 'CLONE',
            'address' => '456 Clone St',
            'city' => 'Abuja',
            'country' => 'Nigeria',
            'phone' => '08011111111',
            'email' => 'clone@hotel.com',
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

    public function test_can_switch_property()
    {
        $property = Property::factory()->create();
        $this->user->properties()->attach($property->id);

        $response = $this->post(route('frontdesk.properties.switch', $property));

        $response->assertRedirect();
        $this->assertEquals($property->id, session('current_property_id'));
    }
}
