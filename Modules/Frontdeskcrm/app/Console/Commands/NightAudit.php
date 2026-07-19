<?php

namespace Modules\Frontdeskcrm\Console\Commands;

use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\NightAuditCompleted;
use Modules\Frontdeskcrm\Models\Registration;

class NightAudit extends Command
{
    protected $signature = 'night-audit {--property=} {--date=}';

    protected $description = 'Run night audit for specified property (posts room charges, taxes, generates reports)';

    protected array $results = [];

    public function handle(): int
    {
        $propertyId = $this->option('property');
        $auditDate = $this->option('date') ?? now()->toDateString();

        $this->info("Starting night audit for date: {$auditDate}");

        if ($propertyId) {
            $this->processProperty((int) $propertyId, $auditDate);
        } else {
            $properties = Property::where('is_active', true)->get();
            foreach ($properties as $property) {
                $this->processProperty($property->id, $auditDate);
            }
        }

        $this->info('Night audit completed successfully!');
        $this->info('Results:');
        foreach ($this->results as $key => $value) {
            $this->line("  {$key}: {$value}");
        }

        Log::info('Night audit completed', $this->results);

        return Command::SUCCESS;
    }

    protected function processProperty(int $propertyId, string $auditDate): void
    {
        $this->info("Processing property ID: {$propertyId}");

        app(PropertyService::class)->setCurrent(Property::findOrFail($propertyId));

        $this->postRoomCharges($propertyId, $auditDate);
        $this->postTaxes($propertyId, $auditDate);
        $this->handleNoShows($propertyId, $auditDate);
        $this->updateRoomStatuses($propertyId);

        $summary = [
            'room_charges_posted' => $this->results['room_charges_posted'] ?? 0,
            'taxes_posted' => $this->results['taxes_posted'] ?? 0,
            'no_shows_handled' => $this->results['no_shows_handled'] ?? 0,
        ];

        event(new NightAuditCompleted($auditDate, $summary, $propertyId));
    }

    protected function postRoomCharges(int $propertyId, string $auditDate): void
    {
        $this->info('  Posting room charges...');

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
            $this->postRoomChargeForRegistration($registration, $auditDate);
            $posted++;
        }

        $this->results['room_charges_posted'] = ($this->results['room_charges_posted'] ?? 0) + $posted;
        $this->info("  Posted room charges for {$posted} registrations");
    }

    protected function postRoomChargeForRegistration(Registration $registration, string $auditDate): void
    {
        $chargeType = DB::table('charge_types')
            ->where('code', 'room_rate')
            ->first();

        if (! $chargeType) {
            $chargeType = DB::table('charge_types')->insertGetId([
                'code' => 'room_rate',
                'name' => 'Room Rate',
                'account_code' => '4100',
                'tax_percentage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $chargeTypeId = $chargeType;
        } else {
            $chargeTypeId = $chargeType->id;
        }

        $existingCharge = DB::table('folio_charges')
            ->where('registration_id', $registration->id)
            ->where('charge_type_id', $chargeTypeId)
            ->whereDate('created_at', $auditDate)
            ->first();

        if ($existingCharge) {
            return;
        }

        DB::table('folio_charges')->insert([
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
    }

    protected function postTaxes(int $propertyId, string $auditDate): void
    {
        $this->info('  Posting taxes...');

        $taxTypes = DB::table('charge_types')
            ->where('tax_percentage', '>', 0)
            ->get();

        $posted = 0;
        foreach ($taxTypes as $taxType) {
            $charges = DB::table('folio_charges')
                ->join('registrations', 'folio_charges.registration_id', '=', 'registrations.id')
                ->where('registrations.property_id', $propertyId)
                ->where('registrations.stay_status', 'checked_in')
                ->where('folio_charges.charge_type_id', $taxType->id)
                ->whereDate('folio_charges.created_at', $auditDate)
                ->where('folio_charges.description', 'not like', '%Tax%')
                ->get();

            foreach ($charges as $charge) {
                $taxAmount = round($charge->amount * $taxType->tax_percentage / 100, 2);

                $existingTax = DB::table('folio_charges')
                    ->where('registration_id', $charge->registration_id)
                    ->where('description', 'like', "%Tax: {$taxType->name}%")
                    ->whereDate('created_at', $auditDate)
                    ->first();

                if (! $existingTax && $taxAmount > 0) {
                    DB::table('folio_charges')->insert([
                        'registration_id' => $charge->registration_id,
                        'charge_type_id' => $taxType->id,
                        'description' => "Tax: {$taxType->name} ({$taxType->tax_percentage}%)",
                        'quantity' => 1,
                        'unit_price' => $taxAmount,
                        'amount' => $taxAmount,
                        'posted_by' => auth()->id() ?? 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $posted++;
                }
            }
        }

        $this->results['taxes_posted'] = ($this->results['taxes_posted'] ?? 0) + $posted;
        $this->info("  Posted {$posted} tax charges");
    }

    protected function handleNoShows(int $propertyId, string $auditDate): void
    {
        $this->info('  Handling no-shows...');

        $noShows = Registration::withoutGlobalScopes()
            ->where('property_id', $propertyId)
            ->whereIn('stay_status', ['reserved', 'draft_by_guest'])
            ->where('check_in', '<', $auditDate)
            ->get();

        $handled = 0;
        foreach ($noShows as $registration) {
            $registration->update([
                'stay_status' => 'no_show',
                'notes' => ($registration->notes ?? '')."\nMarked as no-show during night audit on {$auditDate}",
            ]);

            if ($registration->room_unit_id) {
                DB::table('room_units')
                    ->where('id', $registration->room_unit_id)
                    ->update(['status' => 'available']);
            }

            $handled++;
        }

        $this->results['no_shows_handled'] = ($this->results['no_shows_handled'] ?? 0) + $handled;
        $this->info("  Handled {$handled} no-shows");
    }

    protected function updateRoomStatuses(int $propertyId): void
    {
        $this->info('  Updating room statuses...');

        $checkedOutToday = Registration::withoutGlobalScopes()
            ->where('property_id', $propertyId)
            ->where('stay_status', 'checked_out')
            ->whereDate('actual_checkout_at', now()->toDateString())
            ->whereNotNull('room_unit_id')
            ->pluck('room_unit_id');

        if ($checkedOutToday->isNotEmpty()) {
            DB::table('room_units')
                ->whereIn('id', $checkedOutToday)
                ->update([
                    'status' => 'available',
                    'cleaning_status' => 'dirty',
                    'updated_at' => now(),
                ]);
        }

        $this->info('  Updated room statuses for checked-out rooms');
    }
}
