<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Services\HousekeepingService;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HousekeepingTest extends TestCase
{
    use DatabaseTransactions;

    protected HousekeepingService $housekeepingService;

    protected User $user;

    protected RoomUnit $roomUnit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->housekeepingService = app(HousekeepingService::class);
        $this->user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo('access_frontdesk_dashboard');

        $roomType = RoomType::create([
            'name' => 'HK Test Room',
            'slug' => 'hk-test-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
        ]);

        $this->roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'HK-'.rand(100, 999),
            'floor' => '1',
            'status' => 'available',
        ]);
    }

    public function test_sets_room_housekeeping_status(): void
    {
        $room = $this->housekeepingService->setRoomStatus($this->roomUnit, 'dirty');

        $this->assertEquals('dirty', $room->housekeeping_status);
    }

    public function test_setting_clean_records_cleaned_by(): void
    {
        $room = $this->housekeepingService->setRoomStatus($this->roomUnit, 'clean', $this->user->id);

        $this->assertEquals('clean', $room->housekeeping_status);
        $this->assertNotNull($room->last_cleaned_at);
        $this->assertEquals($this->user->id, $room->last_cleaned_by);
    }

    public function test_creates_housekeeping_task(): void
    {
        $task = $this->housekeepingService->createTask($this->roomUnit, 'clean', [
            'priority' => 'high',
            'notes' => 'Deep clean required',
        ]);

        $this->assertNotNull($task);
        $this->assertEquals('clean', $task->task_type);
        $this->assertEquals('high', $task->priority);
        $this->assertEquals('Deep clean required', $task->notes);
        $this->assertEquals($this->roomUnit->id, $task->room_unit_id);
    }

    public function test_assigns_task(): void
    {
        $task = $this->housekeepingService->createTask($this->roomUnit, 'clean');
        $task = $this->housekeepingService->assignTask($task, $this->user->id);

        $this->assertEquals($this->user->id, $task->assigned_to);
        $this->assertNotNull($task->assigned_at);
    }

    public function test_completes_task_and_updates_room(): void
    {
        $task = $this->housekeepingService->createTask($this->roomUnit, 'deep_clean', [
            'hk_status' => 'dirty',
        ]);

        $task = $this->housekeepingService->completeTask($task, $this->user->id);

        $this->assertNotNull($task->completed_at);
        $this->assertEquals($this->user->id, $task->completed_by);
        $this->assertEquals('clean', $task->hk_status);

        $this->roomUnit->refresh();
        $this->assertEquals('clean', $this->roomUnit->housekeeping_status);
    }

    public function test_returns_room_status_summary(): void
    {
        $summary = $this->housekeepingService->getRoomStatusSummary();

        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('clean', $summary);
        $this->assertArrayHasKey('dirty', $summary);
        $this->assertArrayHasKey('pendingTasks', $summary);
    }

    public function test_housekeeping_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.housekeeping.index'));

        $response->assertOk();
    }
}
