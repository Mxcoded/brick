<?php

namespace Modules\Frontdeskcrm\Tests\Unit;

use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\CityLedgerTransaction;
use Modules\Frontdeskcrm\Models\CorporateAccount;
use Modules\Frontdeskcrm\Models\FolioCharge;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\NightAudit;
use Modules\Frontdeskcrm\Models\NightAuditLog;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;

class ModelRelationshipTest extends ModuleTestCase
{
    public function test_registration_belongs_to_guest()
    {
        $guest = Guest::factory()->create();
        $registration = Registration::factory()->create(['guest_id' => $guest->id]);

        $this->assertTrue($registration->guest->is($guest));
    }

    public function test_registration_has_many_folio_charges()
    {
        $registration = Registration::factory()->create();
        $charge = FolioCharge::factory()->create(['registration_id' => $registration->id]);

        $this->assertTrue($registration->folioCharges->contains($charge));
    }

    public function test_registration_has_many_payments()
    {
        $registration = Registration::factory()->create();
        $payment = RegistrationPayment::factory()->create(['registration_id' => $registration->id]);

        $this->assertTrue($registration->payments->contains($payment));
    }

    public function test_folio_charge_belongs_to_charge_type()
    {
        $chargeType = ChargeType::factory()->create();
        $charge = FolioCharge::factory()->create(['charge_type_id' => $chargeType->id]);

        $this->assertTrue($charge->chargeType->is($chargeType));
    }

    public function test_night_audit_has_many_logs()
    {
        $audit = NightAudit::factory()->create();
        $log = NightAuditLog::factory()->create(['night_audit_id' => $audit->id]);

        $this->assertTrue($audit->logs->contains($log));
    }

    public function test_corporate_account_has_many_transactions()
    {
        $account = CorporateAccount::factory()->create();
        $transaction = CityLedgerTransaction::factory()->create(['corporate_account_id' => $account->id]);

        $this->assertTrue($account->transactions->contains($transaction));
    }

    public function test_corporate_account_available_credit_accessor()
    {
        $account = CorporateAccount::factory()->create([
            'credit_limit' => 100000,
            'current_balance' => 30000,
        ]);

        $this->assertEquals(70000, $account->available_credit);
    }

    public function test_registration_balance_accessor()
    {
        $registration = Registration::factory()->checkedIn()->create(['total_amount' => 50000]);

        FolioCharge::factory()->create([
            'registration_id' => $registration->id,
            'amount' => 10000,
        ]);

        RegistrationPayment::factory()->create([
            'registration_id' => $registration->id,
            'amount' => 20000,
        ]);

        $this->assertEquals(-10000, $registration->balance);
    }

    public function test_charge_type_active_scope()
    {
        ChargeType::factory()->create(['is_active' => true]);
        ChargeType::factory()->create(['is_active' => false]);

        $this->assertEquals(1, ChargeType::active()->count());
    }

    public function test_night_audit_scopes()
    {
        NightAudit::factory()->create(['status' => 'open']);
        NightAudit::factory()->completed()->create();

        $this->assertEquals(1, NightAudit::open()->count());
        $this->assertEquals(1, NightAudit::completed()->count());
    }

    public function test_registration_status_scopes()
    {
        Registration::factory()->pending()->create();
        Registration::factory()->checkedIn()->create();

        $this->assertEquals(2, Registration::count());
    }

    public function test_rate_code_active_scope()
    {
        RateCode::factory()->create(['is_active' => true]);
        RateCode::factory()->create(['is_active' => false]);

        $this->assertEquals(1, RateCode::active()->count());
    }
}
