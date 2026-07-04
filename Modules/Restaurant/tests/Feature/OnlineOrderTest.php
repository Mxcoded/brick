<?php

namespace Modules\Restaurant\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Tests\TestCase;

class OnlineOrderTest extends TestCase
{
    use DatabaseTransactions;

    private MenuItem $burger;

    private MenuItem $fries;

    protected function setUp(): void
    {
        parent::setUp();

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

    private function submitOnlineOrder(array $data): Order
    {
        $response = $this->post('/restaurant/online/order/submit', $data);
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        preg_match('/\/order\/confirm\/(\d+)$/', $location, $matches);

        return Order::findOrFail((int) $matches[1]);
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

    public function test_customer_can_view_order_history(): void
    {
        $order = Order::create([
            'type' => 'online',
            'status' => 'completed',
            'tracking_status' => 'paid',
            'customer_name' => 'Tolu',
            'customer_phone' => '08011111111',
            'delivery_address' => '10 Downing Street',
            'subtotal' => 1500,
            'vat' => 112.50,
            'vat_rate' => 7.5,
            'grand_total' => 1612.50,
        ]);

        $response = $this->post('/restaurant/online/orders', [
            'customer_phone' => '08011111111',
        ]);

        $response->assertOk();
        $response->assertSee('Tolu');
        $response->assertSee('08011111111');
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
