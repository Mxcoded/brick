<?php

namespace Modules\Website\Tests;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Services\PropertyService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

abstract class WebsiteModuleTestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Property $property;
    protected Property $secondProperty;
    protected RoomType $roomType;
    protected RoomUnit $roomUnit;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $basename = class_basename($modelName);
            if (str_starts_with($modelName, 'Modules\\Frontdeskcrm\\')) {
                return 'Modules\\Frontdeskcrm\\Database\\Factories\\'.$basename.'Factory';
            }
            if (str_starts_with($modelName, 'Modules\\Website\\')) {
                return 'Modules\\Website\\Database\\Factories\\'.$basename.'Factory';
            }
            return 'Database\\Factories\\'.$basename.'Factory';
        });

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->property = Property::factory()->create([
            'domain' => 'test-asokoro-' . $this->faker->unique()->word(),
            'city' => 'Abuja',
            'is_active' => true,
        ]);

        $this->secondProperty = Property::factory()->create([
            'domain' => 'test-wuse-' . $this->faker->unique()->word(),
            'city' => 'Abuja',
            'is_active' => true,
        ]);

        $this->roomType = RoomType::factory()->create([
            'property_id' => $this->property->id,
            'price' => 20000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        $this->roomUnit = RoomUnit::factory()->create([
            'room_type_id' => $this->roomType->id,
            'property_id' => $this->property->id,
            'status' => 'available',
        ]);
    }

    protected function setPropertyContext(Property $property): void
    {
        app(PropertyService::class)->setCurrent($property);
    }

    protected function clearPropertyContext(): void
    {
        app(PropertyService::class)->clear();
    }
}
