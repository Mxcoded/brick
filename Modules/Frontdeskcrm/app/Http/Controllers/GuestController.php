<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Frontdeskcrm\Exports\GuestImportGuide;
use Modules\Frontdeskcrm\Exports\GuestsImport;
use Modules\Frontdeskcrm\Models\Guest;
use Yajra\DataTables\DataTables;

class GuestController extends Controller
{
    /**
     * Display a listing of guests.
     */
    public function index()
    {
        $stats = [
            'total_guests' => Guest::count(),
            'recent_visitors' => Guest::recentVisitors(30)->count(),
            'returning_guests' => Guest::where('visit_count', '>', 1)->count(),
            'new_this_month' => Guest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('frontdeskcrm::guests.index', compact('stats'));
    }

    /**
     * Get guests for DataTables.
     */
    public function datatable(Request $request)
    {
        $query = Guest::withCount('registrations')
            ->latest('last_visit_at');

        return DataTables::of($query)
            ->addColumn('name_display', function ($guest) {
                $title = $guest->title ? $guest->title.' ' : '';

                return '<span class="fw-bold">'.$title.$guest->full_name.'</span>';
            })
            ->addColumn('contact_display', function ($guest) {
                $phone = $guest->contact_number ? '<i class="fas fa-phone me-1"></i>'.$guest->contact_number : '';
                $email = $guest->email ? '<br><small class="text-muted"><i class="fas fa-envelope me-1"></i>'.$guest->email.'</small>' : '';

                return $phone.$email;
            })
            ->addColumn('visit_count_display', function ($guest) {
                $badge = $guest->visit_count > 1 ? 'bg-success' : 'bg-secondary';

                return '<span class="badge '.$badge.'">'.($guest->visit_count ?? 0).' visits</span>';
            })
            ->addColumn('last_visit_formatted', function ($guest) {
                return $guest->last_visit_at ? $guest->last_visit_at->format('M d, Y') : '<span class="text-muted">Never</span>';
            })
            ->addColumn('created_at_formatted', function ($guest) {
                return $guest->created_at->format('M d, Y');
            })
            ->addColumn('actions', function ($guest) {
                $showUrl = route('frontdesk.guests.show', $guest->id);
                $editUrl = route('frontdesk.guests.edit', $guest->id);

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$showUrl.'" class="btn btn-outline-primary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['name_display', 'contact_display', 'visit_count_display', 'last_visit_formatted', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new guest.
     */
    public function create()
    {
        return view('frontdeskcrm::guests.create');
    }

    /**
     * Store a newly created guest.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:20',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'contact_number' => 'required|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'identification_type' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:100',
            'gender' => 'nullable|in:Male,Female,Other',
            'birthday' => 'nullable|date',
            'occupation' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'home_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:20',
        ]);

        Guest::create($validated);

        return redirect()->route('frontdesk.guests.index')
            ->with('success', 'Guest profile created successfully.');
    }

    /**
     * Display the specified guest.
     */
    public function show($id)
    {
        $guest = Guest::with(['registrations' => function ($query) {
            $query->latest('check_in')->limit(10);
        }, 'preference'])->findOrFail($id);

        $stats = [
            'total_stays' => $guest->registrations()->count(),
            'total_nights' => $guest->registrations()->sum('total_nights') ?? 0,
            'last_visit' => $guest->last_visit_at,
            'first_visit' => $guest->registrations()->oldest('check_in')->first()?->check_in,
        ];

        return view('frontdeskcrm::guests.show', compact('guest', 'stats'));
    }

    /**
     * Show the form for editing the specified guest.
     */
    public function edit($id)
    {
        $guest = Guest::findOrFail($id);

        return view('frontdeskcrm::guests.edit', compact('guest'));
    }

    /**
     * Update the specified guest.
     */
    public function update(Request $request, $id)
    {
        $guest = Guest::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:20',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'contact_number' => 'required|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'identification_type' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:100',
            'gender' => 'nullable|in:Male,Female,Other',
            'birthday' => 'nullable|date',
            'occupation' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'home_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:20',
        ]);

        $guest->update($validated);

        return redirect()->route('frontdesk.guests.show', $id)
            ->with('success', 'Guest profile updated successfully.');
    }

    /**
     * Remove the specified guest.
     */
    public function destroy($id)
    {
        $guest = Guest::findOrFail($id);

        // Check if guest has registrations
        if ($guest->registrations()->exists()) {
            return back()->with('error', 'Cannot delete guest with existing registrations.');
        }

        $guest->delete();

        return redirect()->route('frontdesk.guests.index')
            ->with('success', 'Guest profile deleted successfully.');
    }

    public function showImportForm()
    {
        return view('frontdeskcrm::guests.import');
    }

    public function downloadTemplate()
    {
        return Excel::download(new GuestImportGuide, 'guest-import-guide.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new GuestsImport;
        Excel::import($import, $request->file('file'));

        return redirect()->route('frontdesk.guests.index')
            ->with('success', $import->getImportedCount().' guests imported. '.$import->getSkippedCount().' skipped (duplicates).');
    }
}
