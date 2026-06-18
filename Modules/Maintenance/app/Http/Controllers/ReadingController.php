<?php

namespace Modules\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Maintenance\Exports\ReadingsExport;
use Modules\Maintenance\Models\MaintenanceReading;

class ReadingController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceReading::with('recorder');

        if ($request->filled('from')) {
            $query->where('reading_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('reading_date', '<=', $request->to);
        }
        if ($request->filled('reading_type')) {
            $query->where('reading_type', $request->reading_type);
        }

        $readings = $query->orderByDesc('reading_date')->orderByDesc('created_at')->paginate(30);

        return view('maintenance::readings.index', compact('readings'));
    }

    public function create()
    {
        $date = request('date', today()->toDateString());
        $existing = MaintenanceReading::onDate($date)->get()->groupBy(fn ($r) => $r->reading_type.'.'.$r->category);

        return view('maintenance::readings.create', compact('date', 'existing'));
    }

    public function store(Request $request)
    {
        $rules = [
            'reading_date' => 'required|date',
            'readings' => 'required|array',
            'readings.*.reading_type' => 'required|string',
            'readings.*.category' => 'nullable|string',
            'readings.*.reading_value' => 'required|numeric',
            'readings.*.capacity' => 'nullable|numeric|min:0',
            'readings.*.notes' => 'nullable|string|max:500',
        ];

        $request->validate($rules);

        foreach ($request->input('readings', []) as $key => $data) {
            $calculatedValue = null;
            if ($data['reading_type'] === 'generator' && ! empty($data['capacity'])) {
                $calculatedValue = round(($data['reading_value'] / 100) * $data['capacity'], 2);
            } elseif ($data['reading_type'] === 'diesel_reservoir') {
                $calculatedValue = $data['reading_value'];
            }

            MaintenanceReading::updateOrCreate(
                [
                    'reading_date' => $request->input('reading_date'),
                    'reading_type' => $data['reading_type'],
                    'category' => $data['category'] ?? '',
                ],
                [
                    'reading_value' => $data['reading_value'],
                    'capacity' => ! empty($data['capacity']) ? $data['capacity'] : null,
                    'calculated_value' => $calculatedValue,
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('maintenance.readings.index')
            ->with('success', 'Readings saved for '.$request->input('reading_date'));
    }

    public function show($date)
    {
        $readings = MaintenanceReading::onDate($date)->with('recorder')->get();

        if ($readings->isEmpty()) {
            return redirect()->route('maintenance.readings.create', ['date' => $date])
                ->with('info', 'No readings found for that date. Create one below.');
        }

        return view('maintenance::readings.show', compact('readings', 'date'));
    }

    public function edit($id)
    {
        $reading = MaintenanceReading::findOrFail($id);

        return view('maintenance::readings.edit', compact('reading'));
    }

    public function update(Request $request, $id)
    {
        $reading = MaintenanceReading::findOrFail($id);

        $data = $request->validate([
            'reading_value' => 'required|numeric',
            'notes' => 'nullable|string|max:500',
        ]);

        $capacity = $reading->capacity;
        $calculatedValue = null;

        if ($reading->reading_type === 'generator' && $capacity) {
            $calculatedValue = round(($data['reading_value'] / 100) * $capacity, 2);
        } elseif ($reading->reading_type === 'diesel_reservoir') {
            $calculatedValue = $data['reading_value'];
        }

        $data['calculated_value'] = $calculatedValue;
        $reading->update($data);

        return redirect()->route('maintenance.readings.show', $reading->reading_date->toDateString())
            ->with('success', 'Reading updated.');
    }

    public function destroy($id)
    {
        $reading = MaintenanceReading::findOrFail($id);
        $date = $reading->reading_date->toDateString();
        $reading->delete();

        return redirect()->route('maintenance.readings.show', $date)
            ->with('success', 'Reading deleted.');
    }

    public function exportReport(Request $request)
    {
        $query = MaintenanceReading::with('recorder');

        if ($request->filled('from')) {
            $query->where('reading_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('reading_date', '<=', $request->to);
        }
        if ($request->filled('reading_type')) {
            $query->where('reading_type', $request->reading_type);
        }

        $readings = $query->orderByDesc('reading_date')->orderByDesc('created_at')->get();

        if ($readings->isEmpty()) {
            return back()->with('info', 'No readings to export.');
        }

        $pdf = Pdf::loadView('maintenance::readings.pdf', compact('readings'));

        return $pdf->download('readings-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $export = new ReadingsExport(
            $request->input('from'),
            $request->input('to'),
            $request->input('reading_type')
        );

        if ($export->collection()->isEmpty()) {
            return back()->with('info', 'No readings to export.');
        }

        return Excel::download($export, 'readings-report-'.now()->format('Y-m-d').'.xlsx');
    }
}
