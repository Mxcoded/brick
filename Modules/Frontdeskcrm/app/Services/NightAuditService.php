<?php

namespace Modules\Frontdeskcrm\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Models\NightAuditLog;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;

class NightAuditService
{
    protected RateCalculator $rateCalculator;

    protected ?NightAuditLog $currentAudit = null;

    protected bool $financePosting = false;

    public function __construct(RateCalculator $rateCalculator)
    {
        $this->rateCalculator = $rateCalculator;
    }

    public function enableFinancePosting(bool $enabled = true): self
    {
        $this->financePosting = $enabled;

        return $this;
    }

    public function process(?Carbon $businessDate = null, ?int $performedBy = null): NightAuditLog
    {
        $date = $businessDate ?: Carbon::today();

        $running = NightAuditLog::where('business_date', $date)
            ->where('status', 'running')
            ->first();

        if ($running) {
            throw new \RuntimeException("Night audit for {$date->format('Y-m-d')} is already in progress.");
        }

        $alreadyCompleted = NightAuditLog::where('business_date', $date)
            ->where('status', 'completed')
            ->exists();

        if ($alreadyCompleted) {
            throw new \RuntimeException("Night audit for {$date->format('Y-m-d')} has already been completed.");
        }

        $audit = NightAuditLog::create([
            'business_date' => $date,
            'started_at' => now(),
            'status' => 'running',
            'performed_by' => $performedBy,
        ]);

        $this->currentAudit = $audit;

        try {
            DB::transaction(function () use ($date, $audit) {
                $inHouse = Registration::where('stay_status', 'checked_in')
                    ->whereDate('check_in', '<=', $date)
                    ->whereDate('check_out', '>', $date)
                    ->get();

                $chargesPosted = 0;
                $totalRevenue = 0;

                foreach ($inHouse as $registration) {
                    $charge = $this->postNightlyCharge($registration, $date, $audit->id);
                    if ($charge) {
                        $chargesPosted++;
                        $totalRevenue += (float) $charge->amount;
                    }
                }

                $audit->update([
                    'rooms_occupied' => $inHouse->count(),
                    'charges_posted' => $chargesPosted,
                    'total_revenue_posted' => $totalRevenue,
                ]);
            });

            $audit->update([
                'completed_at' => now(),
                'status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            $audit->update([
                'completed_at' => now(),
                'status' => 'failed',
                'notes' => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->currentAudit = null;

        return $audit->fresh();
    }

    protected function postNightlyCharge(Registration $registration, Carbon $date, int $auditId): ?RegistrationCharge
    {
        $alreadyPosted = RegistrationCharge::where('registration_id', $registration->id)
            ->where('charge_type', 'room')
            ->whereDate('charge_date', $date)
            ->exists();

        if ($alreadyPosted) {
            return null;
        }

        $rate = (float) $registration->room_rate;

        if ($registration->rateCode) {
            $rate = $this->rateCalculator->calculate(
                $registration->rateCode,
                $date,
                $registration->guestType,
            );
        }

        $folio = app(FolioService::class)->ensureFolio($registration);

        $charge = RegistrationCharge::create([
            'registration_id' => $registration->id,
            'charge_type' => 'room',
            'description' => "Room charge for {$date->format('D, M d, Y')}",
            'amount' => $rate,
            'charge_date' => $date,
            'is_audited' => true,
            'night_audit_log_id' => $auditId,
            'folio_id' => $folio->id,
        ]);

        app(FolioService::class)->postCharge($folio, [
            'charge_type' => 'room',
            'description' => "Room charge for {$date->format('D, M d, Y')}",
            'amount' => $rate,
            'sourceable_type' => RegistrationCharge::class,
            'sourceable_id' => $charge->id,
            'post_date' => $date,
        ], null);

        $registration->increment('nights_posted');
        $registration->update(['last_audit_date' => $date]);

        return $charge;
    }

    public function getCurrentAudit(): ?NightAuditLog
    {
        return $this->currentAudit;
    }
}
