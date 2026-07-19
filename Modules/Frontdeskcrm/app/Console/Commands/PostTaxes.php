<?php

namespace Modules\Frontdeskcrm\Console\Commands;

use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PostTaxes extends Command
{
    protected $signature = 'night-audit:post-taxes {--property=} {--date=}';

    protected $description = 'Post tax charges for applicable folio charges';

    public function handle(): int
    {
        $propertyId = $this->option('property');
        $auditDate = $this->option('date') ?? now()->toDateString();

        $this->info("Posting taxes for date: {$auditDate}");

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

        $taxTypes = DB::table('charge_types')
            ->where('tax_percentage', '>', 0)
            ->get();

        $posted = 0;
        foreach ($taxTypes as $taxType) {
            $posted += $this->postTaxForType($taxType, $propertyId, $auditDate);
        }

        $this->info("Posted {$posted} tax charges");
    }

    protected function postTaxForType(object $taxType, int $propertyId, string $auditDate): int
    {
        $charges = DB::table('folio_charges')
            ->join('registrations', 'folio_charges.registration_id', '=', 'registrations.id')
            ->where('registrations.property_id', $propertyId)
            ->where('registrations.stay_status', 'checked_in')
            ->where('folio_charges.charge_type_id', $taxType->id)
            ->whereDate('folio_charges.created_at', $auditDate)
            ->where('folio_charges.description', 'not like', '%Tax%')
            ->get();

        $posted = 0;
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

                $this->line("  Posted tax for registration {$charge->registration_id}: {$taxType->name} ₦".number_format($taxAmount, 2));
                $posted++;
            }
        }

        return $posted;
    }
}
