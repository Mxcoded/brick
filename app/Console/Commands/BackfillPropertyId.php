<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillPropertyId extends Command
{
    protected $signature = 'properties:backfill {--property= : Property ID to assign (defaults to headquarters)}';

    protected $description = 'Backfill property_id on existing records that have no property assigned. Defaults to the headquarters property.';

    public function handle(): int
    {
        $property = $this->option('property')
            ? Property::find($this->option('property'))
            : Property::where('is_headquarters', true)->first();

        if (! $property) {
            $this->error('No property found. Create a property first or pass --property=<id>.');

            return self::FAILURE;
        }

        $this->info("Backfilling property_id = {$property->id} ({$property->name}) on all unassigned records...\n");

        $tables = [
            // Core
            'room_types',
            'room_units',

            // Frontdeskcrm
            'registrations',
            'rate_codes',
            'charge_types',
            'booking_sources',
            'guest_types',
            'channels',
            'night_audits',
            'corporate_accounts',

            // Website
            'bookings',

            // Restaurant
            'restaurant_tables',
            'restaurant_menu_categories',
            'restaurant_menu_items',
            'restaurant_orders',
            'restaurant_payments',
            'restaurant_customers',
            'restaurant_stock_items',
            'restaurant_settings',

            // Gym
            'subscription_configs',
            'trainers',
            'trainer_payments',
            'members',
            'memberships',
            'payments',

            // Staff
            'employees',
            'leave_requests',
            'leave_balances',
            'shifts',
            'attendance_logs',
            'shared_documents',
            'staff_settings',

            // Inventory
            'items',
            'stores',
            'suppliers',
            'departments',
            'purchase_requests',
            'purchase_orders',
            'stock_takes',
            'cycle_counts',
            'inventory_adjustments',
            'transfers',
            'stock_movements',
            'stock_alerts',
            'item_returns',
            'restock_logs',

            // Maintenance
            'maintenance_logs',
            'maintenance_readings',

            // Banquet
            'customers',
            'banquet_orders',
            'banquet_payments',
            'banquet_venues',
            'banquet_enquiries',

            // Tasks
            'tasks',

            // Housekeeping
            'housekeeping_logs',

            // Finance
            'finance_chart_of_accounts',
            'finance_journal_entries',
            'finance_journal_lines',
        ];

        $total = 0;
        $rows = [];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'property_id')) {
                continue;
            }

            $updated = DB::table($tableName)
                ->whereNull('property_id')
                ->update(['property_id' => $property->id]);

            if ($updated > 0) {
                $rows[] = [$tableName, number_format($updated)];
                $total += $updated;
            }
        }

        if ($rows !== []) {
            $this->table(['Table', 'Rows Updated'], $rows);
        }

        if ($total === 0) {
            $this->info('All records already have a property_id assigned. Nothing to backfill.');
        } else {
            $this->info("\nDone. Updated ".number_format($total).' total records across all tables.');
        }

        return self::SUCCESS;
    }
}
