<?php

namespace Modules\Website\Tests\Feature;

use App\Http\Middleware\DetectWebsiteProperty;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Models\Scopes\PropertyScope;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Modules\Website\Tests\WebsiteModuleTestCase;

class SubdomainContextTest extends WebsiteModuleTestCase
{
    private PropertyService $propertyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->propertyService = app(PropertyService::class);
        $this->clearPropertyContext();
    }

    public function test_main_domain_returns_null_property(): void
    {
        $response = $this->get(route('website.home'));

        $response->assertOk();
        $this->assertNull($this->propertyService->id());
        $this->assertNull($this->propertyService->current());
    }

    public function test_subdomain_sets_property_context(): void
    {
        $request = Request::create('http://'.$this->property->domain.'.brickspoint.com/website');
        $middleware = app(DetectWebsiteProperty::class);

        $middleware->handle($request, function ($req) {
            return response('ok');
        });

        $this->assertEquals($this->property->id, $this->propertyService->id());
        $this->assertEquals($this->property->name, $this->propertyService->current()->name);
    }

    public function test_unknown_subdomain_clears_context(): void
    {
        $this->setPropertyContext($this->property);
        $this->assertNotNull($this->propertyService->id());

        $request = Request::create('http://nonexistent.brickspoint.com/website');
        $middleware = app(DetectWebsiteProperty::class);

        $middleware->handle($request, function ($req) {
            return response('ok');
        });

        $this->assertNull($this->propertyService->id());
    }

    public function test_property_scope_filters_by_current_context(): void
    {
        RoomType::factory()->create([
            'property_id' => $this->secondProperty->id,
            'is_active' => true,
        ]);

        $this->setPropertyContext($this->property);
        $rooms = RoomType::where('is_active', true)->get();

        $this->assertCount(1, $rooms);
        $this->assertEquals($this->property->id, $rooms->first()->property_id);
    }

    public function test_no_context_shows_all_rooms(): void
    {
        RoomType::factory()->create([
            'property_id' => $this->secondProperty->id,
            'is_active' => true,
        ]);

        $this->clearPropertyContext();
        $rooms = RoomType::where('is_active', true)->withoutGlobalScope(PropertyScope::class)->get();

        $this->assertCount(2, $rooms);
    }

    public function test_city_filter_on_availability_api(): void
    {
        $otherCity = Property::factory()->create([
            'city' => 'Lagos',
            'domain' => 'test-lagos-'.$this->faker->unique()->word(),
            'is_active' => true,
        ]);
        $otherRoom = RoomType::factory()->create([
            'property_id' => $otherCity->id,
            'price' => 30000,
            'is_active' => true,
        ]);
        RoomUnit::factory()->create([
            'room_type_id' => $otherRoom->id,
            'property_id' => $otherCity->id,
            'status' => 'available',
        ]);

        $checkIn = now()->addDays(5)->format('Y-m-d');
        $checkOut = now()->addDays(7)->format('Y-m-d');

        $response = $this->getJson(route('website.api.room-availability', [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'city' => 'Abuja',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'room_types');
        $this->assertEquals($this->roomType->id, $response->json('room_types.0.id'));
    }

    public function test_city_filter_excludes_other_cities(): void
    {
        $otherCity = Property::factory()->create([
            'city' => 'Lagos',
            'domain' => 'test-lagos-'.$this->faker->unique()->word(),
            'is_active' => true,
        ]);
        $otherRoom = RoomType::factory()->create([
            'property_id' => $otherCity->id,
            'price' => 30000,
            'is_active' => true,
        ]);
        RoomUnit::factory()->create([
            'room_type_id' => $otherRoom->id,
            'property_id' => $otherCity->id,
            'status' => 'available',
        ]);

        $checkIn = now()->addDays(5)->format('Y-m-d');
        $checkOut = now()->addDays(7)->format('Y-m-d');

        $response = $this->getJson(route('website.api.room-availability', [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'city' => 'Lagos',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'room_types');
        $this->assertNotEquals($this->roomType->id, $response->json('room_types.0.id'));
    }

    public function test_empty_city_returns_all_rooms(): void
    {
        $otherCity = Property::factory()->create([
            'city' => 'Lagos',
            'domain' => 'test-lagos-'.$this->faker->unique()->word(),
            'is_active' => true,
        ]);
        $otherRoom = RoomType::factory()->create([
            'property_id' => $otherCity->id,
            'price' => 30000,
            'is_active' => true,
        ]);
        RoomUnit::factory()->create([
            'room_type_id' => $otherRoom->id,
            'property_id' => $otherCity->id,
            'status' => 'available',
        ]);

        $checkIn = now()->addDays(5)->format('Y-m-d');
        $checkOut = now()->addDays(7)->format('Y-m-d');

        $response = $this->getJson(route('website.api.room-availability', [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]));

        $response->assertOk();
        $this->assertCount(2, $response->json('room_types'));
    }
}
