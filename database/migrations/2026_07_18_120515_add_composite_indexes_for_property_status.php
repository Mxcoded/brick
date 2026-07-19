<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            'tasks' => ['property_id', 'status'],
            'night_audits' => ['property_id', 'status'],
            'registrations' => ['property_id', 'stay_status'],
            'restaurant_orders' => ['property_id', 'status'],
            'restaurant_payments' => ['property_id', 'status'],
            'banquet_orders' => ['property_id', 'status'],
            'banquet_enquiries' => ['property_id', 'status'],
            'employees' => ['property_id', 'status'],
            'leave_requests' => ['property_id', 'status'],
            'attendance_logs' => ['property_id', 'status'],
            'maintenance_logs' => ['property_id', 'status'],
            'finance_journal_entries' => ['property_id', 'status'],
            'bookings' => ['property_id', 'status'],
            'purchase_requests' => ['property_id', 'status'],
            'purchase_orders' => ['property_id', 'status'],
            'stock_takes' => ['property_id', 'status'],
            'cycle_counts' => ['property_id', 'status'],
            'room_units' => ['property_id', 'status'],
        ];

        foreach ($indexes as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasIndex($table, $columns)) {
                continue;
            }
            Schema::table($table, function (Blueprint $table) use ($columns) {
                $table->index($columns);
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'tasks' => ['property_id', 'status'],
            'night_audits' => ['property_id', 'status'],
            'registrations' => ['property_id', 'stay_status'],
            'restaurant_orders' => ['property_id', 'status'],
            'restaurant_payments' => ['property_id', 'status'],
            'banquet_orders' => ['property_id', 'status'],
            'banquet_enquiries' => ['property_id', 'status'],
            'employees' => ['property_id', 'status'],
            'leave_requests' => ['property_id', 'status'],
            'attendance_logs' => ['property_id', 'status'],
            'maintenance_logs' => ['property_id', 'status'],
            'finance_journal_entries' => ['property_id', 'status'],
            'bookings' => ['property_id', 'status'],
            'purchase_requests' => ['property_id', 'status'],
            'purchase_orders' => ['property_id', 'status'],
            'stock_takes' => ['property_id', 'status'],
            'cycle_counts' => ['property_id', 'status'],
            'room_units' => ['property_id', 'status'],
        ];

        foreach ($indexes as $table => $columns) {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                $table->dropIndex($columns);
            });
        }
    }
};
