<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Frontdeskcrm\Models\Registration;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        $query = Registration::where('is_group_lead', true)
            ->withCount('children')
            ->with(['children' => function ($q) {
                $q->select('id', 'parent_registration_id', 'stay_status', 'full_name', 'room_rate', 'no_of_nights', 'total_amount');
            }, 'guest', 'room']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('reservation_code', 'like', "%{$search}%")
                    ->orWhereHas('children', function ($cq) use ($search) {
                        $cq->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('stay_status', $request->status);
        }

        $groups = $query->latest()->paginate(15)->through(function ($group) {
            $members = $group->children;
            $group->checked_in_count = $members->where('stay_status', 'checked_in')->count();
            $group->checked_out_count = $members->where('stay_status', 'checked_out')->count();
            $group->draft_count = $members->where('stay_status', 'draft_by_guest')->count();
            $group->no_show_count = $members->where('stay_status', 'no_show')->count();
            $group->total_member_revenue = $members->sum('total_amount');
            unset($group->children);

            return $group;
        });

        $summary = [
            'total_groups' => Registration::where('is_group_lead', true)->count(),
            'active_groups' => Registration::where('is_group_lead', true)->where('stay_status', 'checked_in')->count(),
            'total_members' => Registration::whereNotNull('parent_registration_id')->count(),
            'total_revenue' => Registration::where('is_group_lead', true)->sum('total_amount')
                + Registration::whereNotNull('parent_registration_id')->sum('total_amount'),
        ];

        return view('frontdeskcrm::groups.index', compact('groups', 'summary'));
    }

    public function show(Registration $registration): View|RedirectResponse
    {
        if (! $registration->is_group_lead) {
            return redirect()->route('frontdesk.registrations.show', $registration->parent_registration_id ?? $registration);
        }

        $registration->load(['guest', 'room', 'roomType', 'folioCharges', 'payments']);
        $members = Registration::where('parent_registration_id', $registration->id)
            ->with(['room', 'roomType', 'folioCharges', 'payments'])
            ->get();

        $leadTotal = $registration->folioCharges->sum('amount') + ($registration->discounted_rate ?? $registration->room_rate ?? 0) * ($registration->no_of_nights ?? 1);
        $leadPaid = $registration->payments->sum('amount');
        $leadBalance = $leadTotal - $leadPaid;

        $membersTotal = 0;
        $membersPaid = 0;
        foreach ($members as $member) {
            $mTotal = $member->folioCharges->sum('amount') + ($member->discounted_rate ?? $member->room_rate ?? 0) * ($member->no_of_nights ?? 1);
            $mPaid = $member->payments->sum('amount');
            $membersTotal += $mTotal;
            $membersPaid += $mPaid;
        }
        $membersBalance = $membersTotal - $membersPaid;

        $financialSummary = [
            'lead_total' => $leadTotal,
            'lead_paid' => $leadPaid,
            'lead_balance' => $leadBalance,
            'members_total' => $membersTotal,
            'members_paid' => $membersPaid,
            'members_balance' => $membersBalance,
            'grand_total' => $leadTotal + $membersTotal,
            'grand_paid' => $leadPaid + $membersPaid,
            'grand_balance' => $leadBalance + $membersBalance,
        ];

        return view('frontdeskcrm::groups.show', compact('registration', 'members', 'financialSummary'));
    }

    public function bulkCheckin(Request $request, Registration $registration): RedirectResponse
    {
        if (! $registration->is_group_lead) {
            return back()->with('error', 'This registration is not a group lead.');
        }
        if ($registration->stay_status !== 'checked_in') {
            return back()->with('error', 'Group lead must be checked in first.');
        }

        $checkedIn = 0;
        $errors = [];
        $members = Registration::where('parent_registration_id', $registration->id)
            ->whereIn('stay_status', ['reserved', 'draft_by_guest'])
            ->get();

        foreach ($members as $member) {
            if (! $member->room_unit_id) {
                $errors[] = "{$member->full_name}: no room assigned";

                continue;
            }
            $member->update([
                'stay_status' => 'checked_in',
                'checked_in_at' => now(),
            ]);
            $member->roomUnit()?->update(['status' => 'occupied']);
            $checkedIn++;
        }

        $message = "{$checkedIn} member(s) checked in.";
        if (! empty($errors)) {
            $message .= ' Skipped: '.implode(', ', $errors);
        }

        return redirect()->route('frontdesk.groups.show', $registration)->with('success', $message);
    }

    public function bulkCheckout(Request $request, Registration $registration): RedirectResponse
    {
        if (! $registration->is_group_lead) {
            return back()->with('error', 'This registration is not a group lead.');
        }

        $count = 0;
        $members = Registration::where('parent_registration_id', $registration->id)
            ->where('stay_status', 'checked_in')
            ->get();

        foreach ($members as $member) {
            $member->update([
                'stay_status' => 'checked_out',
                'actual_checkout_at' => now(),
                'checked_out_by_agent_id' => Auth::id(),
            ]);
            if ($member->room_unit_id) {
                $member->roomUnit()?->update(['status' => 'available', 'cleaning_status' => 'dirty']);
            }
            $count++;
        }

        if ($request->boolean('checkout_lead')) {
            $registration->update([
                'stay_status' => 'checked_out',
                'actual_checkout_at' => now(),
                'checked_out_by_agent_id' => Auth::id(),
            ]);
            if ($registration->room_unit_id) {
                $registration->roomUnit()?->update(['status' => 'available', 'cleaning_status' => 'dirty']);
            }
            $count++;
        }

        return redirect()->route('frontdesk.groups.show', $registration)
            ->with('success', "{$count} guest(s) checked out successfully.");
    }

    public function destroy(Request $request, Registration $registration): RedirectResponse
    {
        if (! $registration->is_group_lead) {
            return back()->with('error', 'This registration is not a group lead.');
        }

        return DB::transaction(function () use ($registration) {
            Registration::where('parent_registration_id', $registration->id)->delete();
            $registration->delete();

            return redirect()->route('frontdesk.groups.index')->with('success', 'Group deleted.');
        });
    }
}
