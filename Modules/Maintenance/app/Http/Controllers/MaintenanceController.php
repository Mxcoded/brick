<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Maintenance\Mail\MaintenanceNotification;
use Modules\Maintenance\Models\MaintenanceLog;

class MaintenanceController extends Controller
{
    protected array $notificationRecipients = [
        'it@brickspoint.com',

    ];

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

        return view('maintenance::dashboard', compact(
            'totalLogs', 'openLogs', 'completedLogs', 'cancelledLogs', 'thisMonth',
            'departmentStats', 'statusStats', 'recentLogs', 'avgCompletionDays'
        ));
    }

    public function report(Request $request)
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
            $query->where('complaint_datetime', '<=', $request->to . ' 23:59:59');
        }

        $logs = $query->latest('complaint_datetime')->paginate(25)->withQueryString();

        $summary = [
            'total' => $logs->total(),
            'open' => $query->open()->count(),
            'completed' => $query->completed()->count(),
            'totalCost' => $query->whereNotNull('cost_of_fixing')->sum('cost_of_fixing'),
        ];

        return view('maintenance::report', compact('logs', 'summary'));
    }

    public function exportReport(Request $request)
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
            $query->where('complaint_datetime', '<=', $request->to . ' 23:59:59');
        }

        $logs = $query->latest('complaint_datetime')->get();

        $pdf = Pdf::loadView('maintenance::reports.pdf', compact('logs'));
        return $pdf->download('maintenance-report-' . now()->format('Y-m-d') . '.pdf');
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
            'department' => 'required|string|in:' . implode(',', array_keys(MaintenanceLog::DEPARTMENTS)),
            'complaint_datetime' => 'required|date',
            'nature_of_complaint' => 'required|string',
            'lodged_by' => 'required|string|max:100',
            'received_by' => 'nullable|string|max:100',
            'cost_of_fixing' => 'nullable|numeric',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

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
            'department' => 'required|string|in:' . implode(',', array_keys(MaintenanceLog::DEPARTMENTS)),
            'complaint_datetime' => 'required|date',
            'nature_of_complaint' => 'required|string',
            'lodged_by' => 'required|string|max:100',
            'received_by' => 'nullable|string|max:100',
            'cost_of_fixing' => 'nullable|numeric',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

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

        return back()->with('success', 'Status updated to ' . str_replace('_', ' ', $request->status) . '.');
    }

    public function destroy(MaintenanceLog $maintenanceLog)
    {
        $maintenanceLog->delete();
        return redirect()->route('maintenance.index')->with('success', 'Log deleted successfully');
    }

    public function publicCreate()
    {
        return view('maintenance::public-create');
    }

    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'department' => 'required|string|in:' . implode(',', array_keys(MaintenanceLog::DEPARTMENTS)),
            'nature_of_complaint' => 'required|string',
            'lodged_by' => 'required|string|max:100',
        ]);

        $validated['complaint_datetime'] = now();
        $validated['status'] = 'new';
        MaintenanceLog::create($validated);

        return redirect()->route('maintenance.public.create')
            ->with('success', 'Your issue has been reported. Thank you!');
    }

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
