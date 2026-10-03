<?php

namespace Modules\Restaurant\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\Table;
use Tests\TestCase;

class OnlineOrderTest extends TestCase
{
    use DatabaseTransactions;

    private MenuItem $burger;

    private MenuItem $fries;

    private bool $paystackStubbed = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $category = MenuCategory::create(['name' => 'Main Courses']);
        $this->burger = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Burger',
            'price' => 1500.00,
            'is_available' => true,
        ]);
        $this->fries = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Fries',
            'price' => 800.00,
            'is_available' => true,
        ]);
    }

    /**
     * Stub a successful Paystack charge.
     *
     * Registered lazily rather than in setUp(), because Laravel's Http fake
     * accumulates URL stubs and answers with the first match. A test that needs
     * a declined or underpaid charge fakes the gateway itself, and a stub added
     * earlier would shadow it.
     */
    private function stubPaystackSuccess(): void
    {
        if ($this->paystackStubbed) {
            return;
        }
        $this->paystackStubbed = true;

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 1_000_000_00, // kobo: ₦1,000,000
                    'channel' => 'card',
                ],
            ]),
        ]);
    }

    private function submitOnlineOrder(array $data, ?string $reference = null): Order
    {
        $this->stubPaystackSuccess();

        $response = $this->post('/restaurant/online/order/submit', array_merge([
            'payment_reference' => $reference ?? 'TEST-'.uniqid(),
        ], $data));
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        preg_match('/\/order\/confirm\/(\d+)$/', $location, $matches);

        return Order::findOrFail((int) $matches[1]);
    }

    private function addToCart(MenuItem $item, int $quantity = 1, string $instructions = ''): void
    {
        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $item->id,
            'quantity' => $quantity,
            'instructions' => $instructions,
        ])->assertOk();
    }

    private function customerDetails(): array
    {
        return [
            'customer_name' => 'John Doe',
            'customer_phone' => '08012345678',
            'delivery_address' => '123 Main Street, Lagos',
        ];
    }

    public function test_resubmitting_the_same_checkout_form_does_not_create_a_second_order(): void
    {
        $this->addToCart($this->burger, 2);

        $first = $this->submitOnlineOrder([
            ...$this->customerDetails(),
            'idempotency_key' => 'fixed-key-abc',
        ]);

        // A double-tap on Place Order replays the identical request.
        $replay = $this->post('/restaurant/online/order/submit', [
            ...$this->customerDetails(),
            'idempotency_key' => 'fixed-key-abc',
        ]);

        $replay->assertRedirectContains("/order/confirm/{$first->id}");
        $this->assertSame(1, Order::where('type', 'online')->count());
    }

    public function test_a_reused_key_with_a_changed_basket_places_the_new_order(): void
    {
        $this->addToCart($this->burger, 2);

        $first = $this->submitOnlineOrder([
            ...$this->customerDetails(),
            'idempotency_key' => 'fixed-key-abc',
        ]);

        // The customer goes back, adds another dish, and submits again from a
        // stale page that still carries the original key. That is a real second
        // order, so it must not be swallowed as a duplicate.
        $this->addToCart($this->fries, 1);

        $second = $this->submitOnlineOrder([
            ...$this->customerDetails(),
            'idempotency_key' => 'fixed-key-abc',
        ]);

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame(2, Order::where('type', 'online')->count());
    }

    public function test_checkout_page_issues_a_fresh_idempotency_key_each_visit(): void
    {
        $this->addToCart($this->burger);

        $first = $this->get('/restaurant/online/cart')->assertOk();
        $second = $this->get('/restaurant/online/cart')->assertOk();

        preg_match('/name="idempotency_key"\s+value="([^"]+)"/', $first->getContent(), $a);
        preg_match('/name="idempotency_key"\s+value="([^"]+)"/', $second->getContent(), $b);

        $this->assertNotEmpty($a[1] ?? null, 'Checkout form must carry an idempotency key.');
        $this->assertNotSame($a[1], $b[1] ?? null);
    }

    public function test_checkout_page_shows_a_total_that_includes_vat(): void
    {
        $this->addToCart($this->burger, 2);

        $response = $this->get('/restaurant/online/cart')->assertOk();

        // 2 x 1500 = 3000 of food. The payable figure has to be the one that
        // includes VAT, or the button quotes a number the customer never sees
        // charged.
        $rate = (float) RestaurantSetting::getValue('vat_rate', '7.5');
        $expected = 3000 * (1 + $rate / 100);

        $this->assertStringContainsString(
            number_format($expected, 2),
            $response->getContent(),
            'Checkout must state the amount actually payable.'
        );
    }

    public function test_menu_hides_sold_out_dishes_from_public_customers(): void
    {
        $this->burger->is_available = false;
        $this->burger->save();

        // A customer browsing the public menu is not shown a dish the kitchen
        // cannot currently serve.
        $this->get('/restaurant/online/menu')->assertOk()->assertDontSee('Burger');

        // Dine-in is no longer a public flow. The table menu URL does not
        // resolve for a visitor at all, so there is nothing there to show or
        // hide; the waiter terminal (tested in WaiterPosTest) keeps the full
        // list for staff.
        $table = Table::create(['number' => 'T-'.uniqid()]);
        $this->get("/restaurant/table/{$table->id}/menu")->assertNotFound();
    }

    public function test_customer_can_add_to_cart(): void
    {
        $response = $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id,
            'quantity' => 2,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertCount(1, $response->json('cart'));
        $this->assertEquals('Burger', $response->json('cart.0.name'));
    }

    public function test_the_basket_panel_read_returns_the_cart_the_customer_just_built(): void
    {
        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id,
            'quantity' => 2,
        ])->assertOk();

        // The "My order" panel re-reads the basket over this route every time it
        // opens and renders whatever comes back. If this answer is not JSON in
        // the documented shape the panel has nothing to draw, so it can only
        // sit on its loading state or claim the basket is empty.
        $response = $this->getJson('/restaurant/online/getcart');

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertIsArray($response->json('cart'));
        $this->assertCount(1, $response->json('cart'));
        $this->assertEquals('Burger', $response->json('cart.0.name'));
        $this->assertEquals(2, $response->json('cart.0.quantity'));
    }

    public function test_the_basket_panel_read_is_empty_rather_than_broken_for_a_new_visitor(): void
    {
        // An empty basket has to arrive as an empty list, not as a null or a
        // missing key. The panel counts items, so anything else renders as a
        // panel that refuses to draw either its empty state or its contents.
        $this->getJson('/restaurant/online/getcart')
            ->assertOk()
            ->assertJson(['success' => true, 'cart' => []]);
    }

    public function test_cannot_add_unavailable_item_to_cart(): void
    {
        $this->burger->is_available = false;
        $this->burger->save();

        $response = $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_customer_can_submit_online_order(): void
    {
        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 2,
        ]);
        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->fries->id, 'quantity' => 1,
        ]);

        $order = $this->submitOnlineOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '08012345678',
            'delivery_address' => '123 Main Street, Lagos',
        ]);

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $order->id,
            'type' => 'online',
            'customer_name' => 'John Doe',
            'customer_phone' => '08012345678',
            'delivery_address' => '123 Main Street, Lagos',
            'status' => 'pending',
            'tracking_status' => 'pending',
        ]);

        $this->assertDatabaseHas('restaurant_order_items', [
            'restaurant_order_id' => $order->id,
            'restaurant_menu_item_id' => $this->burger->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('restaurant_order_items', [
            'restaurant_order_id' => $order->id,
            'restaurant_menu_item_id' => $this->fries->id,
            'quantity' => 1,
        ]);
    }

    public function test_online_order_has_financial_totals(): void
    {
        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 2,
        ]);

        $order = $this->submitOnlineOrder([
            'customer_name' => 'Jane Doe',
            'customer_phone' => '08098765432',
            'delivery_address' => '456 Oak Avenue, Abuja',
        ]);

        $this->assertEquals(3000, (float) $order->subtotal);
        $this->assertEquals(7.5, (float) $order->vat_rate);
        $this->assertEquals(225, (float) $order->vat);
        $this->assertEquals(3225, (float) $order->grand_total);
    }

    public function test_checkout_page_loads_the_gateway_widget_and_a_reference_field(): void
    {
        $this->addToCart($this->burger, 1);

        $response = $this->get('/restaurant/online/cart')->assertOk();

        // The customer cannot pay without the widget, and the server cannot
        // verify a payment it was never told about.
        $response->assertSee('js.paystack.co/v1/inline.js', false);
        $response->assertSee('name="payment_reference"', false);
    }

    public function test_online_order_without_a_payment_reference_is_rejected(): void
    {
        $this->addToCart($this->burger, 1);

        $response = $this->post('/restaurant/online/order/submit', $this->customerDetails());

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::where('type', 'online')->count());
    }

    public function test_online_order_records_the_verified_gateway_payment(): void
    {
        $this->addToCart($this->burger, 2);

        $reference = 'TEST-VERIFIED-PAYMENT';
        $order = $this->submitOnlineOrder([
            ...$this->customerDetails(),
            'payment_reference' => $reference,
        ]);

        $payment = Payment::where('restaurant_order_id', $order->id)->first();

        $this->assertNotNull($payment, 'A verified online payment must be recorded against the order.');
        $this->assertSame('card', $payment->method);
        $this->assertSame('completed', $payment->status);
        $this->assertSame($reference, $payment->reference);
        $this->assertEquals((float) $order->grand_total, (float) $payment->amount);
    }

    /**
     * The gateway reports the rail the money travelled on. A bank transfer must
     * not be filed as a card, or the till report and the ledger misstate how
     * the day's takings arrived.
     */
    public function test_a_bank_transfer_channel_is_recorded_as_a_transfer(): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 1_000_000_00,
                    'channel' => 'bank_transfer',
                ],
            ]),
        ]);

        $this->addToCart($this->burger, 1);

        $this->post('/restaurant/online/order/submit', [
            ...$this->customerDetails(),
            'payment_reference' => 'TEST-BANK-TRANSFER',
        ])->assertRedirect();

        $this->assertSame(
            'transfer',
            Payment::where('reference', 'TEST-BANK-TRANSFER')->value('method')
        );
    }

    /**
     * Paystack will happily verify the same successful charge twice, so the
     * reference, not the gateway response, is what stops one payment from
     * buying two orders.
     */
    public function test_a_payment_reference_cannot_settle_a_second_order(): void
    {
        $reference = 'TEST-REPLAY-PAYMENT';

        $this->addToCart($this->burger, 1);
        $this->submitOnlineOrder([
            ...$this->customerDetails(),
            'payment_reference' => $reference,
        ]);

        // A changed basket, so the idempotency guard does not answer for it and
        // the request reaches the payment check.
        $this->addToCart($this->fries, 1);

        $response = $this->post('/restaurant/online/order/submit', [
            ...$this->customerDetails(),
            'payment_reference' => $reference,
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(1, Order::where('type', 'online')->count());
    }

    public function test_a_failed_payment_places_no_order(): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'failed', 'amount' => 1_000_000_00],
            ]),
        ]);

        $this->addToCart($this->burger, 1);

        $response = $this->post('/restaurant/online/order/submit', [
            ...$this->customerDetails(),
            'payment_reference' => 'TEST-FAILED',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::where('type', 'online')->count());
    }

    public function test_an_underpaid_reference_is_rejected(): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                // ₦1 against a bill of hundreds: a successful charge that does
                // not actually cover this order must not buy it.
                'data' => ['status' => 'success', 'amount' => 100],
            ]),
        ]);

        $this->addToCart($this->burger, 1);

        $response = $this->post('/restaurant/online/order/submit', [
            ...$this->customerDetails(),
            'payment_reference' => 'TEST-UNDERPAID',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::where('type', 'online')->count());
    }

    /**
     * Paying up front is not the same as being finished. The kitchen still has
     * to cook and deliver, so a paid online order must stay on the board.
     */
    public function test_a_paid_online_order_stays_on_the_kitchen_board(): void
    {
        $order = $this->placeOrder($this->customerDetails())->fresh();

        $this->assertTrue($order->is_fully_paid);
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->tracking_status);
        $this->assertDatabaseHas('restaurant_payments', [
            'restaurant_order_id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_customer_can_view_their_own_order_history(): void
    {
        $this->placeOrder([
            'customer_name' => 'Tolu',
            'customer_phone' => '08011111111',
            'delivery_address' => '10 Downing Street',
        ]);

        $response = $this->post('/restaurant/online/orders', [
            'customer_phone' => '08011111111',
        ]);

        $response->assertOk();
        $response->assertSee('Tolu');
        $response->assertSee('08011111111');
        $response->assertSee('10 Downing Street');
    }

    /**
     * The lookup endpoint hands back real customer details for a phone number,
     * so it must only answer for orders the requesting session placed. Any
     * other order for the same phone belongs to a different guest and has to
     * stay hidden.
     */
    public function test_order_history_does_not_expose_other_guests_orders(): void
    {
        $mine = $this->placeOrder([
            'customer_name' => 'Tolu Mine',
            'customer_phone' => '08011111111',
            'delivery_address' => '10 Downing Street',
        ]);

        // A different guest used the same phone on an earlier visit.
        $theirs = Order::create([
            'type' => 'online',
            'status' => 'completed',
            'tracking_status' => 'paid',
            'customer_name' => 'Someone Else',
            'customer_phone' => '08011111111',
            'delivery_address' => '99 Secret Lane',
            'subtotal' => 900,
            'grand_total' => 900,
        ]);

        $response = $this->post('/restaurant/online/orders', [
            'customer_phone' => '08011111111',
        ]);

        $response->assertOk();
        $response->assertSee('Tolu Mine');
        $response->assertDontSee('Someone Else');
        $response->assertDontSee('99 Secret Lane');
        $this->assertStringNotContainsString((string) $theirs->id, $response->getContent());
        $this->assertStringContainsString((string) $mine->id, $response->getContent());
    }

    public function test_order_history_is_throttled(): void
    {
        $this->placeOrder([
            'customer_name' => 'Tolu',
            'customer_phone' => '08011111111',
            'delivery_address' => '10 Downing Street',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/restaurant/online/orders', ['customer_phone' => '08011111111'])->assertOk();
        }

        $this->post('/restaurant/online/orders', ['customer_phone' => '08011111111'])
            ->assertStatus(429);
    }

    public function test_the_order_history_page_is_a_form_and_does_not_look_orders_up_over_get(): void
    {
        $this->placeOrder([
            'customer_name' => 'Tolu',
            'customer_phone' => '08011111111',
            'delivery_address' => '10 Downing Street',
        ]);

        // The shareable page is just the phone form...
        $this->get('/restaurant/online/orders')
            ->assertOk()
            ->assertSee('Phone Number')
            ->assertDontSee('10 Downing Street');

        // ...and a phone passed in the query string is ignored, so the lookup
        // stays POST-only and never leaks an order to a shared link.
        $this->get('/restaurant/online/orders?customer_phone=08011111111')
            ->assertOk()
            ->assertDontSee('10 Downing Street');
    }

    /**
     * Order ids are sequential, so the confirmation link is guessable, and the
     * page renders the guest's name and delivery address. Only the session that
     * placed the order may open it.
     */
    public function test_confirmation_is_scoped_to_the_session_that_placed_the_order(): void
    {
        $order = $this->placeOrder([
            'customer_name' => 'Tolu',
            'customer_phone' => '08011111111',
            'delivery_address' => '10 Downing Street',
        ]);

        $this->get("/restaurant/online/order/confirm/{$order->id}")
            ->assertOk()
            ->assertSee("Order #{$order->id}")
            ->assertSee('Waiting for the kitchen')
            ->assertSee('Burger')
            ->assertSee('10 Downing Street');

        // A different visitor guessing the id gets nothing.
        $this->flushSession();
        $this->get("/restaurant/online/order/confirm/{$order->id}")->assertForbidden();
    }

    public function test_confirmation_rejects_a_foreign_table_order(): void
    {
        $order = Order::create([
            'type' => 'table',
            'status' => 'pending',
            'tracking_status' => 'pending',
            'subtotal' => 100,
            'grand_total' => 100,
        ]);

        $this->get("/restaurant/online/order/confirm/{$order->id}")->assertForbidden();
    }

    private function placeOrder(array $customer): Order
    {
        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id,
            'quantity' => 1,
        ])->assertOk();

        return $this->submitOnlineOrder($customer);
    }

    public function test_submit_empty_cart_rejected(): void
    {
        $response = $this->post('/restaurant/online/order/submit', [
            'customer_name' => 'John',
            'customer_phone' => '08012345678',
            'delivery_address' => 'Some address',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_submit_missing_delivery_fields_fails(): void
    {
        $this->postJson('/restaurant/online/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ]);

        $response = $this->post('/restaurant/online/order/submit', [
            'customer_name' => '',
            'customer_phone' => '',
            'delivery_address' => '',
        ]);

        $response->assertSessionHasErrors(['customer_name', 'customer_phone', 'delivery_address']);
    }

    public function test_online_menu_hides_unavailable_items(): void
    {
        $availableItem = MenuItem::create([
            'restaurant_menu_categories_id' => $this->burger->restaurant_menu_categories_id,
            'name' => 'Available Pizza',
            'price' => 2000,
            'is_available' => true,
        ]);
        $unavailableItem = MenuItem::create([
            'restaurant_menu_categories_id' => $this->burger->restaurant_menu_categories_id,
            'name' => 'Hidden Pasta',
            'price' => 1800,
            'is_available' => false,
        ]);

        $response = $this->get('/restaurant/online/menu');
        $response->assertOk();
        $response->assertSee('Available Pizza');
        $response->assertDontSee('Hidden Pasta');
    }
}
