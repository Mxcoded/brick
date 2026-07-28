<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Models\HousekeepingTask;
use Modules\Website\Models\RoomUnit;

class HousekeepingService
{
    public function setRoomStatus(RoomUnit $roomUnit, string $hkStatus, ?int $userId = null): RoomUnit
    {
        $data = ['housekeeping_status' => $hkStatus];

        if ($hkStatus === 'clean') {
            $data['last_cleaned_at'] = now();
            $data['last_cleaned_by'] = $userId;
        }

        $roomUnit->update($data);

        return $roomUnit->fresh();
    }

    public function createTask(RoomUnit $roomUnit, string $taskType, array $options = []): HousekeepingTask
    {
        return HousekeepingTask::create([
            'room_unit_id' => $roomUnit->id,
            'task_type' => $taskType,
            'hk_status' => $options['hk_status'] ?? 'dirty',
            'assigned_to' => $options['assigned_to'] ?? null,
            'assigned_at' => ($options['assigned_to'] ?? null) ? now() : null,
            'priority' => $options['priority'] ?? 'normal',
            'notes' => $options['notes'] ?? null,
        ]);
    }

    public function assignTask(HousekeepingTask $task, int $userId): HousekeepingTask
    {
        $task->update([
            'assigned_to' => $userId,
            'assigned_at' => now(),
        ]);

        return $task;
    }

    public function completeTask(HousekeepingTask $task, int $userId): HousekeepingTask
    {
        DB::transaction(function () use ($task, $userId) {
            $task->update([
                'completed_by' => $userId,
                'completed_at' => now(),
                'hk_status' => 'clean',
            ]);

            $this->setRoomStatus($task->roomUnit, 'clean', $userId);
        });

        return $task->fresh();
    }

    public function getDirtyRooms()
    {
        return RoomUnit::where('housekeeping_status', 'dirty')
            ->orWhereHas('housekeepingTasks', function ($q) {
                $q->whereNull('completed_at')
                    ->where('hk_status', '!=', 'clean');
            })
            ->get();
    }

    public function getPendingTasks()
    {
        return HousekeepingTask::whereNull('completed_at')
            ->with(['roomUnit', 'assignedTo'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at')
            ->get();
    }

    public function getRoomStatusSummary(): array
    {
        $total = RoomUnit::count();
        $clean = RoomUnit::where('housekeeping_status', 'clean')->count();
        $dirty = RoomUnit::where('housekeeping_status', 'dirty')->count();
        $inspected = RoomUnit::where('housekeeping_status', 'inspected')->count();
        $outOfService = RoomUnit::where('housekeeping_status', 'out_of_service')->count();
        $pendingTasks = HousekeepingTask::whereNull('completed_at')->count();

        return compact('total', 'clean', 'dirty', 'inspected', 'outOfService', 'pendingTasks');
    }
}
