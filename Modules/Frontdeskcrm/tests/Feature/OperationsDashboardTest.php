<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OperationsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo($permission);
    }

    public function test_dashboard_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('frontdeskcrm::operations.dashboard');
    }

    public function test_dashboard_shows_kpi_cards(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('In-House');
        $response->assertSee('Arrivals');
        $response->assertSee('Due Out');
        $response->assertSee('Available');
        $response->assertSee('Occupancy');
    }

    public function test_dashboard_shows_arrivals_list(): void
    {
        $roomType = RoomType::create([
            'name' => 'Deluxe',
            'slug' => 'deluxe-ops-'.uniqid(),
            'price' => 25000,
            'capacity' => 2,
        ]);

        Registration::create([
            'full_name' => 'Alice Arriver',
            'email' => 'alice@test.com',
            'contact_number' => '+2348012345678',
            'room_type_id' => $roomType->id,
            'check_in' => today(),
            'check_out' => today()->addDays(2),
            'no_of_nights' => 2,
            'room_rate' => 25000,
            'stay_status' => 'reserved',
            'finalized_by_agent_id' => $this->user->id,
            'currency' => 'NGN',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Alice Arriver');
    }

    public function test_dashboard_shows_due_out_list(): void
    {
        $roomType = RoomType::create([
            'name' => 'Standard',
            'slug' => 'standard-ops-'.uniqid(),
            'price' => 15000,
            'capacity' => 2,
        ]);

        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => '301',
            'status' => 'occupied',
        ]);

        $registration = Registration::create([
            'full_name' => 'Bob Departing',
            'email' => 'bob@test.com',
            'contact_number' => '+2348012345679',
            'room_allocation' => '301',
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'check_in' => today()->subDay(),
            'check_out' => today(),
            'no_of_nights' => 1,
            'room_rate' => 15000,
            'stay_status' => 'checked_in',
            'finalized_by_agent_id' => $this->user->id,
            'currency' => 'NGN',
        ]);

        Folio::create([
            'registration_id' => $registration->id,
            'folio_number' => Folio::generateFolioNumber(),
            'folio_name' => 'Main Folio',
            'status' => 'open',
            'balance' => 15000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Bob Departing');
        $response->assertSee('301');
    }

    public function test_dashboard_route_is_defined(): void
    {
        $this->assertNotEmpty(route('frontdesk.dashboard'));
    }

    public function test_dashboard_counts_in_house_guests(): void
    {
        $roomType = RoomType::create([
            'name' => 'Suite',
            'slug' => 'suite-ops-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
        ]);

        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => '501',
            'status' => 'occupied',
        ]);

        Registration::create([
            'full_name' => 'In House Guest',
            'contact_number' => '+2348012345680',
            'room_allocation' => '501',
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'check_in' => today()->subDays(2),
            'check_out' => today()->addDays(2),
            'no_of_nights' => 4,
            'room_rate' => 50000,
            'stay_status' => 'checked_in',
            'finalized_by_agent_id' => $this->user->id,
            'currency' => 'NGN',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('inHouseCount', 1);
    }

    public function test_dashboard_unauthenticated_redirects(): void
    {
        $response = $this->get(route('frontdesk.dashboard'));
        $response->assertRedirect();
    }
}
