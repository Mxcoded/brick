<?php

namespace Modules\Website\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\RoomType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::WEBSITE_ADMIN->value, 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'room-types.update', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::WEBSITE_ADMIN->value);
        $this->actingAs($this->admin);
    }

    /**
     * The middleware must log which specific record was edited (not just a
     * generic "update"), so staff.edit-style actions are traceable.
     */
    public function test_editing_a_record_logs_the_affected_model()
    {
        $roomType = RoomType::create([
            'name' => 'Assignment Suite',
            'slug' => 'assignment-suite',
            'price' => 20000,
            'capacity' => 2,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $response = $this->put(route('website.admin.room-types.update', $roomType->id), [
            'name' => 'Assignment Suite Deluxe',
            'price' => 25000,
            'capacity' => 3,
            'description' => 'Updated description',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'room-types.update',
            'model_type' => RoomType::class,
            'model_id' => $roomType->id,
        ]);

        $log = UserActivityLog::where('model_type', RoomType::class)
            ->where('model_id', $roomType->id)
            ->firstOrFail();

        // Description must name the affected record, not just the route name.
        $this->assertStringContainsString('Assignment Suite', $log->description);
        $this->assertStringContainsString((string) $roomType->getKey(), $log->description);
    }
}
