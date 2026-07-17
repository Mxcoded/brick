<?php

namespace Modules\Maintenance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Modules\Maintenance\Models\MaintenanceLog;

class MaintenanceReportSeeder extends Seeder
{
    /**
     * Seed a representative spread of maintenance logs so the maintenance
     * report (filters, summary totals, cost aggregation, PDF export) can be
     * demonstrated and tested with realistic data.
     */
    public function run(): void
    {
        $entries = [
            // Completed jobs with cost (feed "Completed" + "Total Cost").
            ['department' => 'Electrical', 'priority' => 'high', 'status' => 'completed', 'cost' => 45000.00, 'daysAgo' => 30, 'complaint' => 'Power outage in the east wing corridor.'],
            ['department' => 'Plumbing', 'priority' => 'medium', 'status' => 'completed', 'cost' => 18500.50, 'daysAgo' => 21, 'complaint' => 'Leaking pipe under the kitchen sink.'],
            ['department' => 'HVAC', 'priority' => 'critical', 'status' => 'completed', 'cost' => 120000.00, 'daysAgo' => 14, 'complaint' => 'Central air conditioning unit not cooling.'],
            ['department' => 'Maintenance', 'priority' => 'low', 'status' => 'completed', 'cost' => 5000.00, 'daysAgo' => 10, 'complaint' => 'Broken door hinge in room 204.'],

            // Open / in-progress jobs (feed "Open / In Progress").
            ['department' => 'IT', 'priority' => 'high', 'status' => 'in_progress', 'cost' => null, 'daysAgo' => 5, 'complaint' => 'Front-desk workstation cannot connect to the network.'],
            ['department' => 'Housekeeping', 'priority' => 'medium', 'status' => 'new', 'cost' => null, 'daysAgo' => 3, 'complaint' => 'Stained carpet in the lobby lounge.'],
            ['department' => 'Security', 'priority' => 'critical', 'status' => 'new', 'cost' => null, 'daysAgo' => 1, 'complaint' => 'CCTV camera at the rear entrance is offline.'],
            ['department' => 'Electrical', 'priority' => 'medium', 'status' => 'in_progress', 'cost' => null, 'daysAgo' => 2, 'complaint' => 'Flickering lights in the banquet hall.'],

            // Cancelled job (should be excluded from open/completed counts).
            ['department' => 'Other', 'priority' => 'low', 'status' => 'cancelled', 'cost' => null, 'daysAgo' => 7, 'complaint' => 'Duplicate request for gym equipment servicing.'],

            // Older completed job (helps test date-range filtering).
            ['department' => 'Plumbing', 'priority' => 'high', 'status' => 'completed', 'cost' => 32000.00, 'daysAgo' => 90, 'complaint' => 'Blocked drainage in the basement.'],
        ];

        foreach ($entries as $entry) {
            $complaintAt = Carbon::now()->subDays($entry['daysAgo'])->setTime(9, 0);

            MaintenanceLog::create([
                'location' => 'Building A - Floor '.rand(1, 5),
                'department' => $entry['department'],
                'priority' => $entry['priority'],
                'complaint_datetime' => $complaintAt,
                'nature_of_complaint' => $entry['complaint'],
                'lodged_by' => 'Seeded Staff',
                'received_by' => 'Maintenance Desk',
                'cost_of_fixing' => $entry['cost'],
                'completion_date' => $entry['status'] === 'completed'
                    ? $complaintAt->copy()->addDays(2)->toDateString()
                    : null,
                'status' => $entry['status'],
            ]);
        }
    }
}
