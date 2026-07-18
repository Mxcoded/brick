<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Modules\Frontdeskcrm\Models\CityLedgerTransaction;
use Modules\Frontdeskcrm\Models\CorporateAccount;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;

class CityLedgerTest extends ModuleTestCase
{
    private User $user;

    private CorporateAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createAuthenticatedUser();
        $this->account = CorporateAccount::factory()->create([
            'credit_limit' => 500000,
            'current_balance' => 0,
        ]);
    }

    public function test_can_create_corporate_account()
    {
        $response = $this->post(route('frontdesk.city-ledger.store'), [
            'company_name' => 'ACME Corp',
            'contact_person' => 'Jane Doe',
            'email' => 'jane@acme.com',
            'phone' => '08098765432',
            'payment_terms' => 'net_30',
            'credit_limit' => 1000000,
            'is_active' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('corporate_accounts', ['company_name' => 'ACME Corp']);
    }

    public function test_can_record_payment()
    {
        $transaction = CityLedgerTransaction::factory()->charge()->create([
            'corporate_account_id' => $this->account->id,
            'amount' => 50000,
        ]);

        $response = $this->post(route('frontdesk.city-ledger.payment', $this->account), [
            'amount' => 30000,
            'description' => 'Partial payment',
            'reference' => 'PAY-001',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_can_charge_registration_to_account()
    {
        $registration = Registration::factory()->checkedIn()->create([
            'corporate_account_id' => $this->account->id,
            'total_amount' => 75000,
        ]);

        $response = $this->post(route('frontdesk.city-ledger.charge', $this->account), [
            'registration_id' => $registration->id,
            'amount' => 75000,
            'description' => 'Room charge',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_enforces_credit_limit()
    {
        $account = CorporateAccount::factory()->create([
            'credit_limit' => 50000,
            'current_balance' => 45000,
        ]);

        $registration = Registration::factory()->checkedIn()->create([
            'corporate_account_id' => $account->id,
            'total_amount' => 10000,
        ]);

        $response = $this->post(route('frontdesk.registrations.checkout', $registration), [
            'billing_to_account' => '1',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_can_view_corporate_account_dashboard()
    {
        CorporateAccount::factory()->count(3)->create();

        $response = $this->get(route('frontdesk.city-ledger.index'));

        $response->assertOk();
        $response->assertViewHas('accounts');
    }
}
