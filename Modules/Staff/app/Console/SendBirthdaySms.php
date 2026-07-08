<?php

namespace Modules\Staff\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\StaffSetting;
use Modules\Staff\Services\BulkSmsNigeria;

class SendBirthdaySms extends Command
{
    protected $signature = 'staff:send-birthday-sms
                            {--dry-run : Preview employees without sending SMS}';

    protected $description = 'Send birthday SMS to employees whose birthday is today via BulkSMSNigeria';

    public function handle(BulkSmsNigeria $sms): int
    {
        if (! config('staff.birthday_sms_enabled')) {
            $this->warn('Birthday SMS is disabled via config.');

            return 0;
        }

        $apiToken = config('services.bulksmsnigeria.api_token');
        if (empty($apiToken)) {
            $this->error('BulkSMSNigeria API token is not set. Add BULKSMSNIGERIA_API_TOKEN to your .env.');

            return 1;
        }

        $today = Carbon::today();
        $monthDay = $today->format('m-d');

        $employees = Employee::query()
            ->whereNotNull('date_of_birth')
            ->whereNotNull('phone_number')
            ->whereNull('end_date')
            ->get()
            ->filter(fn (Employee $e) => Carbon::parse($e->date_of_birth)->format('m-d') === $monthDay);

        if ($employees->isEmpty()) {
            $this->info('No employees have a birthday today.');

            return 0;
        }

        $senderId = config('services.bulksmsnigeria.sender', 'Brickspoint');
        $this->line("Sender ID: {$senderId}");

        foreach ($employees as $employee) {
            $this->line("  Employee: {$employee->name} (DOB: {$employee->date_of_birth})");
        }

        $messageTemplate = StaffSetting::get('birthday_sms_message')
            ?? config('staff.birthday_sms_message');
        $sent = 0;
        $failed = 0;

        foreach ($employees as $employee) {
            $phone = BulkSmsNigeria::normalizePhone($employee->phone_number);

            if (! $phone) {
                $this->warn("Skipping {$employee->name}: no valid phone number.");
                $failed++;

                continue;
            }

            $personalizedMessage = str_replace(
                ['{name}', '{position}'],
                [$employee->name, $employee->position ?? ''],
                $messageTemplate
            );

            if ($this->option('dry-run')) {
                $this->line("Would send to {$employee->name} ({$phone}): {$personalizedMessage}");
                $sent++;

                continue;
            }

            $response = $sms->send($personalizedMessage, $phone);

            if ($this->option('verbose')) {
                $this->line('API response: '.json_encode($response));
            }

            if (! empty($response['success'])) {
                $this->info("Sent to {$employee->name} ({$phone})");
                $sent++;
            } else {
                $this->warn("Failed to send to {$employee->name} ({$phone}): ".($response['message'] ?? 'Unknown error'));
                $failed++;
            }
        }

        $this->line("Done. Sent: {$sent}, Failed: {$failed}");

        return 0;
    }
}
