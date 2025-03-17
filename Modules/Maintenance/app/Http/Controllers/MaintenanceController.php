<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Maintenance\Models\MaintenanceLog;

class MaintenanceController extends Controller
{
    public function index()
    {
        $logs = MaintenanceLog::all();
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

        MaintenanceLog::create($validated);
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

    public function update(Request $request, MaintenanceLog $maintenance)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'complaint_datetime' => 'required|date',
            'nature_of_complaint' => 'required|string',
            'lodged_by' => 'required|string|max:100',
            'received_by' => 'required|string|max:100',
            'cost_of_fixing' => 'nullable|numeric',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

        $maintenance->update($validated);
        return redirect()->route('maintenance.index')->with('success', 'Log updated successfully');
    }

    public function destroy(MaintenanceLog $maintenanceLog)
    {
        $maintenanceLog->delete();
        return redirect()->route('maintenance.index')->with('success', 'Log deleted successfully');
    }
}
