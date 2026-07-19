<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'employees',
            'leave_requests',
            'leave_balances',
            'shifts',
            'attendance_logs',
            'shared_documents',
            'staff_settings',
        ];

        foreach ($tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            if (! Schema::hasColumn($tbl, 'property_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->foreignId('property_id')->nullable()->after('id')->constrained()->nullOnDelete();
                    $table->index('property_id');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'employees',
            'leave_requests',
            'leave_balances',
            'shifts',
            'attendance_logs',
            'shared_documents',
            'staff_settings',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['property_id']);
                $table->dropIndex(['property_id']);
                $table->dropColumn('property_id');
            });
        }
    }
};
