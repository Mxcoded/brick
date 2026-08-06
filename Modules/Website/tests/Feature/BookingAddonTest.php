<?php

namespace Modules\Website\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Modules\Website\Livewire\BookingSummary;
use Modules\Website\Models\Addon;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\BookingCartService;
use Modules\Website\Services\BookingDraftService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BookingAddonTest extends TestCase
{
    use DatabaseTransactions;

    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->roomType = RoomType::create([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-suite-'.uniqid(),
            'price' => 20000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        RoomUnit::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => '101',
            'floor' => 1,
            'status' => 'available',
        ]);
    }

    private function makeAddon(array $overrides = []): Addon
    {
        return Addon::create(array_merge([
            'name' => 'Airport Pickup',
            'slug' => 'airport-pickup-'.uniqid(),
            'price' => 15000,
            'is_per_night' => false,
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    private function seedCartItem(): void
    {
        session([BookingCartService::SESSION_KEY => [
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(3)->format('Y-m-d'),
            'nights' => 2,
            'addons' => [],
            'items' => [
                $this->roomType->id => [
                    'room_type_id' => $this->roomType->id,
                    'room_type_name' => $this->roomType->name,
                    'quantity' => 1,
                    'price_per_night' => 20000,
                    'base_total' => 40000,
                    'guest_fee_per_night' => 0,
                    'guest_fee_total' => 0,
                    'total_rate' => 40000,
                    'rate_code_id' => null,
                    'capacity' => 2,
                    'adults' => 1,
                    'children' => 0,
                    'image_url' => null,
                    'nights' => 2,
                    'subtotal' => 40000,
                ],
            ],
        ]]);
    }

    // ─────────────────────────────────────────────────────────────
    // Model
    // ─────────────────────────────────────────────────────────────

    public function test_addon_total_for_one_time_addon_is_charged_once(): void
    {
        $addon = $this->makeAddon(['is_per_night' => false, 'price' => 15000]);

        $this->assertSame(15000.0, $addon->totalFor(3));
        $this->assertSame(30000.0, $addon->totalFor(3, 2));
    }

    public function test_addon_total_for_per_night_addon_scales_with_nights(): void
    {
        $addon = $this->makeAddon(['is_per_night' => true, 'price' => 7500]);

        $this->assertSame(22500.0, $addon->totalFor(3));
        $this->assertSame(45000.0, $addon->totalFor(3, 2));
    }

    public function test_active_scope_only_returns_active_addons(): void
    {
        $this->makeAddon(['is_active' => true]);
        $this->makeAddon(['is_active' => false]);

        $this->assertSame(1, Addon::active()->count());
    }

    // ─────────────────────────────────────────────────────────────
    // Cart service
    // ─────────────────────────────────────────────────────────────

    public function test_add_addon_requires_room_in_cart(): void
    {
        $addon = $this->makeAddon();

        $result = app(BookingCartService::class)->addAddon($addon->id);

        $this->assertFalse($result['success']);
    }

    public function test_add_addon_rejects_inactive_addon(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon(['is_active' => false]);

        $result = app(BookingCartService::class)->addAddon($addon->id);

        $this->assertFalse($result['success']);
    }

    public function test_add_addon_puts_addon_in_cart_summary(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon(['is_per_night' => false, 'price' => 15000]);

        $result = app(BookingCartService::class)->addAddon($addon->id);

        $this->assertTrue($result['success']);
        $this->assertSame($addon->id, $result['cart']['addons'][0]['addon_id']);
        $this->assertSame(15000.0, $result['cart']['addon_total']);
        $this->assertSame(55000.0, $result['cart']['total']);
    }

    public function test_per_night_addon_scales_in_cart_total(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon(['is_per_night' => true, 'price' => 7500]);

        $cart = app(BookingCartService::class)->getCartSummary();

        app(BookingCartService::class)->addAddon($addon->id);
        $cart = app(BookingCartService::class)->getCartSummary();

        // 2 nights × 7500
        $this->assertSame(15000.0, $cart['addon_total']);
        $this->assertSame(55000.0, $cart['total']);
    }

    public function test_remove_addon_removes_it_from_cart(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon();

        $service = app(BookingCartService::class);
        $service->addAddon($addon->id);
        $result = $service->removeAddon($addon->id);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['cart']['addons']);
        $this->assertSame(0.0, $result['cart']['addon_total']);
    }

    public function test_get_addon_ids_returns_selected_ids(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon();

        $service = app(BookingCartService::class);
        $service->addAddon($addon->id);

        $this->assertSame([$addon->id], $service->getAddonIds());
    }

    public function test_clear_drops_addons(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon();

        $service = app(BookingCartService::class);
        $service->addAddon($addon->id);
        $service->clear();

        $this->assertEmpty($service->getAddonIds());
    }

    // ─────────────────────────────────────────────────────────────
    // HTTP cart add-on endpoints
    // ─────────────────────────────────────────────────────────────

    public function test_cart_addon_endpoint_adds_addon(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon(['price' => 10000]);

        $response = $this->postJson(route('website.cart.addon'), ['addon_id' => $addon->id]);

        $response->assertOk()
            ->assertJson(['success' => true]);
        $this->assertSame(10000.0, (float) $response->json('cart.addon_total'));
    }

    public function test_cart_addon_endpoint_rejects_inactive_addon(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon(['is_active' => false]);

        $this->postJson(route('website.cart.addon'), ['addon_id' => $addon->id])
            ->assertJson(['success' => false]);
    }

    public function test_cart_addon_remove_endpoint_removes_addon(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon();

        app(BookingCartService::class)->addAddon($addon->id);

        $this->deleteJson(route('website.cart.addon-remove', $addon->id))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('cart.addon_total', 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Booking page rendering
    // ─────────────────────────────────────────────────────────────

    public function test_booking_page_renders_active_add_ons(): void
    {
        $addon = $this->makeAddon(['name' => 'Breakfast Box']);

        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        $response->assertSee('Enhance');
        $response->assertSee('Breakfast Box');
        $response->assertSee('addon-checkbox');
        $response->assertSee('data-addon-id="'.$addon->id.'"', false);
    }

    public function test_booking_page_hides_inactive_add_ons(): void
    {
        $this->makeAddon(['name' => 'Hidden Extra', 'is_active' => false]);

        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        $response->assertDontSee('Hidden Extra');
    }

    public function test_booking_page_pre_checks_draft_addons(): void
    {
        $addon = $this->makeAddon();

        app(BookingDraftService::class)->update(['addons' => [$addon->id]]);

        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        $response->assertSee('addon_'.$addon->id.'" name="addons[]" value="'.$addon->id.'"', false);
        $response->assertSee('checked', false);
    }

    public function test_cart_flow_booking_page_marks_cart_addons_selected(): void
    {
        $this->seedCartItem();
        $addon = $this->makeAddon();
        app(BookingCartService::class)->addAddon($addon->id);

        $response = $this->get(route('website.booking'));

        $response->assertOk();
        $response->assertSee('data-cart-addon="1"', false);
        $response->assertSee('data-addon-id="'.$addon->id.'"', false);
        $response->assertSee('checked', false);
    }

    // ─────────────────────────────────────────────────────────────
    // BookingSummary Livewire bridge (non-cart pricing path)
    // ─────────────────────────────────────────────────────────────

    public function test_booking_summary_dispatches_addon_total(): void
    {
        $addon = $this->makeAddon(['is_per_night' => false, 'price' => 5000]);
        $checkIn = now()->addDays(1)->format('Y-m-d');
        $checkOut = now()->addDays(3)->format('Y-m-d');

        Livewire::test(BookingSummary::class, [
            'roomTypeId' => $this->roomType->id,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'adults' => 1,
            'children' => 0,
            'addons' => [$addon->id],
        ])
            ->assertDispatched('booking-summary-updated', function ($name, $params) {
                return (float) $params['addonTotal'] === 5000.0
                    && (float) $params['total'] === 45000.0; // 2 nights × 20000 + 5000
            });
    }

    // ─────────────────────────────────────────────────────────────
    // Booking store() persistence
    // ─────────────────────────────────────────────────────────────

    public function test_store_persists_addons_and_increases_total_single_room()
    {
        Mail::fake();
        config(['mail.reservations_email' => 'rsv@brickspoint.com']);

        $perNight = $this->makeAddon(['is_per_night' => true, 'price' => 5000]);
        $oneTime = $this->makeAddon(['is_per_night' => false, 'price' => 10000]);

        $checkIn = now()->addDays(5)->format('Y-m-d');
        $checkOut = now()->addDays(8)->format('Y-m-d');

        $this->post(route('website.booking.store'), [
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 1,
            'children' => 0,
            'payment_method' => 'pay_on_arrival',
            'room_type_id' => $this->roomType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'addons' => [$perNight->id, $oneTime->id],
        ]);

        $booking = Booking::where('guest_email', 'guest@example.com')->first();
        $this->assertNotNull($booking);

        $this->assertSame(2, $booking->addons()->count());

        $perNightPivot = $booking->addons()->where('addon_id', $perNight->id)->first()->pivot;
        $this->assertSame(15000.0, (float) $perNightPivot->total); // 3 nights × 5000
        $this->assertTrue((bool) $perNightPivot->is_per_night);

        $oneTimePivot = $booking->addons()->where('addon_id', $oneTime->id)->first()->pivot;
        $this->assertSame(10000.0, (float) $oneTimePivot->total);

        $expectedTotal = (20000 * 3) + 15000 + 10000;
        $this->assertSame((float) $expectedTotal, (float) $booking->total_amount);
    }

    public function test_store_persists_cart_addons_multi_room()
    {
        Mail::fake();
        config(['mail.reservations_email' => 'rsv@brickspoint.com']);

        $addon = $this->makeAddon(['price' => 8000]);

        $this->seedCartItem();
        app(BookingCartService::class)->addAddon($addon->id);

        $this->post(route('website.booking.store'), [
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest2@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 1,
            'children' => 0,
            'payment_method' => 'pay_on_arrival',
        ]);

        $booking = Booking::where('guest_email', 'guest2@example.com')->first();
        $this->assertNotNull($booking);

        $this->assertSame(1, $booking->addons()->count());
        $pivot = $booking->addons()->first()->pivot;
        $this->assertSame('Airport Pickup', $pivot->name);
        $this->assertSame(8000.0, (float) $pivot->total);

        $expectedTotal = 40000 + 8000; // room subtotal + addon
        $this->assertSame((float) $expectedTotal, (float) $booking->total_amount);
    }

    public function test_store_ignores_inactive_addons(): void
    {
        Mail::fake();
        config(['mail.reservations_email' => 'rsv@brickspoint.com']);

        $inactive = $this->makeAddon(['is_active' => false, 'price' => 9000]);

        $this->post(route('website.booking.store'), [
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest3@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 1,
            'children' => 0,
            'payment_method' => 'pay_on_arrival',
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
            'addons' => [$inactive->id],
        ]);

        $booking = Booking::where('guest_email', 'guest3@example.com')->first();
        $this->assertNotNull($booking);
        $this->assertSame(0, $booking->addons()->count());
        $this->assertSame(40000.0, (float) $booking->total_amount);
    }

    // ─────────────────────────────────────────────────────────────
    // Confirmation page
    // ─────────────────────────────────────────────────────────────

    public function test_confirmation_page_lists_add_ons()
    {
        Mail::fake();
        config(['mail.reservations_email' => 'rsv@brickspoint.com']);

        $addon = $this->makeAddon(['name' => 'Spa Session', 'price' => 12000]);

        $this->post(route('website.booking.store'), [
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 1,
            'children' => 0,
            'payment_method' => 'pay_on_arrival',
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
            'addons' => [$addon->id],
        ]);

        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $response = $this->get(route('website.booking.confirmation', $booking->booking_reference));

        $response->assertOk();
        $response->assertSee('Add-ons');
        $response->assertSee('Spa Session');
        $response->assertSee('₦12,000.00');
    }

    public function test_confirmation_page_breakdown_separates_room_subtotal_from_add_ons(): void
    {
        Mail::fake();
        config(['mail.reservations_email' => 'rsv@brickspoint.com']);

        $addon = $this->makeAddon(['name' => 'Spa Session', 'price' => 12000]);

        $this->post(route('website.booking.store'), [
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 1,
            'children' => 0,
            'payment_method' => 'pay_on_arrival',
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
            'addons' => [$addon->id],
        ]);

        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $response = $this->get(route('website.booking.confirmation', $booking->booking_reference));

        $response->assertOk();
        // Room line shows accommodation only (2 nights × ₦20,000).
        $response->assertSee('₦40,000.00');
        // Add-ons are listed separately, not folded into the room line.
        $response->assertSee('₦12,000.00');
        // Total Due includes both.
        $response->assertSee('₦52,000.00');
        // Pay-on-arrival bookings get no pay button.
        $response->assertDontSee('Complete Payment');
    }

    public function test_confirmation_page_breakdown_separates_room_subtotal_for_grouped_booking(): void
    {
        Mail::fake();
        config(['mail.reservations_email' => 'rsv@brickspoint.com']);

        $addon = $this->makeAddon(['name' => 'Airport Pickup', 'price' => 8000]);

        session([BookingCartService::SESSION_KEY => [
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(3)->format('Y-m-d'),
            'nights' => 2,
            'addons' => [
                $addon->id => [
                    'addon_id' => $addon->id,
                    'name' => $addon->name,
                    'price' => 8000.0,
                    'is_per_night' => false,
                    'quantity' => 1,
                ],
            ],
            'items' => [
                $this->roomType->id => [
                    'room_type_id' => $this->roomType->id,
                    'room_type_name' => $this->roomType->name,
                    'quantity' => 2,
                    'price_per_night' => 20000,
                    'base_total' => 40000,
                    'guest_fee_per_night' => 0,
                    'guest_fee_total' => 0,
                    'total_rate' => 40000,
                    'rate_code_id' => null,
                    'capacity' => 2,
                    'adults' => 1,
                    'children' => 0,
                    'image_url' => null,
                    'nights' => 2,
                    'subtotal' => 80000,
                ],
            ],
        ]]);

        $this->post(route('website.booking.store'), [
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 1,
            'children' => 0,
            'payment_method' => 'pay_on_arrival',
        ]);

        $primary = Booking::where('guest_email', 'guest@example.com')->orderBy('id')->first();

        $response = $this->get(route('website.booking.confirmation', $primary->booking_reference));

        $response->assertOk();
        // Room line: 2 rooms × ₦40,000 accommodation only (add-on excluded).
        $response->assertSee('₦80,000.00');
        // Extras listed separately.
        $response->assertSee('₦8,000.00');
        // Total Due includes add-ons.
        $response->assertSee('₦88,000.00');
    }

    // ─────────────────────────────────────────────────────────────
    // Draft persistence
    // ─────────────────────────────────────────────────────────────

    public function test_draft_endpoint_persists_addons(): void
    {
        $addon = $this->makeAddon();

        $this->postJson(route('website.booking.draft'), [
            'addons' => [$addon->id],
        ])->assertJson(['saved' => true]);

        $this->assertSame([$addon->id], app(BookingDraftService::class)->getValue('addons'));
    }

    public function test_draft_endpoint_whitelists_only_active_addons(): void
    {
        $active = $this->makeAddon(['is_active' => true]);
        $inactive = $this->makeAddon(['is_active' => false]);

        $this->postJson(route('website.booking.draft'), [
            'addons' => [$active->id, $inactive->id, 999999],
        ])->assertJson(['saved' => true]);

        $this->assertSame([$active->id], app(BookingDraftService::class)->getValue('addons'));
    }

    // ─────────────────────────────────────────────────────────────
    // Admin CRUD
    // ─────────────────────────────────────────────────────────────

    private function adminUser(): User
    {
        $role = Role::firstOrCreate([
            'name' => 'website_admin',
            'guard_name' => 'web',
        ]);

        foreach ([
            'access_website_dashboard',
            'website.addons.create',
            'website.addons.read',
            'website.addons.update',
            'website.addons.delete',
        ] as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            if (! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'type' => 'staff',
            'status' => 'active',
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_view_addons_index(): void
    {
        $addon = $this->makeAddon(['name' => 'Airport Pickup']);

        $response = $this->actingAs($this->adminUser())
            ->get(route('website.admin.addons.index'));

        $response->assertOk();
        $response->assertSee('Manage Add-ons');
        $response->assertSee('Airport Pickup');
    }

    public function test_admin_can_create_addon(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->post(route('website.admin.addons.store'), [
                'name' => 'Breakfast',
                'slug' => 'breakfast',
                'price' => 5000,
                'is_per_night' => 1,
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('website.admin.addons.index'));
        $this->assertDatabaseHas('addons', ['slug' => 'breakfast', 'price' => 5000]);
    }

    public function test_admin_can_update_addon(): void
    {
        $addon = $this->makeAddon(['price' => 15000]);

        $response = $this->actingAs($this->adminUser())
            ->put(route('website.admin.addons.update', $addon), [
                'name' => 'Airport Pickup (Updated)',
                'slug' => $addon->slug,
                'price' => 20000,
                'is_per_night' => 0,
                'is_active' => 0,
            ]);

        $response->assertRedirect(route('website.admin.addons.index'));

        $addon->refresh();
        $this->assertSame('Airport Pickup (Updated)', $addon->name);
        $this->assertSame(20000.0, (float) $addon->price);
        $this->assertFalse((bool) $addon->is_active);
    }

    public function test_admin_can_delete_addon(): void
    {
        $addon = $this->makeAddon();

        $response = $this->actingAs($this->adminUser())
            ->delete(route('website.admin.addons.destroy', $addon));

        $response->assertRedirect(route('website.admin.addons.index'));
        $this->assertSoftDeleted('addons', ['id' => $addon->id]);
    }

    public function test_addon_admin_routes_deny_guests(): void
    {
        $addon = $this->makeAddon();

        $this->get(route('website.admin.addons.index'))->assertRedirect(route('login'));
        $this->post(route('website.admin.addons.store'), ['name' => 'X', 'slug' => 'x', 'price' => 1])->assertRedirect(route('login'));
        $this->put(route('website.admin.addons.update', $addon), ['name' => 'X', 'slug' => 'x', 'price' => 1])->assertRedirect(route('login'));
        $this->delete(route('website.admin.addons.destroy', $addon))->assertRedirect(route('login'));
    }
}
