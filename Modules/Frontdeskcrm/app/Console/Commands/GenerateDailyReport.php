<?php

namespace Modules\Frontdeskcrm\Console\Commands;

use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyReport extends Command
{
    protected $signature = 'night-audit:generate-report {--property=} {--date=}';

    protected $description = 'Generate daily operations report';

    public function handle(): int
    {
        $propertyId = $this->option('property');
        $reportDate = $this->option('date') ?? now()->toDateString();

        $this->info("Generating daily report for date: {$reportDate}");

        if ($propertyId) {
            $this->generateReport((int) $propertyId, $reportDate);
        } else {
            $properties = Property::where('is_active', true)->get();
            foreach ($properties as $property) {
                $this->generateReport($property->id, $reportDate);
            }
        }

        return Command::SUCCESS;
    }

    protected function generateReport(int $propertyId, string $reportDate): void
    {
        $this->info("\n=== Daily Report for Property #{$propertyId} ===");
        $this->info("Date: {$reportDate}\n");

        app(PropertyService::class)->setCurrent(Property::findOrFail($propertyId));

        $totalRooms = DB::table('room_units')
            ->where('property_id', $propertyId)
            ->count();

        $occupiedRooms = DB::table('room_units')
            ->where('property_id', $propertyId)
            ->where('status', 'occupied')
            ->count();

        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        $this->line('=== Occupancy ===');
        $this->line("  Total Rooms: {$totalRooms}");
        $this->line("  Occupied: {$occupiedRooms}");
        $this->line("  Occupancy Rate: {$occupancyRate}%");

        $roomRevenue = DB::table('folio_charges')
            ->join('registrations', 'folio_charges.registration_id', '=', 'registrations.id')
            ->join('charge_types', 'folio_charges.charge_type_id', '=', 'charge_types.id')
            ->where('registrations.property_id', $propertyId)
            ->where('charge_types.code', 'room_rate')
            ->whereDate('folio_charges.created_at', $reportDate)
            ->sum('folio_charges.amount');

        $totalRevenue = DB::table('folio_charges')
            ->join('registrations', 'folio_charges.registration_id', '=', 'registrations.id')
            ->where('registrations.property_id', $propertyId)
            ->whereDate('folio_charges.created_at', $reportDate)
            ->sum('folio_charges.amount');

        $restaurantRevenue = DB::table('restaurant_payments')
            ->where('property_id', $propertyId)
            ->whereDate('paid_at', $reportDate)
            ->where('status', 'completed')
            ->sum('amount');

        $this->line("\n=== Revenue ===");
        $this->line('  Room Revenue: ₦'.number_format($roomRevenue, 2));
        $this->line('  Restaurant Revenue: ₦'.number_format($restaurantRevenue, 2));
        $this->line('  Total Folio Charges: ₦'.number_format($totalRevenue, 2));

        $cashPayments = DB::table('restaurant_payments')
            ->where('property_id', $propertyId)
            ->whereDate('paid_at', $reportDate)
            ->where('method', 'cash')
            ->where('status', 'completed')
            ->sum('amount');

        $cardPayments = DB::table('restaurant_payments')
            ->where('property_id', $propertyId)
            ->whereDate('paid_at', $reportDate)
            ->where('method', 'card')
            ->where('status', 'completed')
            ->sum('amount');

        $roomChargePayments = DB::table('restaurant_payments')
            ->where('property_id', $propertyId)
            ->whereDate('paid_at', $reportDate)
            ->where('method', 'room_charge')
            ->where('status', 'completed')
            ->sum('amount');

        $this->line("\n=== Payments (Restaurant) ===");
        $this->line('  Cash: ₦'.number_format($cashPayments, 2));
        $this->line('  Card: ₦'.number_format($cardPayments, 2));
        $this->line('  Room Charge: ₦'.number_format($roomChargePayments, 2));

        $totalPaid = DB::table('restaurant_payments')
            ->where('property_id', $propertyId)
            ->whereDate('paid_at', $reportDate)
            ->where('status', 'completed')
            ->sum('amount');

        $this->line("\n=== Summary ===");
        $this->line('  Total Payments: ₦'.number_format($totalPaid, 2));

        $this->info("\nReport generated successfully!");
    }
}
