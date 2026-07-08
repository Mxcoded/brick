<?php

namespace Modules\Banquet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Banquet\Models\EventLead;
use Modules\Banquet\Models\LeadEvent;
use Yajra\DataTables\DataTables;

class EventLeadController extends Controller
{
    public function index(Request $request)
    {
        $events = LeadEvent::where('is_active', true)->orderBy('title')->get();

        $query = EventLead::query();
        if ($eventId = $request->get('event_id')) {
            $query->where('event_id', $eventId);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'new' => (clone $query)->where('status', 'New')->count(),
            'contacted' => (clone $query)->where('status', 'Contacted')->count(),
            'converted' => (clone $query)->where('status', 'Converted')->count(),
        ];

        return view('banquet::event-leads.index', compact('stats', 'events'));
    }

    public function datatable(Request $request)
    {
        $query = EventLead::with('leadEvent');

        if ($eventId = $request->get('event_id')) {
            $query->where('event_id', $eventId);
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($search = $request->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('company', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('event_name', function ($lead) {
                return $lead->leadEvent?->title ?? '<span class="text-muted">—</span>';
            })
            ->addColumn('status_badge', function ($lead) {
                $colors = ['New' => 'warning', 'Contacted' => 'info', 'Converted' => 'success', 'Closed' => 'secondary'];
                $color = $colors[$lead->status] ?? 'secondary';

                return '<span class="badge bg-'.$color.' rounded-pill px-3">'.$lead->status.'</span>';
            })
            ->addColumn('created_at_formatted', function ($lead) {
                return $lead->created_at->format('M d, Y h:i A');
            })
            ->addColumn('actions', function ($lead) {
                return view('banquet::event-leads.partials.actions', compact('lead'))->render();
            })
            ->rawColumns(['event_name', 'status_badge', 'actions'])
            ->make(true);
    }

    public function show($id)
    {
        $lead = EventLead::with('leadEvent')->findOrFail($id);

        return view('banquet::event-leads.show', compact('lead'));
    }

    public function updateStatus(Request $request, $id)
    {
        $lead = EventLead::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:New,Contacted,Converted,Closed',
        ]);

        $lead->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => $lead->status, 'message' => 'Status updated successfully.']);
        }

        return redirect()->route('banquet.event-leads.show', $id)
            ->with('success', 'Lead status updated to '.$validated['status'].'.');
    }

    public function updateNotes(Request $request, $id)
    {
        $lead = EventLead::findOrFail($id);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->route('banquet.event-leads.show', $id)
            ->with('success', 'Notes updated successfully.');
    }

    public function destroy($id)
    {
        $lead = EventLead::findOrFail($id);
        $lead->delete();

        return redirect()->route('banquet.event-leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = EventLead::with('leadEvent')->orderBy('created_at', 'desc');

        if ($eventId = $request->get('event_id')) {
            $query->where('event_id', $eventId);
        }

        $leads = $query->get();

        $csv = "Event,Name,Email,Phone,Company,Source,Status,Date\n";
        foreach ($leads as $lead) {
            $eventName = $lead->leadEvent?->title ?? 'N/A';
            $csv .= '"'.str_replace('"', '""', $eventName).'",'
                .'"'.str_replace('"', '""', $lead->name).'",'
                .'"'.str_replace('"', '""', $lead->email).'",'
                .'"'.str_replace('"', '""', $lead->phone).'",'
                .'"'.str_replace('"', '""', $lead->company).'",'
                .'"'.str_replace('"', '""', $lead->source).'",'
                .'"'.$lead->status.'",'
                .'"'.$lead->created_at->format('Y-m-d H:i')."\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="event-leads-'.date('Y-m-d').'.csv"',
        ]);
    }
}
