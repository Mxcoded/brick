<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontdeskcrm\Models\HousekeepingTask;
use Modules\Frontdeskcrm\Services\HousekeepingService;
use Modules\Website\Models\RoomUnit;

class HousekeepingController extends Controller
{
    protected HousekeepingService $housekeepingService;

    public function __construct(HousekeepingService $housekeepingService)
    {
        $this->housekeepingService = $housekeepingService;
    }

    public function index()
    {
        $summary = $this->housekeepingService->getRoomStatusSummary();
        $rooms = RoomUnit::with(['roomType', 'currentOccupant'])->orderBy('floor')->orderBy('room_number')->get();
        $pendingTasks = $this->housekeepingService->getPendingTasks();

        return view('frontdeskcrm::housekeeping.index', compact('summary', 'rooms', 'pendingTasks'));
    }

    public function updateStatus(Request $request, RoomUnit $roomUnit)
    {
        $validated = $request->validate([
            'housekeeping_status' => 'required|in:clean,dirty,inspected,out_of_service',
        ]);

        $this->housekeepingService->setRoomStatus($roomUnit, $validated['housekeeping_status'], auth()->id());

        return redirect()->route('frontdesk.housekeeping.index')
            ->with('success', "Room {$roomUnit->room_number} status updated to {$validated['housekeeping_status']}.");
    }

    public function createTask(Request $request, RoomUnit $roomUnit)
    {
        $validated = $request->validate([
            'task_type' => 'required|in:clean,inspect,maintenance,deep_clean',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->housekeepingService->createTask($roomUnit, $validated['task_type'], $validated);

        return redirect()->route('frontdesk.housekeeping.index')
            ->with('success', 'Housekeeping task created.');
    }

    public function assignTask(Request $request, HousekeepingTask $task)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $this->housekeepingService->assignTask($task, $validated['assigned_to']);

        return redirect()->route('frontdesk.housekeeping.index')
            ->with('success', 'Task assigned.');
    }

    public function completeTask(HousekeepingTask $task)
    {
        $this->housekeepingService->completeTask($task, auth()->id());

        return redirect()->route('frontdesk.housekeeping.index')
            ->with('success', 'Task completed.');
    }

    public function tasks()
    {
        $tasks = HousekeepingTask::with(['roomUnit', 'roomUnit.roomType', 'assignedTo', 'completedBy'])
            ->orderByRaw('FIELD(priority, "urgent", "high", "normal", "low")')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('frontdeskcrm::housekeeping.tasks', compact('tasks'));
    }
}
