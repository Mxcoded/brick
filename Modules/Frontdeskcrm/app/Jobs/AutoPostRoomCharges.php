<?php

namespace Modules\Frontdeskcrm\Jobs;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\NightAudit;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;

class AutoPostRoomCharges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public float $taxRate;

    public NightAudit $audit;

    public array $results;

    public function __construct(NightAudit $audit)
    {
        $this->audit = $audit;
        $this->taxRate = Property::find($audit->property_id)?->getTaxRate() ?? 7.5;
        $this->results = [
            'charges_posted' => 0,
            'room_revenue' => 0,
            'extra_revenue' => 0,
            'tax_amount' => 0,
            'total_revenue' => 0,
            'checked_in_count' => 0,
            'occupancy_count' => 0,
            'payments_count' => 0,
            'total_payments' => 0,
        ];
    }

    public function handle(): void
    {
        $auditDate = $this->audit->audit_date;
        $today = Carbon::today();

        $roomNightChargeType = ChargeType::firstOrCreate(
            ['code' => 'room_night'],
            [
                'name' => 'Room Night Charge',
                'description' => 'Auto-posted daily room charge',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $taxChargeType = ChargeType::firstOrCreate(
            ['code' => 'vat'],
            [
                'name' => 'VAT '.$this->taxRate.'%',
                'description' => 'Value Added Tax on room charges',
                'is_active' => true,
                'sort_order' => 99,
            ]
        );

        $checkedInRegistrations = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_in', '<=', $auditDate)
            ->get();

        $this->results['checked_in_count'] = $checkedInRegistrations->count();

        $roomNightChargeTypeId = $roomNightChargeType->id;
        $taxChargeTypeId = $taxChargeType->id;
        $systemUserId = $this->getSystemUserId();

        $chargesToInsert = [];

        foreach ($checkedInRegistrations as $registration) {
            $checkOutDate = $registration->check_out
                ? Carbon::parse($registration->check_out)->startOfDay()
                : $today->copy()->addDay();

            if ($auditDate->greaterThanOrEqualTo($checkOutDate)) {
                continue;
            }

            $roomRate = $registration->room_rate ?? 0;

            $folioChargesTotal = $registration->folioCharges()
                ->whereDate('created_at', $auditDate)
                ->sum('amount');

            $this->results['room_revenue'] += $roomRate;
            $this->results['extra_revenue'] += $folioChargesTotal;

            $chargesToInsert[] = [
                'registration_id' => $registration->id,
                'charge_type_id' => $roomNightChargeTypeId,
                'description' => 'Room Charge - '.$auditDate->format('M d, Y'),
                'quantity' => 1,
                'unit_price' => $roomRate,
                'amount' => $roomRate,
                'posted_by' => $systemUserId,
                'created_at' => $auditDate->copy()->setTime(23, 59, 0),
                'updated_at' => $auditDate->copy()->setTime(23, 59, 0),
            ];

            $this->results['charges_posted']++;
        }

        if (! empty($chargesToInsert)) {
            DB::table('folio_charges')->insert($chargesToInsert);
        }

        $this->results['total_revenue'] = $this->results['room_revenue'] + $this->results['extra_revenue'];
        $this->results['tax_amount'] = round($this->results['total_revenue'] * $this->taxRate / 100, 2);

        $totalRooms = Room::count();
        $this->results['occupancy_count'] = $checkedInRegistrations->count();
        $this->results['total_rooms'] = $totalRooms;
        $this->results['occupancy_percentage'] = $totalRooms > 0
            ? round(($this->results['occupancy_count'] / $totalRooms) * 100, 2)
            : 0;

        $this->results['payments_count'] = RegistrationPayment::whereHas('registration', function ($q) {
            $q->where('stay_status', 'checked_in');
        })->whereDate('payment_date', $auditDate)->count();

        $this->results['total_payments'] = RegistrationPayment::whereHas('registration', function ($q) {
            $q->where('stay_status', 'checked_in');
        })->whereDate('payment_date', $auditDate)->sum('amount');

        $this->audit->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $this->audit->started_by,
            'checked_in_count' => $this->results['checked_in_count'],
            'occupancy_count' => $this->results['occupancy_count'],
            'total_rooms' => $this->results['total_rooms'],
            'occupancy_percentage' => $this->results['occupancy_percentage'],
            'room_revenue' => $this->results['room_revenue'],
            'extra_revenue' => $this->results['extra_revenue'],
            'tax_amount' => $this->results['tax_amount'],
            'total_revenue' => $this->results['total_revenue'],
            'total_payments' => $this->results['total_payments'],
            'charges_posted' => $this->results['charges_posted'],
            'payments_count' => $this->results['payments_count'],
        ]);

        Log::info("Night audit completed for {$auditDate->format('Y-m-d')}", $this->results);
    }

    private function getSystemUserId(): int
    {
        $systemUser = User::where('email', 'system@brickspoint.com')->first();
        if ($systemUser) {
            return $systemUser->id;
        }

        return Auth::id() ?? 1;
    }
}
