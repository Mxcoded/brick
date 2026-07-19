<?php

namespace Modules\Frontdeskcrm\Console\Commands;

use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Events\FolioChargePosted;
use Modules\Frontdeskcrm\Models\Registration;

class PostRoomCharges extends Command
{
    protected $signature = 'night-audit:post-room-charges {--property=} {--date=}';

    protected $description = 'Post room charges for occupied rooms';

    public function handle(): int
    {
        $propertyId = $this->option('property');
        $auditDate = $this->option('date') ?? now()->toDateString();

        $this->info("Posting room charges for date: {$auditDate}");

        if ($propertyId) {
            $this->processProperty((int) $propertyId, $auditDate);
        } else {
            $properties = Property::where('is_active', true)->get();
            foreach ($properties as $property) {
                $this->processProperty($property->id, $auditDate);
            }
        }

        return Command::SUCCESS;
    }

    protected function processProperty(int $propertyId, string $auditDate): void
    {
        $this->info("Processing property ID: {$propertyId}");

        app(PropertyService::class)->setCurrent(Property::findOrFail($propertyId));

        $checkedIn = Registration::withoutGlobalScopes()
            ->where('property_id', $propertyId)
            ->where('stay_status', 'checked_in')
            ->where('check_in', '<=', $auditDate)
            ->where(function ($q) use ($auditDate) {
                $q->whereNull('check_out')
                    ->orWhere('check_out', '>', $auditDate);
            })
            ->get();

        $posted = 0;
        foreach ($checkedIn as $registration) {
            if ($this->postRoomCharge($registration, $auditDate)) {
                $posted++;
            }
        }

        $this->info("Posted room charges for {$posted} registrations");
    }

    protected function postRoomCharge(Registration $registration, string $auditDate): bool
    {
        $chargeType = DB::table('charge_types')
            ->where('code', 'room_rate')
            ->first();

        if (! $chargeType) {
            $chargeTypeId = DB::table('charge_types')->insertGetId([
                'code' => 'room_rate',
                'name' => 'Room Rate',
                'account_code' => '4100',
                'tax_percentage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $chargeTypeId = $chargeType->id;
        }

        $existingCharge = DB::table('folio_charges')
            ->where('registration_id', $registration->id)
            ->where('charge_type_id', $chargeTypeId)
            ->whereDate('created_at', $auditDate)
            ->first();

        if ($existingCharge) {
            $this->line("  Skipping registration {$registration->id} - charge already exists");

            return false;
        }

        $chargeId = DB::table('folio_charges')->insertGetId([
            'registration_id' => $registration->id,
            'charge_type_id' => $chargeTypeId,
            'description' => "Room charge for {$registration->reservation_code}",
            'quantity' => 1,
            'unit_price' => $registration->room_rate,
            'amount' => $registration->room_rate,
            'posted_by' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $folioCharge = DB::table('folio_charges')->find($chargeId);
        event(new FolioChargePosted(
            (object) $folioCharge,
            $registration
        ));

        $this->line("  Posted room charge for registration {$registration->id}: ₦".number_format($registration->room_rate, 2));

        return true;
    }
}
