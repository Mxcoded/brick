<?php

namespace Modules\Finance\Exceptions;

use Exception;
use Modules\Finance\Models\FiscalPeriod;

class FiscalPeriodLockedException extends Exception
{
    public static function forPeriod(FiscalPeriod $period): self
    {
        return new self(sprintf(
            'The accounting period "%s" (%s to %s) is locked. Unlock it before posting to these dates.',
            $period->name,
            $period->start_date->toFormattedDateString(),
            $period->end_date->toFormattedDateString(),
        ));
    }

    public static function forDate(string $date): self
    {
        return new self("The accounting period containing $date is locked.");
    }
}
