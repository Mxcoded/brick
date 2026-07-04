<?php

namespace Modules\Staff\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Staff\Models\HikvisionAttendanceRecord;
use Modules\Staff\Services\HikvisionService;

class ImportHikvisionAttendance extends Command
{
    protected $signature = 'attendance:import-hikvision
        {--from= : Start datetime (Y-m-d H:i:s). Defaults to last imported + 1 minute, or today 00:00:00}
        {--to= : End datetime (Y-m-d H:i:s). Defaults to now}
        {--dry-run : Preview records without importing}';

    protected $description = 'Import attendance records from Hikvision machine via ISAPI';

    public function handle(HikvisionService $hikvision): int
    {
        if (! $hikvision->isConfigured()) {
            $this->error('Hikvision machine is not configured. Set IP, username, and password in Staff Settings.');

            return 1;
        }

        $latest = HikvisionAttendanceRecord::latest('punch_time')->first();
        $from = $this->option('from')
            ? Carbon::parse($this->option('from'))
            : ($latest ? $latest->punch_time->addMinute() : now()->startOfDay());

        $to = $this->option('to')
            ? Carbon::parse($this->option('to'))
            : now();

        if ($from->greaterThan($to)) {
            $this->error('--from must be before --to.');

            return 1;
        }

        $this->info("Fetching Hikvision records from {$from} to {$to}...");

        $records = $hikvision->fetchAttendanceRecords($from, $to);
        $this->line("Found {$records->count()} record(s).");

        if ($records->isEmpty()) {
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->table(
                ['UID', 'PIN', 'Time', 'Status'],
                $records->map(fn ($r) => [
                    $r['uid'],
                    $r['pin'],
                    $r['time']?->format('Y-m-d H:i:s'),
                    $r['status'],
                ])
            );

            return 0;
        }

        $result = $hikvision->importFetchedRecords($records);

        $this->info("Imported: {$result['imported']}, Skipped (duplicates): {$result['skipped']}, Matched to employees: {$result['matched_employees']}");

        if ($result['matched_employees'] < $result['imported']) {
            $this->warn('Some records did not match an employee biometric_pin. Check employee biometric_pin settings.');
        }

        return 0;
    }
}
