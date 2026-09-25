<?php

namespace Modules\Admin\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Modules\Admin\Filament\Resources\PaymentGateways\Pages\CreatePaymentGateway;
use Modules\Admin\Filament\Resources\PaymentGateways\Pages\EditPaymentGateway;
use Modules\Admin\Filament\Resources\PaymentGateways\Pages\ListPaymentGateways;
use Modules\Website\Models\PaymentGateway;
use Tests\TestCase;

/**
 * Confirms the Filament payment-gateway resource renders correctly and is
 * guarded by the manage_payment_gateways permission.
 */
class PaymentGatewayAdminUiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Isolate from any gateway rows committed by other suites in the shared test DB.
        PaymentGateway::query()->delete();
    }

    private function adminUser(): User
    {
        $user = User::create([
            'name' => 'Gateway Admin',
            'email' => 'gwadmin'.uniqid().'@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('admin'); // seeded with manage_payment_gateways + access_admin_dashboard

        return $user;
    }

    private function dashboardOnlyUser(): User
    {
        $user = User::create([
            'name' => 'Limited Admin',
            'email' => 'limited'.uniqid().'@example.com',
            'password' => Hash::make('password'),
        ]);
        // Has admin dashboard access but NOT the gateway permission.
        $user->givePermissionTo('access_admin_dashboard');

        return $user;
    }

    public function test_index_page_lists_gateways_with_status_and_default_controls(): void
    {
        $this->actingAs($this->adminUser());

        // One default gateway and one inactive, non-default gateway so both the
        // "Default" badge and the "Set Default" action are exercised.
        PaymentGateway::create([
            'code' => 'paystack', 'name' => 'Paystack', 'driver' => 'paystack',
            'is_active' => true, 'is_default' => true,
            'credentials' => ['secret' => 'sk_test_x', 'public' => 'pk_test_x'],
        ]);
        PaymentGateway::create([
            'code' => 'stripe', 'name' => 'Stripe', 'driver' => 'stripe',
            'is_active' => false, 'is_default' => false,
            'credentials' => ['secret' => 'sk_live_x', 'public' => 'pk_live_x'],
        ]);

        $component = Livewire::test(ListPaymentGateways::class);

        $component->assertOk();
        $component->assertTableColumnExists('name');
        $component->assertCanSeeTableRecords(PaymentGateway::all());
        $component->assertTableActionVisible('set-default'); // Stripe (not default)
        $component->assertTableActionVisible('toggle-active'); // both gateways
    }

    public function test_create_page_exposes_gateway_options_and_credential_fields(): void
    {
        $this->actingAs($this->adminUser());

        $component = Livewire::test(CreatePaymentGateway::class);

        // Gateway selector options + base fields render.
        $component->assertFormFieldExists('code');
        $component->assertFormFieldExists('name');
        $component->assertFormFieldExists('is_active');
        $component->assertFormFieldExists('is_default');

        // Credential fields are only shown for the currently selected driver.
        $component->assertFormFieldHidden('credentials.secret');
        $component->set('data.code', 'stripe')->assertOk();
        $component->assertFormFieldVisible('credentials.secret');
        $component->set('data.code', 'paystack')->assertOk();
        $component->assertFormFieldVisible('credentials.secret');

        // Stripe's webhook_secret must not appear for Paystack.
        $component->assertFormFieldHidden('credentials.webhook_secret');
    }

    public function test_store_persists_encrypted_credentials_and_syncs_default(): void
    {
        $this->actingAs($this->adminUser());

        Livewire::test(CreatePaymentGateway::class)
            ->fillForm([
                'code' => 'stripe',
                'name' => 'Stripe',
                'is_active' => true,
                'is_default' => true,
                'credentials' => [
                    'secret' => 'sk_live_abc',
                    'public' => 'pk_live_abc',
                    'webhook_secret' => 'whsec_abc',
                    'irrelevant' => 'nope',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $gateway = PaymentGateway::where('code', 'stripe')->firstOrFail();
        $this->assertEquals('stripe', $gateway->driver);
        // Credentials are encrypted at rest but decryptable.
        $this->assertEquals('sk_live_abc', $gateway->credential('secret'));
        $this->assertTrue($gateway->is_active);
        $this->assertTrue($gateway->is_default);

        // Only Stripe's fields are stored (driver whitelist enforced).
        $this->assertArrayNotHasKey('irrelevant', $gateway->credentials);
        $this->assertArrayHasKey('webhook_secret', $gateway->credentials);
        $this->assertSame(['secret', 'public', 'webhook_secret'], array_keys($gateway->credentials));
    }

    public function test_edit_page_does_not_expose_existing_secret_and_preserves_blank_fields(): void
    {
        $this->actingAs($this->adminUser());

        $gateway = PaymentGateway::create([
            'code' => 'paystack', 'name' => 'Paystack', 'driver' => 'paystack',
            'is_active' => true, 'is_default' => true,
            'credentials' => ['secret' => 'sk_test_existing', 'public' => 'pk_test_existing'],
        ]);

        $component = Livewire::test(EditPaymentGateway::class, ['record' => $gateway->getKey()]);

        // Legacy edit pre-filled secrets; the Filament edit deliberately starts
        // credential fields blank so stored secrets are never re-exposed.
        $component->assertSchemaComponentStateSet('credentials.secret', '');
        $component->assertSchemaComponentStateSet('credentials.public', '');

        // Saving with blank credential fields must preserve existing values.
        Livewire::test(EditPaymentGateway::class, ['record' => $gateway->getKey()])
            ->fillForm(['name' => 'Paystack Updated'])
            ->call('save')
            ->assertHasNoFormErrors();

        $gateway->refresh();
        $this->assertEquals('sk_test_existing', $gateway->credential('secret'));
        $this->assertEquals('pk_test_existing', $gateway->credential('public'));
        $this->assertEquals('Paystack Updated', $gateway->name);
    }

    public function test_update_keeps_existing_secret_when_field_left_blank(): void
    {
        $this->actingAs($this->adminUser());

        $gateway = PaymentGateway::create([
            'code' => 'paystack', 'name' => 'Paystack', 'driver' => 'paystack',
            'is_active' => true, 'is_default' => true,
            'credentials' => ['secret' => 'sk_test_keepme', 'public' => 'pk_test_keepme'],
        ]);

        // Partial fill: only the secret is replaced, the blank public key stays.
        Livewire::test(EditPaymentGateway::class, ['record' => $gateway->getKey()])
            ->fillForm(['credentials' => ['secret' => 'sk_test_new', 'public' => '']])
            ->call('save')
            ->assertHasNoFormErrors();

        $gateway->refresh();
        $this->assertEquals('sk_test_new', $gateway->credential('secret'));
        $this->assertEquals('pk_test_keepme', $gateway->credential('public'));
    }

    public function test_set_default_action_promotes_gateway_and_deactivates_others(): void
    {
        $this->actingAs($this->adminUser());

        $paystack = PaymentGateway::create([
            'code' => 'paystack', 'name' => 'Paystack', 'driver' => 'paystack',
            'is_active' => true, 'is_default' => true,
        ]);
        $stripe = PaymentGateway::create([
            'code' => 'stripe', 'name' => 'Stripe', 'driver' => 'stripe',
            'is_active' => false, 'is_default' => false,
        ]);

        Livewire::test(ListPaymentGateways::class)
            ->callTableAction('set-default', $stripe->getKey())
            ->assertHasNoTableActionErrors();

        $this->assertSame(1, PaymentGateway::where('is_default', true)->count());
        $this->assertTrue($stripe->refresh()->is_default);
        $this->assertTrue($stripe->refresh()->is_active);
    }

    public function test_toggle_active_action_flips_status(): void
    {
        $this->actingAs($this->adminUser());

        $gateway = PaymentGateway::create([
            'code' => 'stripe', 'name' => 'Stripe', 'driver' => 'stripe',
            'is_active' => false, 'is_default' => false,
        ]);

        Livewire::test(ListPaymentGateways::class)
            ->callTableAction('toggle-active', $gateway->getKey())
            ->assertHasNoTableActionErrors();

        $this->assertTrue($gateway->refresh()->is_active);
    }

    public function test_unauthorized_user_is_forbidden_from_gateway_routes(): void
    {
        $this->actingAs($this->dashboardOnlyUser());

        $this->get('/admin/payment-gateways')->assertForbidden();
        $this->get('/admin/payment-gateways/create')->assertForbidden();
    }
}
