<?php

namespace Modules\Admin\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Modules\Website\Models\PaymentGateway;
use Tests\TestCase;

/**
 * Confirms the payment-gateway configuration UI/UX renders correctly and is
 * guarded by the manage_payment_gateways permission.
 */
class PaymentGatewayAdminUiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

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

        $response = $this->get(route('admin.payment-gateways.index'));

        $response->assertOk();
        $response->assertSee('Payment Gateways');
        $response->assertSee('Paystack');
        $response->assertSee('Stripe');
        $response->assertSee('Default');   // default badge on Paystack
        $response->assertSee('Deactivate'); // toggle action on active gateway
        $response->assertSee('Set Default'); // action on inactive Stripe
    }

    public function test_create_page_exposes_gateway_options_and_credential_fields(): void
    {
        $this->actingAs($this->adminUser());

        $response = $this->get(route('admin.payment-gateways.create'));

        $response->assertOk();
        $response->assertSee('Add Payment Gateway');
        // Gateway selector options
        $response->assertSee('paystack');
        $response->assertSee('stripe');
        // Credential field toggling script
        $response->assertSee('credential-fields');
        $response->assertSee('toggleFields');
    }

    public function test_store_persists_encrypted_credentials_and_redirects(): void
    {
        $this->actingAs($this->adminUser());

        $response = $this->post(route('admin.payment-gateways.store'), [
            'code' => 'stripe',
            'name' => 'Stripe',
            'is_active' => '1',
            'is_default' => '1',
            'credentials' => ['secret' => 'sk_live_abc', 'public' => 'pk_live_abc', 'webhook_secret' => 'whsec_abc'],
        ]);

        $response->assertRedirect(route('admin.payment-gateways.index'));
        $response->assertSessionHas('success');

        $gateway = PaymentGateway::where('code', 'stripe')->firstOrFail();
        // Credentials are encrypted at rest but decryptable.
        $this->assertEquals('sk_live_abc', $gateway->credential('secret'));
        $this->assertTrue($gateway->is_active);
        $this->assertTrue($gateway->is_default);
        // Only Stripe's fields are stored (driver whitelist enforced).
        $this->assertArrayNotHasKey('irrelevant', $gateway->credentials);
    }

    public function test_edit_page_prefills_credentials_without_exposing_plain_secret_issue(): void
    {
        $this->actingAs($this->adminUser());

        $gateway = PaymentGateway::create([
            'code' => 'paystack', 'name' => 'Paystack', 'driver' => 'paystack',
            'is_active' => true, 'is_default' => true,
            'credentials' => ['secret' => 'sk_test_existing', 'public' => 'pk_test_existing'],
        ]);

        $response = $this->get(route('admin.payment-gateways.edit', $gateway));

        $response->assertOk();
        $response->assertSee('Edit Payment Gateway');
        $response->assertSee('sk_test_existing'); // value prefilled
        $response->assertSee('Leave a field blank to keep its current value');
    }

    public function test_update_keeps_existing_secret_when_field_left_blank(): void
    {
        $this->actingAs($this->adminUser());

        $gateway = PaymentGateway::create([
            'code' => 'paystack', 'name' => 'Paystack', 'driver' => 'paystack',
            'is_active' => true, 'is_default' => true,
            'credentials' => ['secret' => 'sk_test_keepme', 'public' => 'pk_test_keepme'],
        ]);

        // Submit with blank secret/public → must preserve existing values.
        $response = $this->put(route('admin.payment-gateways.update', $gateway), [
            'name' => 'Paystack Updated',
            'credentials' => ['secret' => '', 'public' => ''],
        ]);

        $response->assertRedirect(route('admin.payment-gateways.index'));
        $gateway->refresh();
        $this->assertEquals('sk_test_keepme', $gateway->credential('secret'));
        $this->assertEquals('pk_test_keepme', $gateway->credential('public'));
        $this->assertEquals('Paystack Updated', $gateway->name);
    }

    public function test_unauthorized_user_is_forbidden_from_gateway_routes(): void
    {
        $this->actingAs($this->dashboardOnlyUser());

        $this->get(route('admin.payment-gateways.index'))->assertForbidden();
        $this->get(route('admin.payment-gateways.create'))->assertForbidden();
    }
}
