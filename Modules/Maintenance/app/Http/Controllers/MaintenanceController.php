<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Maintenance\Mail\MaintenanceNotification;
use Modules\Maintenance\Models\MaintenanceLog;

class MaintenanceController extends Controller
{
    /**
     * Email recipients for maintenance notifications.
     */
    protected array $notificationRecipients = [
        'it@brickspoint.com',
        'fm@brickspoint.com',
    ];

    public function index()
    {
        $logs = MaintenanceLog::latest('created_at')->get();
        return view('maintenance::index', compact('logs'));
    }

    public function create()
    {
        return view('maintenance::create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'complaint_datetime' => 'required|date',
            'nature_of_complaint' => 'required|string',
            'lodged_by' => 'required|string|max:100',
            'received_by' => 'nullable|string|max:100',
            'cost_of_fixing' => 'nullable|numeric',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

        $log = MaintenanceLog::create($validated);

        // Send email notification for new maintenance request
        $this->sendNotification($log, 'new');

        return redirect()->route('maintenance.index')->with('success', 'Log created successfully');
    }

    public function show(MaintenanceLog $maintenanceLog)
    {
        return view('maintenance::show', compact('maintenanceLog'));
    }

    public function edit(MaintenanceLog $maintenanceLog)
    {
        return view('maintenance::edit', compact('maintenanceLog'));
    }

    public function update(Request $request, MaintenanceLog $maintenanceLog)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'complaint_datetime' => 'required|date',
            'nature_of_complaint' => 'required|string',
            'lodged_by' => 'required|string|max:100',
            'received_by' => 'nullable|string|max:100',
            'cost_of_fixing' => 'nullable|numeric',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

        // Check if status is changing
        $previousStatus = $maintenanceLog->status;
        $statusChanged = $previousStatus !== $validated['status'];

        $maintenanceLog->update($validated);

        // Send email notification if status changed
        if ($statusChanged) {
            $this->sendNotification($maintenanceLog, 'status_update', $previousStatus);
        }

        return redirect()->route('maintenance.index')->with('success', 'Log updated successfully');
    }

    public function destroy(MaintenanceLog $maintenanceLog)
    {
        $maintenanceLog->delete();
        return redirect()->route('maintenance.index')->with('success', 'Log deleted successfully');
    }

    /**
     * Send maintenance notification email to GM and FM.
     */
    protected function sendNotification(MaintenanceLog $log, string $type, ?string $previousStatus = null): void
    {
        try {
            Mail::to($this->notificationRecipients)->send(
                new MaintenanceNotification($log, $type, $previousStatus)
            );

            Log::info('Maintenance notification sent', [
                'log_id' => $log->id,
                'type' => $type,
                'recipients' => $this->notificationRecipients,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send maintenance notification', [
                'log_id' => $log->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
