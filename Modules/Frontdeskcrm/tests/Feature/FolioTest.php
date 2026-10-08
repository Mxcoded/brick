<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource\Pages\ListFolios;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource\Pages\ViewFolio;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource\RelationManagers\FolioItemsRelationManager;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
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
        Permission::firstOrCreate(['name' => 'folios.read', 'guard_name' => 'web']);
        $this->user->givePermissionTo('folios.read');
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

    public function test_total_charges_and_total_payments_accessors(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room', 'description' => 'Room charge',
            'amount' => 50000, 'tax_code' => 'VAT', 'tax_rate' => 7.5,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $folio->refresh();

        $this->assertEqualsWithDelta(50000.0 + 3750.0, (float) $folio->total_charges, 0.01);

        RegistrationPayment::create([
            'registration_id' => $this->registration->id,
            'payment_date' => Carbon::today(),
            'amount' => 25000,
            'payment_method' => 'cash',
        ]);

        $this->assertEquals(25000.0, (float) $folio->fresh()->total_payments);
    }

    public function test_filament_view_page_renders_folio_and_items(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Test charge',
            'amount' => 45000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        $response = $this->actingAs($this->user)
            ->get(ViewFolio::getUrl(['record' => $folio]));

        $response->assertSuccessful();
        $response->assertSee($folio->folio_number);
        $response->assertSee($folio->registration->full_name);
    }

    public function test_folio_items_relation_manager_is_registered(): void
    {
        $this->assertContains(
            FolioItemsRelationManager::class,
            FolioResource::getRelations(),
        );
    }

    public function test_folio_items_relation_manager_lists_items(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room',
            'description' => 'Test charge',
            'amount' => 45000,
            'post_date' => Carbon::today(),
        ], $this->user->id);

        Livewire::actingAs($this->user)
            ->test(
                FolioItemsRelationManager::class,
                [
                    'ownerRecord' => $folio,
                    'pageClass' => ViewFolio::class,
                ],
            )
            ->assertSuccessful()
            ->assertSee('Test charge')
            ->assertSee('Room');
    }

    public function test_filament_list_page_renders_folio_columns(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->postCharge($folio, [
            'charge_type' => 'room', 'description' => 'Test charge',
            'amount' => 45000, 'post_date' => Carbon::today(),
        ], $this->user->id);

        $response = $this->actingAs($this->user)
            ->get(ListFolios::getUrl());

        $response->assertSuccessful();
        $response->assertSee($folio->folio_number);
        $response->assertSee($this->registration->guest->full_name);
    }

    public function test_filament_view_page_can_post_a_charge_to_the_folio(): void
    {
        Permission::firstOrCreate(['name' => 'folios.post_charge', 'guard_name' => 'web']);
        $this->user->givePermissionTo('folios.post_charge');

        $folio = $this->folioService->ensureFolio($this->registration);

        Livewire::actingAs($this->user)
            ->test(ViewFolio::class, ['record' => $folio->getRouteKey()])
            ->assertSuccessful()
            ->assertActionVisible('postCharge')
            ->callAction('postCharge', [
                'charge_type' => 'laundry',
                'description' => 'Express laundry service',
                'amount' => 5000,
                'tax_code' => null,
                'tax_rate' => 0,
                'post_date' => Carbon::today()->toDateString(),
            ])
            ->assertNotified();

        $folio->refresh();

        $this->assertEqualsWithDelta(5000.0, (float) $folio->balance, 0.01);
        $this->assertSame(1, $folio->items()->count());
        $this->assertSame('laundry', $folio->items()->first()->charge_type);
        $this->assertSame($this->user->id, $folio->items()->first()->posted_by);
    }

    public function test_post_charge_action_hidden_without_folio_post_charge_permission(): void
    {
        $folio = $this->folioService->ensureFolio($this->registration);

        Livewire::actingAs($this->user)
            ->test(ViewFolio::class, ['record' => $folio->getRouteKey()])
            ->assertActionHidden('postCharge');
    }

    public function test_post_charge_action_hidden_on_closed_folio(): void
    {
        Permission::firstOrCreate(['name' => 'folios.post_charge', 'guard_name' => 'web']);
        $this->user->givePermissionTo('folios.post_charge');

        $folio = $this->folioService->ensureFolio($this->registration);
        $this->folioService->closeFolio($folio);

        Livewire::actingAs($this->user)
            ->test(ViewFolio::class, ['record' => $folio->getRouteKey()])
            ->assertActionHidden('postCharge');
    }

    public function test_filament_list_page_rejects_users_without_folio_permission(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->get(ListFolios::getUrl())
            ->assertRedirect();
    }
}
