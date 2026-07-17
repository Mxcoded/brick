<?php

namespace Modules\Maintenance\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Maintenance\Mail\MaintenanceNotification;
use Modules\Maintenance\Models\MaintenanceLog;
use Modules\Maintenance\Models\MaintenanceReading;

class MaintenanceController extends Controller
{
    public function dashboard()
    {
        $totalLogs = MaintenanceLog::count();
        $openLogs = MaintenanceLog::open()->count();
        $completedLogs = MaintenanceLog::completed()->count();
        $cancelledLogs = MaintenanceLog::cancelled()->count();
        $thisMonth = MaintenanceLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        $departmentStats = MaintenanceLog::selectRaw('department, count(*) as count')
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        $statusStats = MaintenanceLog::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        $recentLogs = MaintenanceLog::latest()->take(10)->get();

        $avgCompletionDays = MaintenanceLog::whereNotNull('completion_date')
            ->selectRaw('AVG(DATEDIFF(completion_date, DATE(complaint_datetime))) as avg_days')
            ->value('avg_days');

        // Daily Readings
        $todayReadings = MaintenanceReading::onDate(today())->get()->groupBy(fn ($r) => $r->reading_type.'.'.$r->category);
        $todayGen = MaintenanceReading::onDate(today())->byType('generator')->get();
        $todayDiesel = MaintenanceReading::onDate(today())->byType('diesel_reservoir')->first();
        $todayWater = MaintenanceReading::onDate(today())->byType('water_tank')->first();
        $todayColdRoom = MaintenanceReading::onDate(today())->byType('cold_room')->get();
        $recentReadings = MaintenanceReading::with('recorder')->latest('reading_date')->take(5)->get();

        $readingsThisWeek = MaintenanceReading::where('reading_date', '>=', now()->subDays(7))->count();
        $lastReadingDate = MaintenanceReading::max('reading_date');

        return view('maintenance::dashboard', compact(
            'totalLogs', 'openLogs', 'completedLogs', 'cancelledLogs', 'thisMonth',
            'departmentStats', 'statusStats', 'recentLogs', 'avgCompletionDays',
            'todayGen', 'todayDiesel', 'todayWater', 'todayColdRoom',
            'recentReadings', 'readingsThisWeek', 'lastReadingDate'
        ));
    }

    public function report(Request $request)
    {
        $logs = $this->filteredLogsQuery($request)
            ->latest('complaint_datetime')
            ->paginate(25)
            ->withQueryString();

        // Each summary metric must run against a fresh copy of the filtered
        // query, otherwise the scope constraints accumulate and produce
        // contradictory conditions (e.g. status IN (new, in_progress) AND
        // status = completed), returning 0.
        $summary = [
            'total' => $logs->total(),
            'open' => $this->filteredLogsQuery($request)->open()->count(),
            'completed' => $this->filteredLogsQuery($request)->completed()->count(),
            'totalCost' => $this->filteredLogsQuery($request)->whereNotNull('cost_of_fixing')->sum('cost_of_fixing'),
        ];

        return view('maintenance::report', compact('logs', 'summary'));
    }

    public function exportReport(Request $request)
    {
        $logs = $this->filteredLogsQuery($request)->latest('complaint_datetime')->get();

        $pdf = Pdf::loadView('maintenance::reports.pdf', compact('logs'));

        return $pdf->download('maintenance-report-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Build a fresh maintenance-log query with the report filters applied.
     */
    private function filteredLogsQuery(Request $request)
    {
        $query = MaintenanceLog::query();

        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('from')) {
            $query->where('complaint_datetime', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('complaint_datetime', '<=', $request->to.' 23:59:59');
        }

        return $query;
    }

    public function index()
    {
        $logs = MaintenanceLog::latest('created_at')->get();
        $departments = MaintenanceLog::DEPARTMENTS;

        return view('maintenance::index', compact('logs', 'departments'));
    }

    public function create()
    {
        return view('maintenance::create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'department' => 'required|string|in:'.implode(',', array_keys(MaintenanceLog::DEPARTMENTS)),
            'priority' => 'required|in:'.implode(',', array_keys(MaintenanceLog::PRIORITIES)),
            'complaint_datetime' => 'required|date',
            'nature_of_complaint' => 'required|string',
            'image' => 'nullable|image|max:5120',
            'lodged_by' => 'required|string|max:100',
            'received_by' => 'nullable|string|max:100',
            'cost_of_fixing' => 'nullable|numeric',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('maintenance', 'public');
        }

        $validated['received_by'] = $validated['received_by'] ?? auth()->user()->name;
        $log = MaintenanceLog::create($validated);
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
            'department' => 'required|string|in:'.implode(',', array_keys(MaintenanceLog::DEPARTMENTS)),
            'priority' => 'required|in:'.implode(',', array_keys(MaintenanceLog::PRIORITIES)),
            'complaint_datetime' => 'required|date',
            'nature_of_complaint' => 'required|string',
            'image' => 'nullable|image|max:5120',
            'lodged_by' => 'required|string|max:100',
            'received_by' => 'nullable|string|max:100',
            'cost_of_fixing' => 'nullable|numeric',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

        if ($request->hasFile('image')) {
            if ($maintenanceLog->image) {
                Storage::disk('public')->delete($maintenanceLog->image);
            }
            $validated['image'] = $request->file('image')->store('maintenance', 'public');
        }

        if ($request->boolean('remove_image')) {
            if ($maintenanceLog->image) {
                Storage::disk('public')->delete($maintenanceLog->image);
            }
            $validated['image'] = null;
        }

        $previousStatus = $maintenanceLog->status;
        $statusChanged = $previousStatus !== $validated['status'];
        $maintenanceLog->update($validated);

        if ($statusChanged) {
            $this->sendNotification($maintenanceLog, 'status_update', $previousStatus);
        }

        return redirect()->route('maintenance.index')->with('success', 'Log updated successfully');
    }

    public function toggleStatus(Request $request, MaintenanceLog $maintenanceLog)
    {
        $request->validate(['status' => 'required|in:new,in_progress,completed,cancelled']);

        $previousStatus = $maintenanceLog->status;
        $maintenanceLog->update(['status' => $request->status, 'completion_date' => $request->status === 'completed' ? now() : $maintenanceLog->completion_date]);

        if ($previousStatus !== $request->status) {
            $this->sendNotification($maintenanceLog, 'status_update', $previousStatus);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $maintenanceLog->fresh()->status]);
        }

        return back()->with('success', 'Status updated to '.str_replace('_', ' ', $request->status).'.');
    }

    public function destroy(MaintenanceLog $maintenanceLog)
    {
        $maintenanceLog->delete();

        return redirect()->route('maintenance.index')->with('success', 'Log deleted successfully');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'department' => 'required|string|in:'.implode(',', array_keys(MaintenanceLog::DEPARTMENTS)),
            'priority' => 'required|in:'.implode(',', array_keys(MaintenanceLog::PRIORITIES)),
            'nature_of_complaint' => 'required|string',
            'lodged_by' => 'required|string|max:100',
            'received_by' => 'nullable|string|max:100',
            'complaint_datetime' => 'required|date',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('maintenance', 'public');
        }

        $validated['received_by'] = $validated['received_by'] ?? auth()->user()->name;
        $validated['status'] = 'new';
        $log = MaintenanceLog::create($validated);
        $this->sendNotification($log, 'new');

        return redirect()->back()->with('success', 'Issue reported successfully. Thank you!');
    }

    public function publicCreate()
    {
        return view('maintenance::public-create');
    }

    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'department' => 'required|string|in:'.implode(',', array_keys(MaintenanceLog::DEPARTMENTS)),
            'priority' => 'required|in:'.implode(',', array_keys(MaintenanceLog::PRIORITIES)),
            'nature_of_complaint' => 'required|string',
            'lodged_by' => 'required|string|max:100',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('maintenance', 'public');
        }

        $validated['priority'] = $validated['priority'] ?? 'medium';
        $validated['complaint_datetime'] = now();
        $validated['status'] = 'new';
        $log = MaintenanceLog::create($validated);
        $this->sendNotification($log, 'new');

        return redirect()->route('maintenance.public.create')
            ->with('success', 'Your issue has been reported. Thank you!');
    }

    public function qrCode()
    {
        $url = route('maintenance.public.create');

        return view('maintenance::qr', compact('url'));
    }

    protected function notificationRecipients(): array
    {
        return array_filter(array_map('trim', config('mail.maintenance_recipients', [])), fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    protected function sendNotification(MaintenanceLog $log, string $type, ?string $previousStatus = null): void
    {
        $recipients = $this->notificationRecipients();

        if (empty($recipients)) {
            Log::warning('No valid maintenance notification recipients configured', ['log_id' => $log->id, 'type' => $type]);

            return;
        }

        try {
            Mail::to($recipients)->queue(
                new MaintenanceNotification($log, $type, $previousStatus)
            );
            Log::info('Maintenance notification queued', [
                'log_id' => $log->id,
                'type' => $type,
                'recipients' => $recipients,
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
