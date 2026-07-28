<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontdeskcrm\Models\NightAuditLog;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Services\NightAuditService;

class NightAuditController extends Controller
{
    protected NightAuditService $nightAudit;

    public function __construct(NightAuditService $nightAudit)
    {
        $this->nightAudit = $nightAudit;
    }

    public function index()
    {
        $auditLogs = NightAuditLog::with('performedBy')
            ->orderBy('business_date', 'desc')
            ->paginate(20);

        $lastAudit = NightAuditLog::where('status', 'completed')
            ->orderBy('business_date', 'desc')
            ->first();

        $inHouseCount = Registration::where('stay_status', 'checked_in')->count();

        $todayRevenue = RegistrationCharge::whereDate('created_at', today())
            ->where('charge_type', 'room')
            ->sum('amount');

        return view('frontdeskcrm::night-audit.index', compact(
            'auditLogs', 'lastAudit', 'inHouseCount', 'todayRevenue'
        ));
    }

    public function run(Request $request)
    {
        $date = $request->has('date')
            ? Carbon::parse($request->date)
            : today();

        try {
            $audit = $this->nightAudit->process($date, auth()->id());

            return redirect()->route('frontdesk.night-audit.show', $audit)
                ->with('success', "Night audit for {$date->format('M d, Y')} completed successfully.");
        } catch (\RuntimeException $e) {
            return redirect()->route('frontdesk.night-audit.index')
                ->with('error', $e->getMessage());
        }
    }

    public function show(NightAuditLog $nightAuditLog)
    {
        $auditLog = $nightAuditLog->load('performedBy', 'charges.registration');

        return view('frontdeskcrm::night-audit.show', compact('auditLog'));
    }

    public function preview()
    {
        $inHouse = Registration::with(['guest', 'roomType', 'rateCode'])
            ->where('stay_status', 'checked_in')
            ->whereDate('check_in', '<=', today())
            ->whereDate('check_out', '>', today())
            ->get();

        $totalRevenue = $inHouse->sum('room_rate');
        $occupiedCount = $inHouse->count();

        return view('frontdeskcrm::night-audit.preview', compact(
            'inHouse', 'totalRevenue', 'occupiedCount'
        ));
    }
}
