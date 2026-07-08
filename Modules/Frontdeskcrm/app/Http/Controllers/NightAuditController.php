<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Models\Room;
use App\Services\PropertyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Jobs\AutoPostRoomCharges;
use Modules\Frontdeskcrm\Models\NightAudit;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Yajra\DataTables\Facades\DataTables;

class NightAuditController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $audits = NightAudit::with(['starter', 'completer'])->latest('audit_date');

            return DataTables::of($audits)
                ->addColumn('date', fn ($a) => $a->audit_date->format('D, M d, Y'))
                ->addColumn('status_badge', fn ($a) => view('frontdeskcrm::audit.partials.status-badge', ['audit' => $a]))
                ->addColumn('occupancy', fn ($a) => $a->occupancy_count.' / '.$a->total_rooms.' ('.$a->occupancy_percentage.'%)')
                ->addColumn('revenue', fn ($a) => '₦'.number_format($a->total_revenue, 2))
                ->addColumn('actions', fn ($a) => view('frontdeskcrm::audit.partials.actions', ['audit' => $a]))
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        $lastAudit = NightAudit::completed()->latest('audit_date')->first();
        $todayAudit = NightAudit::forDate(Carbon::today())->first();

        return view('frontdeskcrm::audit.index', compact('lastAudit', 'todayAudit'));
    }

    public function create()
    {
        $today = Carbon::today();

        $existingAudit = NightAudit::forDate($today)->first();
        if ($existingAudit && $existingAudit->status === 'completed') {
            return redirect()->route('frontdesk.audit.index')
                ->with('info', 'Night audit for today has already been completed.');
        }

        $checkedIn = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_in', '<=', $today)
            ->get();

        $totalRooms = Room::count();
        $occupancyCount = $checkedIn->count();
        $occupancyPercent = $totalRooms > 0 ? round(($occupancyCount / $totalRooms) * 100, 2) : 0;

        $roomRevenue = 0;
        $toCharge = [];

        foreach ($checkedIn as $registration) {
            $checkOut = $registration->check_out ? Carbon::parse($registration->check_out)->startOfDay() : $today->copy()->addDay();

            if ($today->greaterThanOrEqualTo($checkOut)) {
                continue;
            }

            $rate = $registration->room_rate ?? 0;
            $roomRevenue += $rate;

            $toCharge[] = [
                'id' => $registration->id,
                'guest' => $registration->full_name,
                'room' => $registration->roomUnit->room_number ?? $registration->room_allocation ?? '—',
                'room_type' => $registration->roomType->name ?? '—',
                'rate' => $rate,
                'check_in' => $registration->check_in?->format('M d'),
                'check_out' => $registration->check_out?->format('M d'),
                'nights' => $registration->no_of_nights,
                'total_amount' => $registration->total_amount ?? 0,
                'balance' => $registration->balance,
            ];
        }

        $taxRate = app(PropertyService::class)->taxRate();
        $taxAmount = round($roomRevenue * $taxRate / 100, 2);
        $grandRevenue = $roomRevenue + $taxAmount;

        $recentPayments = RegistrationPayment::whereHas('registration', function ($q) {
            $q->where('stay_status', 'checked_in');
        })->whereDate('payment_date', $today)->sum('amount');

        return view('frontdeskcrm::audit.create', compact(
            'toCharge', 'totalRooms', 'occupancyCount', 'occupancyPercent',
            'roomRevenue', 'taxRate', 'taxAmount', 'grandRevenue',
            'recentPayments', 'today', 'existingAudit'
        ));
    }

    public function store(Request $request)
    {
        $today = Carbon::today();

        $existing = NightAudit::forDate($today)->first();
        if ($existing && $existing->status === 'completed') {
            return back()->with('error', 'Night audit for today has already been completed.');
        }

        $checkedInCount = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_in', '<=', $today)
            ->count();

        if ($checkedInCount === 0) {
            $totalRooms = Room::count();

            $audit = NightAudit::updateOrCreate(
                ['audit_date' => $today],
                [
                    'status' => 'completed',
                    'started_at' => now(),
                    'completed_at' => now(),
                    'started_by' => Auth::id(),
                    'completed_by' => Auth::id(),
                    'checked_in_count' => 0,
                    'occupancy_count' => 0,
                    'total_rooms' => $totalRooms,
                    'occupancy_percentage' => 0,
                    'room_revenue' => 0,
                    'extra_revenue' => 0,
                    'tax_amount' => 0,
                    'total_revenue' => 0,
                    'total_payments' => 0,
                    'charges_posted' => 0,
                    'payments_count' => 0,
                    'notes' => $request->notes,
                ]
            );

            Log::info('Night audit completed (vacant) for '.$today->format('Y-m-d'));

            return redirect()->route('frontdesk.audit.show', $audit)
                ->with('success', 'Night audit completed. No guests were checked in.');
        }

        $audit = NightAudit::updateOrCreate(
            ['audit_date' => $today],
            [
                'status' => 'open',
                'started_at' => now(),
                'started_by' => Auth::id(),
                'notes' => $request->notes,
            ]
        );

        AutoPostRoomCharges::dispatchSync($audit);
        $audit->refresh();

        return redirect()->route('frontdesk.audit.show', $audit)
            ->with('success', 'Night audit completed successfully for '.$today->format('M d, Y').'.');
    }

    public function show(NightAudit $audit)
    {
        $audit->load(['starter', 'completer', 'logs']);

        return view('frontdeskcrm::audit.show', compact('audit'));
    }

    public function rollback(NightAudit $audit)
    {
        if ($audit->status !== 'completed') {
            return back()->with('error', 'Only completed audits can be rolled back.');
        }

        $today = Carbon::today();

        if ($audit->audit_date->isToday()) {
            return back()->with('error', 'Cannot roll back today\'s audit. Use the "Reopen" function on the registration instead.');
        }

        DB::transaction(function () use ($audit) {
            $postedDate = $audit->audit_date->copy()->setTime(23, 59, 0);

            $deleted = DB::table('folio_charges')
                ->where('description', 'LIKE', 'Room Charge - '.$audit->audit_date->format('M d, Y').'%')
                ->where('created_at', $postedDate)
                ->delete();

            $audit->update([
                'status' => 'rolled_back',
                'notes' => ($audit->notes ? $audit->notes."\n" : '').'Rolled back on '.now()->format('M d, Y H:i').' by '.Auth::user()->name.'. '.$deleted.' charges removed.',
            ]);

            Log::info("Night audit {$audit->id} rolled back. {$deleted} charges removed.");
        });

        return redirect()->route('frontdesk.audit.index')
            ->with('success', 'Night audit for '.$audit->audit_date->format('M d, Y').' has been rolled back.');
    }
}
