<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\NightAuditLog;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Services\NightAuditService;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Tests\TestCase;

class NightAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected NightAuditService $nightAudit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->nightAudit = app(NightAuditService::class);
    }

    private function makeCheckedInRegistration(array $overrides = []): Registration
    {
        $roomType = RoomType::create([
            'name' => 'Audit Room',
            'slug' => 'audit-room-'.uniqid(),
            'price' => 50000.00,
            'capacity' => 2,
            'is_active' => true,
        ]);

        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'AUDIT-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);

        $guest = Guest::create([
            'full_name' => 'Audit Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'audit'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        return Registration::create(array_merge([
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
        ], $overrides));
    }

    public function test_process_creates_audit_log(): void
    {
        $this->makeCheckedInRegistration();

        $audit = $this->nightAudit->process(Carbon::today());

        $this->assertInstanceOf(NightAuditLog::class, $audit);
        $this->assertEquals('completed', $audit->status);
        $this->assertEquals(1, $audit->rooms_occupied);
        $this->assertEquals(1, $audit->charges_posted);
    }

    public function test_process_posts_room_charge_for_in_house_guest(): void
    {
        $registration = $this->makeCheckedInRegistration();

        $this->nightAudit->process(Carbon::today());

        $charge = RegistrationCharge::where('registration_id', $registration->id)->first();
        $this->assertNotNull($charge);
        $this->assertEquals('room', $charge->charge_type);
        $this->assertEquals(50000.00, $charge->amount);
        $this->assertTrue($charge->is_audited);
    }

    public function test_charge_not_duplicated_on_second_run(): void
    {
        $this->makeCheckedInRegistration();

        $this->nightAudit->process(Carbon::today());
        $countBefore = RegistrationCharge::count();

        $this->expectException(\RuntimeException::class);
        $this->nightAudit->process(Carbon::today());

        $this->assertEquals($countBefore, RegistrationCharge::count());
    }

    public function test_increments_nights_posted(): void
    {
        $registration = $this->makeCheckedInRegistration();
        $this->assertEquals(0, $registration->nights_posted);

        $this->nightAudit->process(Carbon::today());

        $this->assertEquals(1, $registration->fresh()->nights_posted);
        $this->assertNotNull($registration->fresh()->last_audit_date);
    }

    public function test_skips_already_charged_date(): void
    {
        $registration = $this->makeCheckedInRegistration();

        $this->nightAudit->process(Carbon::today());

        $this->expectException(\RuntimeException::class);
        $this->nightAudit->process(Carbon::today());
    }

    public function test_no_charges_for_no_in_house_guests(): void
    {
        $audit = $this->nightAudit->process(Carbon::today());

        $this->assertEquals(0, $audit->rooms_occupied);
        $this->assertEquals(0, $audit->charges_posted);
        $this->assertEquals(0, $audit->total_revenue_posted);
    }

    public function test_calculates_rate_from_rate_code_during_audit(): void
    {
        $rateCode = RateCode::create([
            'code' => 'AUDITRC',
            'name' => 'Audit Rate',
            'default_rate' => 35000.00,
            'min_los' => 1,
        ]);

        $this->makeCheckedInRegistration([
            'rate_code_id' => $rateCode->id,
            'room_rate' => 35000.00,
        ]);

        $this->nightAudit->process(Carbon::today());

        $charge = RegistrationCharge::first();
        $this->assertEquals(35000.00, $charge->amount);
    }
}
