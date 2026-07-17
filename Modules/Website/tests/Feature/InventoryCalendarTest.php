<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\RoomInventoryBlock;
use Modules\Website\Models\RoomType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InventoryCalendarTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        $role = Role::firstOrCreate(['name' => RoleEnum::WEBSITE_ADMIN->value, 'guard_name' => 'web']);
        $permissions = ['access_website_dashboard', 'website.inventory'];
        foreach ($permissions as $name) {
            $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            if (! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->admin->assignRole(RoleEnum::WEBSITE_ADMIN->value);

        $this->actingAs($this->admin);

        $this->roomType = RoomType::create([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-suite',
            'price' => 25000,
            'capacity' => 2,
            'is_active' => true,
            'display_order' => 1,
        ]);
    }

    public function test_website_admin_can_view_inventory_calendar_page()
    {
        $response = $this->get(route('website.admin.inventory.index'));

        $response->assertStatus(200);
        $response->assertViewHas('roomTypes');
    }

    public function test_website_admin_can_apply_block()
    {
        $start = now()->addDays(10)->format('Y-m-d');
        $end = now()->addDays(12)->format('Y-m-d');

        $response = $this->postJson(route('website.admin.inventory.block'), [
            'room_type_id' => $this->roomType->id,
            'start_date' => $start,
            'end_date' => $end,
            'blocked_count' => 1,
            'block_type' => 'manual',
            'notes' => 'Test block',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $start,
            'end_date' => $end,
            'blocked_count' => 1,
            'block_type' => 'manual',
            'notes' => 'Test block',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_website_admin_can_apply_restrictions()
    {
        $start = now()->addDays(5)->format('Y-m-d');
        $end = now()->addDays(15)->format('Y-m-d');

        $response = $this->postJson(route('website.admin.inventory.restrict'), [
            'room_type_id' => $this->roomType->id,
            'start_date' => $start,
            'end_date' => $end,
            'min_stay' => 3,
            'closed_to_arrival' => true,
            'blocked_count' => 2,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'min_stay' => 3,
            'closed_to_arrival' => true,
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }

    public function test_website_admin_can_bulk_update_inventory()
    {
        $start = now()->addDays(20)->format('Y-m-d');
        $end = now()->addDays(22)->format('Y-m-d');

        $response = $this->postJson(route('website.admin.inventory.bulk'), [
            'updates' => [
                [
                    'room_type_id' => $this->roomType->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'stop_sell' => true,
                    'blocked_count' => 0,
                ],
            ],
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $start,
            'end_date' => $end,
            'stop_sell' => true,
        ]);
    }

    public function test_website_admin_can_apply_stop_sell()
    {
        $start = now()->addDays(1)->format('Y-m-d');
        $end = now()->addDays(3)->format('Y-m-d');

        $response = $this->postJson(route('website.admin.inventory.stop-sell'), [
            'room_type_id' => $this->roomType->id,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $start,
            'end_date' => $end,
            'stop_sell' => true,
        ]);
    }

    public function test_website_admin_can_open_rooms()
    {
        $block = RoomInventoryBlock::create([
            'room_type_id' => $this->roomType->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'blocked_count' => 1,
            'block_type' => 'manual',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->postJson(route('website.admin.inventory.open'), [
            'room_type_id' => $this->roomType->id,
            'start_date' => now()->addDays(4)->format('Y-m-d'),
            'end_date' => now()->addDays(8)->format('Y-m-d'),
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('room_inventory_blocks', [
            'id' => $block->id,
            'deleted_at' => null,
        ]);
    }

    public function test_opening_a_sub_range_splits_the_block_and_keeps_the_rest_blocked()
    {
        $base = now()->addDays(20);
        $blockStart = $base->copy()->format('Y-m-d');
        $blockEnd = $base->copy()->addDays(5)->format('Y-m-d'); // 6-day block

        $block = RoomInventoryBlock::create([
            'room_type_id' => $this->roomType->id,
            'start_date' => $blockStart,
            'end_date' => $blockEnd,
            'blocked_count' => 2,
            'block_type' => 'manual',
            'created_by' => $this->admin->id,
        ]);

        // Open only the middle 2 days of the 6-day block.
        $openStart = $base->copy()->addDays(2)->format('Y-m-d');
        $openEnd = $base->copy()->addDays(3)->format('Y-m-d');

        $response = $this->postJson(route('website.admin.inventory.open'), [
            'room_type_id' => $this->roomType->id,
            'start_date' => $openStart,
            'end_date' => $openEnd,
        ]);

        $response->assertJson(['success' => true]);

        // The originally blocked range is removed (split into the two ends)...
        $this->assertSoftDeleted($block);

        // ...and replaced by the two remaining segments, each still blocked.
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $blockStart,
            'end_date' => $base->copy()->addDays(1)->format('Y-m-d'),
            'blocked_count' => 2,
        ]);

        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $base->copy()->addDays(4)->format('Y-m-d'),
            'end_date' => $blockEnd,
            'blocked_count' => 2,
        ]);

        // The opened middle days are no longer blocked.
        $this->assertDatabaseMissing('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $openStart,
            'end_date' => $openEnd,
        ]);
    }

    public function test_opening_a_count_of_rooms_reduces_blocked_count_instead_of_clearing()
    {
        $base = now()->addDays(20);
        $blockStart = $base->copy()->format('Y-m-d');
        $blockEnd = $base->copy()->addDays(5)->format('Y-m-d');

        $block = RoomInventoryBlock::create([
            'room_type_id' => $this->roomType->id,
            'start_date' => $blockStart,
            'end_date' => $blockEnd,
            'blocked_count' => 4,
            'block_type' => 'manual',
            'created_by' => $this->admin->id,
        ]);

        // Open only 1 of the 4 blocked rooms for the whole range.
        $response = $this->postJson(route('website.admin.inventory.open'), [
            'room_type_id' => $this->roomType->id,
            'start_date' => $blockStart,
            'end_date' => $blockEnd,
            'open_count' => 1,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted($block);

        // The range now stays blocked, but with 1 fewer room (4 - 1 = 3).
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $blockStart,
            'end_date' => $blockEnd,
            'blocked_count' => 3,
        ]);
    }

    public function test_opening_a_count_on_a_sub_range_keeps_the_rest_fully_blocked()
    {
        $base = now()->addDays(20);
        $blockStart = $base->copy()->format('Y-m-d');
        $blockEnd = $base->copy()->addDays(5)->format('Y-m-d'); // 6-day block

        $block = RoomInventoryBlock::create([
            'room_type_id' => $this->roomType->id,
            'start_date' => $blockStart,
            'end_date' => $blockEnd,
            'blocked_count' => 4,
            'block_type' => 'manual',
            'created_by' => $this->admin->id,
        ]);

        // Open 1 room from the middle 2 days only.
        $openStart = $base->copy()->addDays(2)->format('Y-m-d');
        $openEnd = $base->copy()->addDays(3)->format('Y-m-d');

        $response = $this->postJson(route('website.admin.inventory.open'), [
            'room_type_id' => $this->roomType->id,
            'start_date' => $openStart,
            'end_date' => $openEnd,
            'open_count' => 1,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted($block);

        // Ends stay fully blocked (4).
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $blockStart,
            'end_date' => $base->copy()->addDays(1)->format('Y-m-d'),
            'blocked_count' => 4,
        ]);
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $base->copy()->addDays(4)->format('Y-m-d'),
            'end_date' => $blockEnd,
            'blocked_count' => 4,
        ]);

        // Middle stays blocked but with 1 fewer room (3).
        $this->assertDatabaseHas('room_inventory_blocks', [
            'room_type_id' => $this->roomType->id,
            'start_date' => $openStart,
            'end_date' => $openEnd,
            'blocked_count' => 3,
        ]);
    }

    public function test_website_admin_can_remove_block()
    {
        $block = RoomInventoryBlock::create([
            'room_type_id' => $this->roomType->id,
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(12)->format('Y-m-d'),
            'blocked_count' => 1,
            'block_type' => 'manual',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->deleteJson(route('website.admin.inventory.block.remove'), [
            'block_id' => $block->id,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted($block);
    }

    public function test_website_admin_cannot_block_with_invalid_data()
    {
        $response = $this->postJson(route('website.admin.inventory.block'), [
            'room_type_id' => 9999,
            'start_date' => 'invalid',
            'end_date' => 'invalid',
            'blocked_count' => -1,
            'block_type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
    }

    public function test_unauthenticated_user_cannot_access_inventory()
    {
        auth()->logout();

        $response = $this->get(route('website.admin.inventory.index'));
        $response->assertStatus(302);
    }

    public function test_user_without_permission_cannot_block()
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->actingAs($user);

        $response = $this->postJson(route('website.admin.inventory.block'), [
            'room_type_id' => $this->roomType->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'blocked_count' => 1,
            'block_type' => 'manual',
        ]);

        $response->assertStatus(403);
    }
}
