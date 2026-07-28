<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FolioTest extends TestCase
{
    use DatabaseTransactions;

    protected FolioService $folioService;

    protected User $user;

    protected Registration $registration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->folioService = app(FolioService::class);
        $this->user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo('access_frontdesk_dashboard');
        $this->registration = $this->makeRegistration();
    }

    private function makeRegistration(): Registration
    {
        $roomType = RoomType::create([
            'name' => 'Folio Test Room',
            'slug' => 'folio-test-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'FL-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);
        $guest = Guest::create([
            'full_name' => 'Folio Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'folio'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        return Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'nationality' => $guest->nationality,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => Carbon::today()->subDays(2),
            'check_out' => Carbon::today()->addDays(3),
            'stay_status' => 'checked_in',
            'no_of_nights' => 5,
            'total_amount' => 250000,
        ]);
    }

    public function test_ensure_folio_creates_new_folio_when_none_exists(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);

        $this->assertNotNull($folio);
        $this->assertEquals('Main Folio', $folio->folio_name);
        $this->assertEquals('open', $folio->status);
        $this->assertEquals(0, $folio->balance);
        $this->assertStringStartsWith('FOL-', $folio->folio_number);
    }

    public function test_ensure_folio_returns_existing_open_folio(): void
    {
        $folio1 = $this->folioService->ensureFolio($this->registration);
        $folio2 = $this->folioService->ensureFolio($this->registration);

        $this->assertEquals($folio1->id, $folio2->id);
    }

    public function test_post_charge_adds_to_folio_balance(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);

        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Room charge test',
            'amount' => 50000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $folio->refresh();
        $this->assertEquals(50000, $folio->balance);
        $this->assertEquals(1, $folio->items()->count());
    }

    public function test_split_folio_moves_items(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);

        $item1 = $this->folioService->postCharge($folio, [
            'charge_type' => 'room', 'description' => 'Room charge',
            'amount' => 40000, 'post_date' => Carbon::today(),
        ], $this->user->id);

        $item2 = $this->folioService->postCharge($folio, [
            'charge_type' => 'breakfast', 'description' => 'Breakfast',
            'amount' => 5000, 'post_date' => Carbon::today(),
        ], $this->user->id);

        $newFolio = $this->folioService->splitFolio($folio, 'Incidentals', [$item2->id], $this->user->id);

        $folio->refresh();
        $newFolio->refresh();

        $this->assertEquals(40000, $folio->balance);
        $this->assertEquals(5000, $newFolio->balance);
        $this->assertEquals('Incidentals', $newFolio->folio_name);
        $this->assertEquals($folio->registration_id, $newFolio->registration_id);
    }

    public function test_close_folio(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $folio = $this->folioService->closeFolio($folio);

        $this->assertEquals('closed', $folio->status);
    }

    public function test_void_folio_clears_items_and_balance(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room', 'description' => 'Test',
            'amount' => 30000, 'post_date' => Carbon::today(),
        ]);

        $folio = $this->folioService->voidFolio($folio);

        $this->assertEquals('void', $folio->status);
        $this->assertEquals(0, $folio->balance);
        $this->assertEquals(0, $folio->items()->count());
    }

    public function test_folio_page_loads(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room', 'description' => 'Test charge',
            'amount' => 45000, 'post_date' => Carbon::today(),
        ], $this->user->id);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.folios.show', $folio));

        $response->assertOk();
        $response->assertSee($folio->folio_number);
        $response->assertSee('45,000');
    }
}
