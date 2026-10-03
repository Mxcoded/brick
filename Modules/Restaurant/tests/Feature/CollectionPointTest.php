<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;
use Modules\Restaurant\Models\CollectionPoint;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Services\OrderPaymentService;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use Tests\TestCase;

class CollectionPointTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    private User $waiter;

    private MenuItem $burger;

    private Table $table;

    private OrderPaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        RestaurantSetting::setValue('vat_rate', '0');
        RestaurantSetting::setValue('service_charge_rate', '0');

        $this->grantRestaurantRole(RoleEnum::WAITER->value);
        $this->waiter = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->waiter->assignRole(RoleEnum::WAITER->value);

        $category = MenuCategory::create(['name' => 'Test Foods']);
        $this->burger = MenuItem::create([
            'restaurant_menu_categories_id' => $category->id,
            'name' => 'Burger',
            'price' => 1500.00,
            'is_available' => true,
        ]);
        $this->table = Table::create(['number' => 'T1']);

        $this->payments = app(OrderPaymentService::class);
    }

    private function assetAccount(string $code, string $name): ChartOfAccount
    {
        return ChartOfAccount::create([
            'code' => $code,
            'name' => $name,
            'type' => 'asset',
            'normal_balance' => 'debit',
            'active' => true,
        ]);
    }

    private function collectionPoint(array $overrides = []): CollectionPoint
    {
        $code = $overrides['code'] ?? 'MONI'.uniqid();

        return CollectionPoint::create(array_merge([
            'name' => 'Monipoint',
            'code' => $code,
            'is_active' => true,
        ], $overrides));
    }

    private function startShift(array $overrides = []): array
    {
        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/shift/start', array_merge([
            'starting_cash' => 0,
        ], $overrides));

        $response->assertOk()->assertJson(['success' => true]);

        return $response->json('shift');
    }

    private function sell(): Order
    {
        $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/cart/add', [
            'item_id' => $this->burger->id, 'quantity' => 1,
        ])->assertOk();

        $response = $this->actingAs($this->waiter)->postJson('/restaurant-waiter/pos/order/submit', [
            'source_type' => 'table',
            'source_id' => $this->table->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $order = Order::findOrFail($response->json('order_id'));
        $this->payments->takePayment($order, ['method' => 'card']);

        return $order;
    }

    /**
     * @return array<string, float>
     */
    private function entrySide(JournalEntry $entry, string $side): array
    {
        $amounts = [];

        foreach ($entry->lines()->with('account')->get() as $line) {
            $value = round((float) $line->{$side}, 2);
            if ($value > 0) {
                $amounts[$line->account->code] = $value;
            }
        }

        return $amounts;
    }

    private function debitsFor(Order $order): array
    {
        return $this->entrySide(
            JournalEntry::where('reference_type', 'restaurant_payment')
                ->where('reference_id', $order->payments()->first()->id)
                ->firstOrFail(),
            'debit',
        );
    }

    public function test_shift_snapshots_collection_point(): void
    {
        $moni = $this->collectionPoint();

        $shift = $this->startShift(['collection_point_id' => $moni->id]);

        $this->assertSame($moni->id, $shift['collection_point_id']);
    }

    public function test_order_inherits_collection_point_from_shift(): void
    {
        $moni = $this->collectionPoint();
        $this->startShift(['collection_point_id' => $moni->id]);

        $order = $this->sell();

        $this->assertSame($moni->id, $order->collection_point_id);
    }

    public function test_payment_debits_terminal_asset_account(): void
    {
        $this->assetAccount('1205', 'Monipoint');
        $moni = $this->collectionPoint(['asset_account_code' => '1205']);
        $this->startShift(['collection_point_id' => $moni->id]);

        $order = $this->sell();

        $this->assertSame(['1205' => 1500.0], $this->debitsFor($order));
    }

    public function test_refund_debits_revenue_and_credits_terminal_asset(): void
    {
        $this->assetAccount('1205', 'Monipoint');
        $moni = $this->collectionPoint(['asset_account_code' => '1205']);
        $this->startShift(['collection_point_id' => $moni->id]);

        $order = $this->sell();
        $this->payments->refundPayment($order->fresh(), 'Refund');

        $entry = JournalEntry::where('entry_number', 'REF-restaurant_payment-'.$order->payments()->first()->id)
            ->firstOrFail();

        $this->assertTrue($entry->isBalanced());
        $this->assertSame(['1205' => 1500.0], $this->entrySide($entry, 'credit'));
        $this->assertSame(['4100' => 1500.0], $this->entrySide($entry, 'debit'));
    }
}