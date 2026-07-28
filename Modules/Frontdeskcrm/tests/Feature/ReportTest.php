<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->user->givePermissionTo('access_frontdesk_dashboard');
        $this->actingAs($this->user);
    }

    private function makeRoomType(): RoomType
    {
        return RoomType::create([
            'name' => 'Report Room',
            'slug' => 'report-room-'.uniqid(),
            'price' => 40000.00,
            'capacity' => 2,
            'is_active' => true,
        ]);
    }

    private function makeGuest(): Guest
    {
        return Guest::create([
            'full_name' => 'Report Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'report'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);
    }

    public function test_daily_revenue_page_loads(): void
    {
        $response = $this->get(route('frontdesk.reports.daily-revenue'));
        $response->assertOk();
    }

    public function test_daily_revenue_shows_room_revenue(): void
    {
        $roomType = $this->makeRoomType();
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'REP-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);
        $guest = $this->makeGuest();

        $registration = Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'nationality' => $guest->nationality,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 40000.00,
            'check_in' => Carbon::today()->subDay(),
            'check_out' => Carbon::today()->addDay(),
            'stay_status' => 'checked_in',
            'no_of_nights' => 2,
            'total_amount' => 80000.00,
        ]);

        RegistrationCharge::create([
            'registration_id' => $registration->id,
            'charge_type' => 'room',
            'amount' => 40000.00,
            'charge_date' => Carbon::today(),
            'is_audited' => true,
        ]);

        RegistrationPayment::create([
            'registration_id' => $registration->id,
            'amount' => 40000.00,
            'payment_method' => 'cash',
            'payment_date' => Carbon::today(),
            'received_by' => $this->user->id,
        ]);

        $response = $this->get(route('frontdesk.reports.daily-revenue', ['date' => Carbon::today()->format('Y-m-d')]));
        $response->assertOk();
        $response->assertSee('40,000');
    }

    public function test_arrivals_departures_page_loads(): void
    {
        $response = $this->get(route('frontdesk.reports.arrivals-departures'));
        $response->assertOk();
    }

    public function test_occupancy_page_loads(): void
    {
        $response = $this->get(route('frontdesk.reports.occupancy'));
        $response->assertOk();
    }

    public function test_reports_index_page_loads(): void
    {
        $response = $this->get(route('frontdesk.reports.index'));
        $response->assertOk();
        $response->assertSee('Daily Revenue');
        $response->assertSee('Arrivals');
        $response->assertSee('Occupancy');
    }
}
