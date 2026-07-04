<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontdeskcrm\Models\GuestDocument;
use Modules\Frontdeskcrm\Models\Registration;
use Yajra\DataTables\Facades\DataTables;

class PreArrivalDashboardController extends Controller
{
    public function index()
    {
        return view('frontdeskcrm::pre-arrival.index');
    }

    public function datatable(Request $request)
    {
        $query = Registration::with(['guest', 'roomType', 'documents'])
            ->where('stay_status', 'reserved')
            ->whereNotNull('pre_arrival_token');

        return DataTables::of($query)
            ->addColumn('guest_name', function ($reg) {
                return '<span class="fw-bold">' . e($reg->guest?->full_name ?? $reg->full_name) . '</span>';
            })
            ->addColumn('room_type', function ($reg) {
                return $reg->roomType?->name ?? 'N/A';
            })
            ->addColumn('check_in', function ($reg) {
                return $reg->check_in?->format('M d, Y');
            })
            ->addColumn('check_out', function ($reg) {
                return $reg->check_out?->format('M d, Y');
            })
            ->addColumn('arrival_time', function ($reg) {
                return $reg->estimated_arrival_at
                    ? \Carbon\Carbon::parse($reg->estimated_arrival_at)->format('h:i A')
                    : '<span class="text-muted">—</span>';
            })
            ->addColumn('documents', function ($reg) {
                $total = $reg->documents->count();
                $pending = $reg->documents->whereNull('verified_at')->whereNull('rejected_at')->count();
                $submitted = $total - $pending;
                return $submitted . ' / ' . $total;
            })
            ->addColumn('status_badge', function ($reg) {
                if ($reg->pre_arrival_completed_at) {
                    return '<span class="badge bg-success">Completed</span>';
                }
                return '<span class="badge bg-warning text-dark">Pending</span>';
            })
            ->addColumn('actions', function ($reg) {
                $showUrl = route('frontdesk.pre-arrivals.show', $reg);
                return '<a href="' . $showUrl . '" class="btn btn-sm btn-outline-dark"><i class="fas fa-eye me-1"></i>View</a>';
            })
            ->rawColumns(['guest_name', 'arrival_time', 'documents', 'status_badge', 'actions'])
            ->make(true);
    }

    public function show(Registration $registration)
    {
        if ($registration->stay_status !== 'reserved' || !$registration->pre_arrival_token) {
            return redirect()->route('frontdesk.pre-arrivals.index')
                ->with('error', 'This registration is not a valid pre-arrival.');
        }

        $registration->load(['guest', 'roomType', 'documents.guest', 'documents.verifiedBy']);

        return view('frontdeskcrm::pre-arrival.show', compact('registration'));
    }

    public function verifyDocument(Request $request, Registration $registration, GuestDocument $document)
    {
        if ($document->registration_id !== $registration->id) {
            return back()->with('error', 'Document does not belong to this registration.');
        }

        $document->update([
            'verified_at' => now(),
            'verified_by_agent_id' => auth()->id(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Document verified successfully.');
    }

    public function rejectDocument(Request $request, Registration $registration, GuestDocument $document)
    {
        if ($document->registration_id !== $registration->id) {
            return back()->with('error', 'Document does not belong to this registration.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $document->update([
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'verified_at' => null,
            'verified_by_agent_id' => null,
        ]);

        return back()->with('success', 'Document rejected.');
    }

    public function approve(Registration $registration)
    {
        if ($registration->stay_status !== 'reserved' || !$registration->pre_arrival_token) {
            return back()->with('error', 'This registration is not a valid pre-arrival.');
        }

        if (!$registration->pre_arrival_completed_at) {
            $registration->update(['pre_arrival_completed_at' => now()]);
        }

        return back()->with('success', 'Pre-arrival approved successfully.');
    }

    public function sendReminder(Registration $registration)
    {
        if ($registration->stay_status !== 'reserved' || !$registration->pre_arrival_token) {
            return back()->with('error', 'This registration is not a valid pre-arrival.');
        }

        try {
            $messaging = app(\Modules\Frontdeskcrm\Services\GuestMessagingService::class);
            $result = $messaging->sendFromTemplate($registration, 'pre_arrival_reminder', 'email');

            if ($result) {
                return back()->with('success', 'Pre-arrival reminder sent to guest.');
            }

            return back()->with('error', 'No pre-arrival reminder template found. Please seed the message templates.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send pre-arrival reminder', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to send reminder: ' . $e->getMessage());
        }
    }
}
