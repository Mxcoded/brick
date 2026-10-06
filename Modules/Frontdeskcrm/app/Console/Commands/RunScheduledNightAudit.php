<?php

namespace Modules\Frontdeskcrm\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Frontdeskcrm\Models\FrontdeskSetting;
use Modules\Frontdeskcrm\Services\NightAuditService;

/**
 * The automatic nightly room-charge run.
 *
 * Registered on the scheduler at the time stored in `auto_charge_time`. The
 * command itself only guards the `auto_charge_enabled` switch, so the schedule
 * stays registered even while the feature is off - the scheduler answers "when
 * should this run?" statically and this command answers "may it actually post
 * money today?" at runtime, which is the only way a toggle can take effect
 * without the scheduler being re-registered.
 *
 * Runs the same service the manual night-audit screen does, so an automatic
 * run and a hand-run audit for the same date can never disagree.
 */
class RunScheduledNightAudit extends Command
{
    protected $signature = 'frontdesk:auto-charge';

    protected $description = 'Post nightly room charges automatically, gated by the PMS setting.';

    public function handle(NightAuditService $nightAudit): int
    {
        if ((string) FrontdeskSetting::getValue('auto_charge_enabled', '0') !== '1') {
            $this->info('Automatic room charging is disabled in PMS settings.');

            return self::SUCCESS;
        }

        try {
            $audit = $nightAudit->process(Carbon::today());

            $this->info(
                'Night audit for '.$audit->business_date->format('Y-m-d')
                .' completed: '.$audit->charges_posted.' charges, '
                .number_format((float) $audit->total_revenue_posted, 2).' posted.'
            );

            return self::SUCCESS;
        } catch (\RuntimeException $e) {
            // Already completed or already in progress - nothing to do, and the
            // scheduler should not treat a duplicate attempt as a failure.
            $this->warn($e->getMessage());

            return self::SUCCESS;
        }
    }
}
