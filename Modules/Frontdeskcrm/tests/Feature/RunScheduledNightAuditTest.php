<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\FrontdeskSetting;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\NightAuditLog;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The scheduled `frontdesk:auto-charge` run.
 *
 * The switch lives in the settings table, so these tests exercise the two
 * behaviours that matter: doing nothing while the switch is off, and posting
 * the nightly room charges through the same service the manual screen uses
 * once it is on.
 */
class RunScheduledNightAuditTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCheckedInRegistration(): Registration
    {
        $roomType = RoomType::create([
            'name' => 'Auto Charge Room',
            'slug' => 'auto-charge-'.uniqid(),
            'price' => 50000.00,
            'capacity' => 2,
            'is_active' => true,
        ]);

        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'AUTO-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);

        $guest = Guest::create([
            'full_name' => 'Auto Charge Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'auto'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        return Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'nationality' => $guest->nationality,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000.00,
            'check_in' => Carbon::today()->subDays(2),
            'check_out' => Carbon::today()->addDays(2),
            'stay_status' => 'checked_in',
            'no_of_nights' => 4,
            'total_amount' => 200000.00,
        ]);
    }

    #[Test]
    public function it_does_nothing_while_automatic_charging_is_off(): void
    {
        FrontdeskSetting::setValue('auto_charge_enabled', '0');
        $this->makeCheckedInRegistration();

        $this->artisan('frontdesk:auto-charge')->assertExitCode(0);

        $this->assertSame(0, NightAuditLog::count());
        $this->assertSame(0, RegistrationCharge::count());
    }

    #[Test]
    public function it_posts_the_nightly_room_charges_when_enabled(): void
    {
        FrontdeskSetting::setValue('auto_charge_enabled', '1');
        $registration = $this->makeCheckedInRegistration();

        $this->artisan('frontdesk:auto-charge')->assertExitCode(0);

        $audit = NightAuditLog::where('status', 'completed')->firstOrFail();

        $this->assertSame(1, $audit->charges_posted);
        $this->assertSame(1, $audit->rooms_occupied);

        $charge = RegistrationCharge::where('registration_id', $registration->id)
            ->where('charge_type', 'room')
            ->firstOrFail();

        $this->assertSame(50000.0, (float) $charge->amount);
        $this->assertSame($audit->id, $charge->night_audit_log_id);
    }

    #[Test]
    public function a_second_run_on_the_same_day_is_a_clean_noop(): void
    {
        FrontdeskSetting::setValue('auto_charge_enabled', '1');
        $this->makeCheckedInRegistration();

        $this->artisan('frontdesk:auto-charge')->assertExitCode(0);
        $this->artisan('frontdesk:auto-charge')->assertExitCode(0);

        // The audit service refuses a duplicate business date; the command must
        // swallow that as a normal situation, not turn it into a failure.
        $this->assertSame(1, NightAuditLog::where('status', 'completed')->count());
        $this->assertSame(1, RegistrationCharge::count());
    }
}
