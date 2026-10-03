<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Restaurant\Models\CollectionPoint;
use Modules\Restaurant\Models\Customer;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\RecipeItem;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\SalesPoint;
use Modules\Restaurant\Models\StockItem;
use Modules\Restaurant\Models\StockMovement;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Models\WaiterShift;
use Modules\Restaurant\Services\OrderPaymentService;
use Modules\Restaurant\Services\OrderPricingService;
use Modules\Restaurant\Support\OrderStatus;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\PaymentGatewayManager;

class RestaurantController extends Controller
{
    protected $validTypes = ['table', 'room', 'online'];

    /**
     * Ceiling on a single cart line, and on the order types the waiter
     * terminal is allowed to settle. The terminal takes money for orders that
     * were served in the restaurant; online orders are settled on the website,
     * so a waiter must not be able to pay, refund or print one.
     */
    public const POS_MAX_LINE_QUANTITY = 999;

    public const POS_SETTLEABLE_TYPES = ['table', 'room', 'walk_in'];

    protected function payments(): OrderPaymentService
    {
        return app(OrderPaymentService::class);
    }

    /**
     * The one place order totals are calculated.
     */
    protected function pricing(): OrderPricingService
    {
        return app(OrderPricingService::class);
    }

    /**
     * The outlet an order from this shift belongs to.
     *
     * Falls back to the property default when the waiter clocked in without
     * picking one, because an order with no outlet is still a real sale and
     * should land somewhere rather than be dropped from the outlet reports.
     */
    protected function salesPointForShift(?WaiterShift $shift): ?int
    {
        return $shift?->sales_point_id ?: SalesPoint::default()?->id;
    }

    /**
     * The physical terminal the takings for this order came through.
     */
    protected function collectionPointForShift(?WaiterShift $shift): ?int
    {
        return $shift?->collection_point_id ?: CollectionPoint::default()?->id;
    }

    /**
     * Refuse an order the waiter terminal is not allowed to act on.
     *
     * Implicit route binding will happily hand a waiter any order in the
     * system, including a delivery order they never served, so the type is
     * checked before the order is touched.
     */
    protected function guardSettleableOrder(Order $order): Order
    {
        if (! in_array($order->type, self::POS_SETTLEABLE_TYPES, true)) {
            abort(403, 'This order cannot be settled from the waiter terminal.');
        }

        return $order;
    }

    /**
     * The public restaurant front door.
     *
     * Only online ordering is offered here. Dine-in tables are ordered by staff
     * on the waiter terminal, and room service belongs to a checked-in guest in
     * the guest dashboard, so neither is a choice the open web gets to make.
     * Listing the tables and rooms here used to let anyone pick one and order
     * against it.
     */
    public function index()
    {
        return view('restaurant::index');
    }

    public function menu($type, $source, Request $request)
    {
        Log::info('Menu method called', [
            'type' => $type,
            'source' => $source,
            'url' => $request->fullUrl(),
            'route_parameters' => $request->route()->parameters(),
        ]);

        if (! in_array($type, $this->validTypes)) {
            Log::error('Invalid order type accessed: '.$type);
            abort(404, 'Invalid order type.');
        }

        $sourceModel = null;
        if ($type === 'table') {
            $sourceModel = Table::find($source) ?? abort(404, 'Invalid table.');
        } elseif ($type === 'room') {
            $sourceModel = Room::find($source) ?? abort(404, 'Invalid room.');
        } elseif ($type === 'online' && $source) {
            Log::warning('Online order accessed with source: '.$source);
            abort(404, 'Online orders do not require a source.');
        }

        try {
            // Sold-out dishes are withheld from the public menu, where there is
            // nobody to explain the absence. A dish that vanishes mid-service
            // leaves the customer wondering whether they misremembered it, and
            // the kitchen should not have to turn an item away at the pass. The
            // server also refuses one at add and at submit, so a tab left open
            // across a sell-out is still caught.
            //
            // Waiter ordering keeps the full list, including sold-out dishes:
            // the terminal is staff, and seeing a sold-out item next to its
            // category is what tells a waiter not to promise it to a guest.
            $itemFilter = $type === 'online'
                ? fn ($q) => $q->where('is_available', true)
                : fn ($q) => $q;

            $categoryId = $request->query('category');
            if ($categoryId) {
                $category = MenuCategory::with([
                    'menuItems' => $itemFilter,
                    'childrenRecursive.menuItems' => $itemFilter,
                ])->find($categoryId);
                if (! $category) {
                    Session::flash('error', 'The selected category is not available.');
                    $categories = MenuCategory::with([
                        'menuItems' => $itemFilter,
                        'childrenRecursive.menuItems' => $itemFilter,
                    ])->get();
                } else {
                    $categories = collect([$category]);
                }
            } else {
                $categories = MenuCategory::with([
                    'menuItems' => $itemFilter,
                    'childrenRecursive.menuItems' => $itemFilter,
                ])
                    ->whereNull('parent_id')
                    ->get();
            }
            $category_names = $categories->whereNull('parent_id')->pluck('name')->toArray();
        } catch (\Exception $e) {
            Log::error('Menu loading error: '.$e->getMessage());
            Session::flash('error', 'Unable to load menu. Please try again later.');
            $categories = collect();
            $category_names = [];
        }

        return view('restaurant::menu', compact('categories', 'category_names', 'type', 'sourceModel'));
    }

    public function addToCart(Request $request, $type = 'online', $sourceId = null)
    {
        Log::info('addToCart called', [
            'type' => $type,
            'sourceId' => $sourceId,
            'request_data' => $request->all(),
        ]);
        // Define valid types and fallback
        $validTypes = ['online', 'table', 'room'];
        if (! in_array($type, $validTypes)) {
            Log::error('Invalid order type accessed: '.$type);
            abort(404, 'Invalid order type.');
        }
        // Check if request contains 'index', redirect to updateCart
        if ($request->has('index')) {
            Log::warning('addToCart received index parameter, redirecting to updateCart', ['request_data' => $request->all()]);

            return $this->updateCart($request, $type, $sourceId);
        }
        // Validate request data
        $validated = $request->validate([
            'item_id' => 'required|exists:restaurant_menu_items,id',
            'quantity' => 'required|integer|min:1|max:'.self::POS_MAX_LINE_QUANTITY,
            'instructions' => 'nullable|string|max:255',
        ]);

        // Check availability for online orders
        if ($type === 'online') {
            $menuItem = MenuItem::find($validated['item_id']);
            if (! $menuItem || ! $menuItem->is_available) {
                return response()->json(['success' => false, 'message' => 'This item is currently unavailable.'], 422);
            }
        }

        // Validate source if required
        if ($type === 'table' && ! Table::find($sourceId)) {
            Log::error('Invalid table ID accessed: '.$sourceId);
            abort(404, 'Invalid table ID.');
        }
        if ($type === 'room' && ! Room::find($sourceId)) {
            Log::error('Invalid room ID accessed: '.$sourceId);
            abort(404, 'Invalid room ID.');
        }
        if ($type === 'online' && $sourceId) {
            Log::error('Online order accessed with source: '.$sourceId);
            abort(404, 'Online orders should not include a source ID.');
        }

        // Generate cart key
        $cartKey = "{$type}_cart";
        $cart = session()->get($cartKey, []);
        Log::info('Current cart contents', ['cart' => $cart]);
        // Check for existing item with same instructions
        foreach ($cart as $index => $item) {
            if (
                $item['item_id'] == $validated['item_id'] &&
                ($item['instructions'] ?? '') === ($validated['instructions'] ?? '')
            ) {
                $cart[$index]['quantity'] += $validated['quantity'];
                session()->put($cartKey, $cart);
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Item quantity updated in cart.',
                        'cart' => $cart,
                    ]);
                }
                Log::info('Item quantity updated in cart', ['item_id' => $validated['item_id'], 'quantity' => $cart[$index]['quantity']]);

                return redirect()->back()->with('success', 'Item quantity updated in cart.');
            }
        }

        // Add new item to cart
        Log::info('Adding new item to cart', ['item_id' => $validated['item_id'], 'quantity' => $validated['quantity']]);
        $menuItem = MenuItem::findOrFail($validated['item_id']);
        $cart[] = [
            'item_id' => $menuItem->id,
            'name' => $menuItem->name,
            'price' => $menuItem->price,
            'quantity' => $validated['quantity'],
            'instructions' => $validated['instructions'] ?? '',
        ];
        Log::info('New item added to cart', ['cart' => $cart]);

        session()->put($cartKey, $cart);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart.',
                'cart' => $cart,
            ]);
            Log::info('Item added to cart', ['item_id' => $validated['item_id'], 'cart' => $cart]);
        }

        return redirect()->back()->with('success', 'Item added to cart.');
    }

    public function getCart($type = 'online', $sourceId = null)
    {

        Log::info('getCart called', ['type' => $type, 'sourceId' => $sourceId]);
        // Validate type and source
        $validTypes = ['online', 'table', 'room'];
        if (! in_array($type, $validTypes)) {
            Log::error('Invalid order type accessed: '.$type);

            return response()->json(['success' => false, 'message' => 'Invalid order type'], 400);
        }

        if ($type === 'table' && ! Table::find($sourceId)) {
            Log::error('Invalid table accessed: '.$sourceId);

            return response()->json(['success' => false, 'message' => 'Invalid table'], 404);
        }
        if ($type === 'room' && ! Room::find($sourceId)) {
            Log::error('Invalid room accessed: '.$sourceId);

            return response()->json(['success' => false, 'message' => 'Invalid room'], 404);
        }
        if ($type === 'online' && $sourceId) {
            Log::error('Online order accessed with source: '.$sourceId);

            return response()->json(['success' => false, 'message' => 'Online orders do not require a source'], 400);
        }

        $cartKey = $type === 'online' ? 'online_cart' : $type.'_cart';
        $cart = session($cartKey, []);
        Log::info('Cart retrieved', ['cart' => $cart]);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    /**
     * Replace a guest's whole cart in one call.
     *
     * Kept for clients that build a cart in a single request. The payload is
     * rebuilt from the database here: names and prices are display fields, and
     * trusting the caller's copy would let a guest post its own prices and
     * name arbitrary session keys.
     */
    public function addToOrder(Request $request, $type, $source = null)
    {
        if (! in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'order' => 'required|array',
            'order.*.item_id' => 'required|exists:restaurant_menu_items,id',
            'order.*.quantity' => 'required|integer|min:1|max:'.self::POS_MAX_LINE_QUANTITY,
            'order.*.instructions' => 'nullable|string|max:255',
        ]);

        if ($type === 'table' && ! Table::find($source)) {
            abort(404, 'Invalid table.');
        }
        if ($type === 'room' && ! Room::find($source)) {
            abort(404, 'Invalid room.');
        }
        if ($type === 'online' && $source) {
            abort(404, 'Online orders do not require a source.');
        }

        $submitted = collect($request->input('order'));
        $menuItems = MenuItem::whereIn('id', $submitted->pluck('item_id')->unique())
            ->get()->keyBy('id');

        $cart = [];
        foreach ($submitted as $item) {
            $menuItem = $menuItems[$item['item_id']] ?? null;
            if (! $menuItem) {
                continue;
            }

            $cart[] = [
                'item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => (float) $menuItem->price,
                'quantity' => (int) $item['quantity'],
                'instructions' => $item['instructions'] ?? null,
            ];
        }

        $cartKey = $type.'_cart';
        session()->put($cartKey, $cart);

        return response()->json(['success' => 'Cart updated successfully!']);
    }

    public function viewCart($type, $source = null)
    {
        if (! in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $sourceModel = null;
        if ($type === 'table') {
            $sourceModel = Table::find($source) ?? abort(404, 'Invalid table.');
        } elseif ($type === 'room') {
            $sourceModel = Room::find($source) ?? abort(404, 'Invalid room.');
        } elseif ($type === 'online' && $source) {
            abort(404, 'Online orders do not require a source.');
        }

        $cartKey = $type.'_cart';
        $cart = session()->get($cartKey, []);
        $itemIds = array_column($cart, 'item_id');
        $items = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');

        // The checkout applies VAT on top of the line totals. Showing only the
        // line sum here means the number on the checkout is not the number the
        // customer was just shown, so the same rate is passed down and the
        // page can state the amount that will actually be charged.
        $vatRate = (float) RestaurantSetting::getValue('vat_rate', '7.5');
        $serviceChargeRate = (float) RestaurantSetting::getValue('service_charge_rate', '0');

        // Survives a validation bounce via old(), so fixing a typo is a new
        // order while resubmitting an unchanged form is a repeat of the same one.
        $idempotencyKey = (string) Str::uuid();

        // Online checkout is the only flow that pays up front, so it is the only
        // one that needs the public key for the browser-side gateway widget.
        $paystackPublicKey = (string) config('services.paystack.public');

        return view('restaurant::cart', compact('cart', 'items', 'type', 'sourceModel', 'vatRate', 'serviceChargeRate', 'idempotencyKey', 'paystackPublicKey'));
    }

    public function updateCart(Request $request, $type, $source = null)
    {
        if (! in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'index' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1|max:'.self::POS_MAX_LINE_QUANTITY,
        ]);

        $cartKey = $type.'_cart';
        $cart = session()->get($cartKey, []);
        $index = $request->input('index');

        if (isset($cart[$index])) {
            $cart[$index]['quantity'] = $request->input('quantity');
            session()->put($cartKey, $cart);
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Cart updated!', 'cart' => array_values($cart)]);
            }

            return redirect()->back()->with('success', 'Cart updated!');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Invalid cart item.'], 404);
        }

        return redirect()->back()->with('error', 'Invalid cart item.');
    }

    public function removeFromCart(Request $request, $type, $source = null)
    {
        if (! in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $cartKey = $type.'_cart';
        $cart = session()->get($cartKey, []);
        $index = $request->input('index');

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart);
            session()->put($cartKey, $cart);
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Item removed from cart!', 'cart' => $cart]);
            }

            return redirect()->back()->with('success', 'Item removed from cart!');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Invalid cart item.'], 404);
        }

        return redirect()->back()->with('error', 'Invalid cart item.');
    }

    public function submitOrder(Request $request, $type, $source = null)
    {
        if (! in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $sourceModel = null;
        if ($type === 'table') {
            $sourceModel = Table::find($source) ?? abort(404, 'Invalid table.');
        } elseif ($type === 'room') {
            $sourceModel = Room::find($source) ?? abort(404, 'Invalid room.');
        } elseif ($type === 'online' && $source) {
            abort(404, 'Online orders do not require a source.');
        }

        $cartKey = $type.'_cart';
        $cart = session()->get($cartKey, []);

        // A double-tapped Place Order must not become two real orders. The key
        // is paired with a fingerprint of the basket it was issued for, so a
        // resubmit of the same form is answered with the order it already made
        // while a basket edited since the page was rendered is treated as the
        // new order the customer actually means. Comparing contents rather
        // than trusting the key alone is what stops a stale page from
        // silently discarding items the customer just added.
        $idempotencyKey = $request->input('idempotency_key');
        $cartFingerprint = $this->cartFingerprint($cart);
        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $known = session()->get('submitted_order_keys', []);
            $match = $known[$idempotencyKey] ?? null;
            if (is_array($match) && $existing = Order::find($match['order_id'] ?? null)) {
                // Two shapes of replay count as the same order. An empty basket
                // is the normal case: a successful submit consumed it, so a
                // double-tap arrives with nothing left. A basket that still
                // matches the one recorded is a resubmitted form. Anything else
                // means the customer changed the order after the page was
                // rendered, which is a new order and must be placed as one.
                $alreadyPlaced = empty($cart)
                    || ($match['fingerprint'] ?? null) === $cartFingerprint;

                if ($alreadyPlaced) {
                    return $this->orderConfirmationRedirect($type, $source, $existing)
                        ->with('success', 'Order already received.');
                }
            }
        }

        if ($type === 'online') {
            $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'delivery_address' => 'required|string|max:1000',
            ]);
        }

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $itemIds = array_column($cart, 'item_id');
        $menuItems = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');
        $invalidItems = array_diff($itemIds, $menuItems->pluck('id')->toArray());
        if (! empty($invalidItems)) {
            return redirect()->back()->with('error', 'Some items in your cart are no longer available.');
        }

        // Check availability for online
        if ($type === 'online') {
            $unavailable = $menuItems->filter(fn ($i) => ! $i->is_available);
            if ($unavailable->isNotEmpty()) {
                $names = $unavailable->pluck('name')->implode(', ');

                return redirect()->back()->with('error', "Some items are unavailable: {$names}");
            }
        }

        $subtotal = collect($cart)->sum(fn ($i) => ($menuItems[$i['item_id']]->price ?? 0) * $i['quantity']);

        // Priced by the same service the POS uses, so a table guest, a room
        // guest and a delivery guest are charged the same for the same basket.
        $totals = $this->pricing()->quote((float) $subtotal);
        $grandTotal = $totals['grand_total'];

        // Online orders are paid before they exist. The reference is verified
        // against the gateway here, before a row is written, so an abandoned or
        // failed payment leaves no order behind for the kitchen to cook.
        $paymentReference = null;
        $paymentMethod = null;
        if ($type === 'online') {
            $paymentReference = (string) $request->input('payment_reference', '');
            $verification = $this->verifyOnlinePayment($paymentReference, $grandTotal);
            if ($verification['error'] !== null) {
                return redirect()->back()->withInput()->with('error', $verification['error']);
            }
            $paymentMethod = $verification['method'];
        }

        $orderData = [
            'type' => $type,
            'status' => 'pending',
            'tracking_status' => $type === 'online' || $type === 'room' ? 'pending' : null,
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'vat' => $totals['vat'],
            'vat_rate' => $totals['vat_rate'],
            'service_charge' => $totals['service_charge'],
            'service_charge_rate' => $totals['service_charge_rate'],
            'grand_total' => $totals['grand_total'],
        ];

        if ($type === 'table' || $type === 'room') {
            $orderData['source_id'] = $sourceModel->id;
        }

        // A guest order has no waiter and so no shift to inherit from. It falls
        // back to the property's default outlet, and to none at all if the
        // property has not declared one, in which case the sale stays on the
        // module revenue account as it always has.
        $orderData['sales_point_id'] = SalesPoint::default()?->id;
        $orderData['collection_point_id'] = CollectionPoint::default()?->id;
        if ($type === 'online') {
            $orderData['customer_name'] = $request->input('customer_name');
            $orderData['customer_phone'] = $request->input('customer_phone');
            $orderData['delivery_address'] = $request->input('delivery_address');
        }

        $order = Order::create($orderData);

        foreach ($cart as $item) {
            OrderItem::create([
                'restaurant_order_id' => $order->id,
                'restaurant_menu_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                // Freeze the price the order was priced from, so the
                // confirmation and any reprint keep showing this figure.
                'unit_price' => $menuItems[$item['item_id']]->price,
                'instructions' => $item['instructions'],
            ]);
        }

        session()->forget($cartKey);

        // The money already cleared with the gateway, so record it against the
        // fresh order. This does not close the order out: the kitchen still has
        // to make it and get it to the customer.
        if ($type === 'online') {
            $this->payments()->recordGatewayPayment($order, [
                'amount' => (float) $grandTotal,
                'method' => $paymentMethod ?? 'card',
                'reference' => $paymentReference,
            ]);
        }

        // Remember what this session bought. Guests have no account, so the
        // session is the only thing tying a confirmation link to the person
        // who placed the order.
        $sessionKey = $type === 'online' ? 'online_order_ids' : 'guest_order_ids';
        $placed = array_values(array_unique(array_merge(
            (array) session()->get($sessionKey, []),
            [$order->id]
        )));
        session()->put($sessionKey, array_slice($placed, -50));

        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $keys = session()->get('submitted_order_keys', []);
            $keys[$idempotencyKey] = [
                'order_id' => $order->id,
                'fingerprint' => $cartFingerprint,
            ];
            session()->put('submitted_order_keys', array_slice($keys, -25, null, true));
        }

        return $this->orderConfirmationRedirect($type, $source, $order)
            ->with('success', 'Order placed successfully!');
    }

    /**
     * A stable digest of the basket, used to tell an accidental resubmit of one
     * form apart from a genuine new order made after the basket changed.
     *
     * Lines are sorted before hashing so that two identical baskets written in
     * a different order still match, and notes are included because two lines
     * of the same dish with different notes are different orders to the kitchen.
     */
    protected function cartFingerprint(array $cart): string
    {
        $lines = [];
        foreach ($cart as $line) {
            $lines[] = implode(':', [
                (int) ($line['item_id'] ?? 0),
                (int) ($line['quantity'] ?? 0),
                trim((string) ($line['instructions'] ?? '')),
            ]);
        }
        sort($lines);

        return hash('sha256', implode('|', $lines));
    }

    /**
     * Confirm an online customer's gateway payment before the order is written.
     *
     * The browser runs the gateway widget and hands back a reference, so the
     * reference is the claim. It is checked three ways against the gateway and
     * our own records: it must be a successful charge, cover the server-priced
     * total, and not have settled an earlier order. Prices are recomputed from
     * the database, never taken from the response.
     *
     * @return array{error: string|null, method: string} The method is the
     *                                                   restaurant's own vocabulary for how the money arrived, so the payment
     *                                                   row and the ledger say "transfer" (or "mobile_money", or "card") rather
     *                                                   than the gateway's name for the channel.
     */
    protected function verifyOnlinePayment(string $reference, float $expected): array
    {
        if ($reference === '') {
            return ['error' => 'Please complete payment before placing your order.', 'method' => 'card'];
        }

        // A reference may settle one order only. The gateway will happily verify
        // the same successful charge again, so this is what stops one payment
        // being replayed against a second basket.
        if (Payment::where('reference', $reference)->where('status', 'completed')->exists()) {
            return ['error' => 'That payment has already been used for another order.', 'method' => 'card'];
        }

        try {
            $verification = app(PaymentGatewayManager::class)->driver('paystack')->verify($reference);
        } catch (\Throwable $e) {
            report($e);

            return ['error' => 'We could not confirm your payment. Please try again.', 'method' => 'card'];
        }

        if (! ($verification['status'] ?? false) || ($verification['data']['status'] ?? null) !== 'success') {
            return ['error' => 'Your payment was not successful, so no order was placed.', 'method' => 'card'];
        }

        $paid = (int) ($verification['data']['amount'] ?? 0);
        if ($paid < (int) round($expected * 100)) {
            return ['error' => 'The amount paid does not cover this order.', 'method' => 'card'];
        }

        return [
            'error' => null,
            'method' => $this->paystackChannelMethod($verification['data']['channel'] ?? null),
        ];
    }

    /**
     * Translate a Paystack channel into the restaurant's payment vocabulary.
     *
     * Paystack names the rail the money travelled on; our reports and the GL
     * only understand the handful of methods used at the till. Anything
     * unfamiliar is treated as a card, which is also the account a gateway
     * charge settles through.
     */
    protected function paystackChannelMethod(?string $channel): string
    {
        return match ($channel) {
            'mobile_money' => 'mobile_money',
            'bank', 'bank_transfer', 'ussd', 'eft' => 'transfer',
            default => 'card',
        };
    }

    /**
     * The one place a placed order is turned into its confirmation redirect,
     * so a repeat submission lands on the same page a first one does.
     */
    protected function orderConfirmationRedirect(string $type, $source, Order $order)
    {
        if (! $order) {
            abort(404, 'Order not found.');
        }

        return redirect()->route(
            $type === 'online' ? 'restaurant.online.order.confirm' : 'restaurant.order.confirm',
            $type === 'online' ? ['order' => $order->id] : ['type' => $type, 'source' => $source, 'order' => $order->id]
        );
    }

    public function confirmOrder($type, $source, $order)
    {
        if (! in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $sourceModel = null;
        if ($type === 'table') {
            $sourceModel = Table::find($source) ?? abort(404, 'Invalid table.');
        } elseif ($type === 'room') {
            $sourceModel = Room::find($source) ?? abort(404, 'Invalid room.');
        } elseif ($type === 'online' && $source) {
            abort(404, 'Online orders do not require a source.');
        }

        $order = Order::with('orderItems.menuItem')->findOrFail($order);
        if ($order->type !== $type || ($type !== 'online' && $order->source_id !== ($sourceModel->id ?? null))) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Guests have no account, so the order id is guessable: these are
        // sequential, and the page renders the customer's name and delivery
        // address. Require the session that placed the order, which is what
        // links a guest to their own order and nothing else.
        if (! $this->sessionOwnsOrder($order)) {
            abort(403, 'Unauthorized access to this order.');
        }

        return view('restaurant::confirm', compact('order', 'type', 'sourceModel'));
    }

    /**
     * Whether the current session is one that placed this order.
     *
     * Staff working an order look it up through the back office, not the guest
     * confirmation route, so staff sessions are not a concern here.
     */
    protected function sessionOwnsOrder(Order $order): bool
    {
        $key = $order->type === 'online' ? 'online_order_ids' : 'guest_order_ids';

        return in_array($order->id, (array) session()->get($key, []), true);
    }

    public function viewOrderHistory(Request $request)
    {
        $orders = collect();
        $phone = null;

        if ($request->isMethod('post')) {
            $request->validate([
                'customer_phone' => 'required|string|max:20',
            ]);

            $phone = $request->input('customer_phone');

            // Lookup stays available to guests, but only for orders this
            // session actually placed. Otherwise the endpoint hands every
            // online order for any phone number to whoever asks, which is a
            // customer-enumeration and bulk-scraping hole.
            $orders = Order::where('type', 'online')
                ->where('customer_phone', $phone)
                ->whereIn('id', (array) session()->get('online_order_ids', []))
                ->with('orderItems.menuItem')
                ->get();
        }

        return view('restaurant::orders', compact('orders', 'phone'));
    }

    /** Waiter Dashboard and Order Management start */
    public function waiterDashboard()
    {
        $pendingOrders = Order::where('status', 'pending')
            ->whereIn('type', ['table', 'room', 'walk_in'])
            ->with('orderItems.menuItem')
            ->get();

        $activeOrders = Order::where('status', 'accepted')
            ->whereIn('tracking_status', ['preparing', 'ready', 'served'])
            ->whereIn('type', ['table', 'room', 'walk_in'])
            ->with('orderItems.menuItem')
            ->get();

        $paidOrders = Order::where('status', 'completed')
            ->where('tracking_status', 'paid')
            ->whereIn('type', ['table', 'room', 'walk_in'])
            ->with('orderItems.menuItem')
            ->latest()
            ->limit(50)
            ->get();

        $categories = MenuCategory::with(['menuItems', 'childrenRecursive.menuItems'])
            ->whereNull('parent_id')
            ->get();

        $tables = Table::all();
        $occupiedTableIds = Order::whereIn('status', ['pending', 'accepted'])
            ->where('type', 'table')
            ->pluck('source_id')
            ->unique()
            ->toArray();

        // Only currently checked-in rooms may be posted to from the waiter
        // terminal. A guest who has checked out must not be billed again here,
        // and vacant rooms must not appear as selectable targets.
        $rooms = collect();
        if (class_exists(RoomUnit::class)) {
            $rooms = RoomUnit::query()
                ->with('currentOccupant')
                ->whereHas('currentOccupant', function ($query) {
                    if (class_exists(Registration::class)) {
                        $query->where('stay_status', 'checked_in');
                    }
                })
                ->orderBy('room_number')
                ->get(['id', 'room_number', 'floor', 'status'])
                ->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'room_number' => $unit->room_number,
                        'floor' => $unit->floor,
                        'status' => $unit->status,
                        'guest' => $unit->currentOccupant?->full_name,
                    ];
                });
        }

        $salesPoints = SalesPoint::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'is_default'])
            ->all();

        return view('restaurant::waiter.dashboard', compact('pendingOrders', 'activeOrders', 'paidOrders', 'categories', 'tables', 'rooms', 'occupiedTableIds', 'salesPoints'));
    }

    public function posAddToCart(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:restaurant_menu_items,id',
            'quantity' => 'required|integer|min:1|max:'.self::POS_MAX_LINE_QUANTITY,
        ]);

        $cart = session()->get('pos_cart', []);

        $existing = null;
        foreach ($cart as $i => $item) {
            if ($item['item_id'] == $validated['item_id']) {
                $existing = $i;
                break;
            }
        }

        if ($existing !== null) {
            $cart[$existing]['quantity'] = min(
                $cart[$existing]['quantity'] + $validated['quantity'],
                self::POS_MAX_LINE_QUANTITY
            );
        } else {
            $menuItem = MenuItem::find($validated['item_id']);
            $cart[] = [
                'item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => (float) $menuItem->price,
                'quantity' => $validated['quantity'],
            ];
        }

        session()->put('pos_cart', $cart);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    public function posUpdateCart(Request $request)
    {
        $validated = $request->validate([
            'index' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1|max:'.self::POS_MAX_LINE_QUANTITY,
        ]);

        $cart = session()->get('pos_cart', []);
        if (isset($cart[$validated['index']])) {
            $cart[$validated['index']]['quantity'] = $validated['quantity'];
            session()->put('pos_cart', $cart);
        }

        return response()->json(['success' => true, 'cart' => array_values($cart)]);
    }

    public function posRemoveFromCart(Request $request)
    {
        $validated = $request->validate([
            'index' => 'required|integer',
        ]);

        $cart = session()->get('pos_cart', []);

        if ($validated['index'] === -1) {
            $cart = [];
        } elseif (isset($cart[$validated['index']])) {
            unset($cart[$validated['index']]);
            $cart = array_values($cart);
        }

        session()->put('pos_cart', $cart);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    public function posGetCart()
    {
        $cart = session()->get('pos_cart', []);
        $total = array_reduce($cart, fn ($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);

        return response()->json(['success' => true, 'cart' => $cart, 'total' => $total]);
    }

    public function posSubmitOrder(Request $request)
    {
        $validated = $request->validate([
            'source_type' => 'required|in:table,walk_in',
            'source_id' => 'required_if:source_type,table|nullable|integer|exists:restaurant_tables,id',
            'customer_name' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
        ]);

        $cart = session()->get('pos_cart', []);
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        $itemIds = array_column($cart, 'item_id');
        $menuItems = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');
        $invalidItems = array_diff($itemIds, $menuItems->pluck('id')->toArray());
        if (! empty($invalidItems)) {
            return response()->json(['success' => false, 'message' => 'Some items are no longer available.'], 422);
        }

        // Price from the database, never from the session snapshot. The cart
        // holds the price the item had when it was added, so a menu edit
        // between adding and settling would otherwise bill the old figure.
        $subtotal = collect($cart)->sum(
            fn ($i) => (float) ($menuItems[$i['item_id']]->price ?? 0) * (int) $i['quantity']
        );

        $discount = (float) ($validated['discount'] ?? 0);
        $discountType = $validated['discount_type'] ?? null;

        // Clamp the discount server-side. The browser clamps it too, but that
        // is cosmetic: without this a request can carry a discount larger than
        // the order, which drives the VAT base and the grand total negative and
        // writes that to the order.
        $discountLimit = (float) RestaurantSetting::getValue('discount_limit', '10000');
        if ($discountType === 'percentage') {
            $discountAmount = $subtotal * min($discount, 100) / 100;
        } else {
            $discountAmount = min($discount, $discountLimit, $subtotal);
        }
        $discountAmount = max(0, $discountAmount);

        $totals = $this->pricing()->quote($subtotal, $discountAmount);
        $grandTotal = $totals['grand_total'];

        $shift = WaiterShift::where('user_id', auth()->id())->where('status', 'open')->first();

        $sourceId = $validated['source_type'] === 'table' ? $validated['source_id'] : null;

        $orderData = [
            'type' => $validated['source_type'],
            'source_id' => $sourceId,
            'status' => OrderStatus::PENDING,
            'tracking_status' => OrderStatus::PENDING,
            'customer_name' => ($validated['customer_name'] ?? null) ?: ($validated['source_type'] === 'table' ? 'Table '.Table::find($sourceId)?->number : 'Walk-in'),
            'shift_id' => $shift?->id,
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'discount_type' => $discountType,
            'vat' => $totals['vat'],
            'vat_rate' => $totals['vat_rate'],
            'service_charge' => $totals['service_charge'],
            'service_charge_rate' => $totals['service_charge_rate'],
            'grand_total' => $totals['grand_total'],
            // Snapshotted from the shift so the order keeps saying which counter
            // it came from after the waiter moves on.
            'sales_point_id' => $this->salesPointForShift($shift),
            'collection_point_id' => $this->collectionPointForShift($shift),
        ];

        $order = Order::create($orderData);

        foreach ($cart as $item) {
            OrderItem::create([
                'restaurant_order_id' => $order->id,
                'restaurant_menu_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $menuItems[$item['item_id']]->price,
                'instructions' => null,
            ]);
        }

        if ($shift) {
            $shift->total_sales = $shift->total_sales + $grandTotal;
            $shift->save();
        }

        session()->forget('pos_cart');

        return response()->json(['success' => true, 'order_id' => $order->id]);
    }

    public function posOrderData()
    {
        $pendingOrders = Order::where('status', 'pending')
            ->whereIn('type', ['table', 'room', 'walk_in'])
            ->with('orderItems.menuItem')
            ->get();

        $activeOrders = Order::where('status', 'accepted')
            ->whereIn('tracking_status', ['preparing', 'ready', 'served'])
            ->whereIn('type', ['table', 'room', 'walk_in'])
            ->with('orderItems.menuItem')
            ->get();

        $paidOrders = Order::where('status', 'completed')
            ->where('tracking_status', 'paid')
            ->whereIn('type', ['table', 'room', 'walk_in'])
            ->with('orderItems.menuItem')
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'pendingOrders' => $pendingOrders,
            'activeOrders' => $activeOrders,
            'paidOrders' => $paidOrders,
        ]);
    }

    public function currentShift()
    {
        $shift = WaiterShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        return response()->json([
            'success' => true,
            'shift' => $shift ? [
                'id' => $shift->id,
                'clock_in' => $shift->clock_in->format('Y-m-d H:i:s'),
                'starting_cash' => $shift->starting_cash,
                'total_sales' => $shift->total_sales,
                'status' => $shift->status,
                // The terminal reads this back to tell the waiter which counter
                // they are on rather than showing a picker over a live shift.
                'sales_point_id' => $shift->sales_point_id,
                'sales_point_name' => $shift->salesPoint?->name,
                'collection_point_id' => $shift->collection_point_id,
                'collection_point_name' => $shift->collectionPoint?->name,
            ] : null,
        ]);
    }

    public function startShift(Request $request)
    {
        $validated = $request->validate([
            'starting_cash' => 'nullable|numeric|min:0',
            // Which counter the waiter is on. Every order they place for the rest
            // of the shift inherits it, which is how a cafe and a restaurant
            // sharing one kitchen stay separable in the revenue.
            'sales_point_id' => [
                'nullable',
                Rule::exists('restaurant_sales_points', 'id')->where('is_active', true),
            ],
            'collection_point_id' => [
                'nullable',
                Rule::exists('restaurant_collection_points', 'id')->where('is_active', true),
            ],
        ]);

        $existing = WaiterShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'You already have an open shift.'], 422);
        }

        $todayShift = WaiterShift::where('user_id', auth()->id())
            ->whereDate('clock_in', today())
            ->exists();

        if ($todayShift) {
            return response()->json(['success' => false, 'message' => 'You already clocked in today.'], 422);
        }

        $startTime = RestaurantSetting::getValue('shift_start_time', '06:00');
        $endTime = RestaurantSetting::getValue('shift_end_time', '22:00');
        $now = now()->format('H:i');

        if ($now < $startTime || $now > $endTime) {
            return response()->json([
                'success' => false,
                'message' => "Shifts can only be started between {$startTime} and {$endTime}.",
            ], 422);
        }

        $shift = WaiterShift::create([
            'user_id' => auth()->id(),
            'sales_point_id' => $validated['sales_point_id'] ?? null,
            'collection_point_id' => $validated['collection_point_id'] ?? null,
            'clock_in' => now(),
            'starting_cash' => $validated['starting_cash'] ?? 0,
            'status' => 'open',
        ]);

        return response()->json(['success' => true, 'shift' => [
            'id' => $shift->id,
            'clock_in' => $shift->clock_in->format('Y-m-d H:i:s'),
            'starting_cash' => $shift->starting_cash,
            'sales_point_id' => $shift->sales_point_id,
            'collection_point_id' => $shift->collection_point_id,
        ]]);
    }

    public function endShift(Request $request)
    {
        $validated = $request->validate([
            'ending_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shift = WaiterShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (! $shift) {
            return response()->json(['success' => false, 'message' => 'No open shift found.'], 404);
        }

        $shift->clock_out = now();
        $shift->ending_cash = $validated['ending_cash'];
        $shift->notes = $validated['notes'] ?? null;
        $shift->status = 'closed';
        $shift->save();

        return response()->json(['success' => true]);
    }

    public function posSettings()
    {
        return response()->json([
            'success' => true,
            'settings' => [
                'vat_rate' => RestaurantSetting::getValue('vat_rate', '7.5'),
                'service_charge_rate' => RestaurantSetting::getValue('service_charge_rate', '0'),
                'discount_limit' => RestaurantSetting::getValue('discount_limit', '10000'),
                'shift_start_time' => RestaurantSetting::getValue('shift_start_time', '06:00'),
                'shift_end_time' => RestaurantSetting::getValue('shift_end_time', '22:00'),
            ],
            // The terminal has to offer the counter picker, so the list of
            // outlets travels with the rest of the settings the shift card needs.
            $salesPoints = SalesPoint::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'is_default'])
                ->all(),
            'collection_points' => CollectionPoint::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'is_default'])
                ->all(),
        ]);
    }

    public function adminSettings()
    {
        $settings = [
            'vat_rate' => RestaurantSetting::getValue('vat_rate', '7.5'),
            'service_charge_rate' => RestaurantSetting::getValue('service_charge_rate', '0'),
            'discount_limit' => RestaurantSetting::getValue('discount_limit', '10000'),
            'shift_start_time' => RestaurantSetting::getValue('shift_start_time', '06:00'),
            'shift_end_time' => RestaurantSetting::getValue('shift_end_time', '22:00'),
        ];

        return view('restaurant::admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'vat_rate' => 'required|numeric|min:0|max:100',
            'service_charge_rate' => 'required|numeric|min:0|max:100',
            'discount_limit' => 'required|numeric|min:0',
            'shift_start_time' => 'required|date_format:H:i',
            'shift_end_time' => 'required|date_format:H:i|after:shift_start_time',
        ]);

        RestaurantSetting::setValue('vat_rate', $validated['vat_rate']);
        RestaurantSetting::setValue('service_charge_rate', $validated['service_charge_rate']);
        RestaurantSetting::setValue('discount_limit', $validated['discount_limit']);
        RestaurantSetting::setValue('shift_start_time', $validated['shift_start_time']);
        RestaurantSetting::setValue('shift_end_time', $validated['shift_end_time']);

        return redirect()->route('restaurant.admin.settings')->with('success', 'Settings updated successfully.');
    }

    // method to handle accepted orders
    public function acceptOrder(Order $order)
    {
        $order->status = 'accepted';
        $order->tracking_status = 'preparing'; // The first stage after acceptance
        $order->save();

        return redirect()->route('restaurant.waiter.dashboard')->with('success', "Order #{$order->id} accepted!");
    }

    // method to handle status updates from the dropdown
    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'tracking_status' => 'required|in:preparing,ready,served,paid',
            'status' => 'sometimes|in:completed',
        ]);

        $order->tracking_status = $request->input('tracking_status');

        // If the form submitted a final status, update it
        if ($request->has('status')) {
            $order->status = $request->input('status');
        }

        $order->save();

        return redirect()->route('restaurant.waiter.dashboard')->with('success', "Order #{$order->id} status updated.");
    }

    // New method to reject a pending order
    public function rejectOrder(Request $request, Order $order)
    {
        // Ensure we can only reject a pending order
        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be rejected.');
        }

        $request->validate(['reason' => 'required|string|max:255']);

        $order->status = 'rejected';
        $order->reason = $request->input('reason'); // Assumes you have a 'reason' column
        $order->save();

        return redirect()->route('restaurant.waiter.dashboard')->with('success', "Order #{$order->id} has been rejected.");
    }

    // New method to void an active order
    public function voidOrder(Request $request, Order $order)
    {
        // Ensure we can only void an accepted order
        if ($order->status !== 'accepted') {
            return redirect()->back()->with('error', 'Only active orders can be voided.');
        }

        $request->validate(['reason' => 'required|string|max:255']);

        $order->status = 'void';
        $order->tracking_status = 'cancelled';
        $order->reason = $request->input('reason'); // Assumes you have a 'reason' column
        $order->save();

        return redirect()->route('restaurant.waiter.dashboard')->with('success', "Order #{$order->id} has been voided.");
    }

    public function printReceipt(Order $order)
    {
        $this->guardSettleableOrder($order);
        $order->load('orderItems.menuItem', 'shift.user', 'payments');

        return view('restaurant::waiter.receipt', compact('order'));
    }

    /**
     * Admin function starting the dashboard
     */
    public function adminDashboard()
    {
        $categories = MenuCategory::withCount('menuItems')->get();
        $parent_categories = $categories->whereNull('parent_id');
        $menuItems = MenuItem::with('category')->get();
        $orders = Order::with('orderItems.menuItem')->latest()->get();
        $trashedItems = MenuItem::onlyTrashed()->with('category')->get();
        $trashedCategories = MenuCategory::onlyTrashed()->withCount('menuItems')->get();

        return view('restaurant::admin.dashboard', compact('categories', 'parent_categories', 'menuItems', 'orders', 'trashedItems', 'trashedCategories'));
    }

    public function addMenuCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:restaurant_menu_categories,name',
            'parent_category' => 'nullable|exists:restaurant_menu_categories,id',
        ]);

        MenuCategory::create([
            'name' => $request->input('name'),
            'parent_id' => $request->input('parent_category'),
        ]);

        return redirect()->route('restaurant.admin.dashboard')->with('success', 'Menu category added successfully!');
    }

    // New Method
    public function editMenuCategory(MenuCategory $category)
    {
        $parent_categories = MenuCategory::whereNull('parent_id')->where('id', '!=', $category->id)->get();

        return view('restaurant::admin.edit_category', compact('category', 'parent_categories'));
    }

    // New Method
    public function updateMenuCategory(Request $request, MenuCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:restaurant_menu_categories,name,'.$category->id,
            'parent_id' => 'nullable|exists:restaurant_menu_categories,id',
        ]);

        // Prevent a category from being its own parent
        if ($request->input('parent_id') == $category->id) {
            return redirect()->back()->with('error', 'A category cannot be its own parent.');
        }

        $category->update($request->all());

        return redirect()->route('restaurant.admin.dashboard')->with('success', 'Menu category updated successfully!');
    }

    public function deleteMenuCategory(MenuCategory $category)
    {
        $category->delete();

        return redirect()->route('restaurant.admin.dashboard')->with('success', 'Menu category deleted successfully!');
    }

    public function restoreMenuCategory($id)
    {
        $category = MenuCategory::withTrashed()->findOrFail($id);
        $category->restore();

        return redirect()->route('restaurant.admin.dashboard')->with('success', 'Menu category restored successfully!');
    }

    // New Method
    public function getSubcategories(MenuCategory $category)
    {
        return response()->json($category->children);
    }

    public function addMenuItem(Request $request)
    {
        $request->validate([
            'restaurant_menu_categories_id' => 'required|exists:restaurant_menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_available' => 'nullable|boolean',
        ]);

        $menuItem = new MenuItem;
        $menuItem->restaurant_menu_categories_id = $request->input('restaurant_menu_categories_id');
        $menuItem->name = $request->input('name');
        $menuItem->description = $request->input('description');
        $menuItem->price = $request->input('price');
        $menuItem->is_available = $request->boolean('is_available', true);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('dishes', 'public');
            $menuItem->image = $path;
        }

        $menuItem->save();

        return redirect()->back()->with('success', 'Menu item added successfully!');
    }

    public function editMenuItem(MenuItem $item)
    {
        $categories = MenuCategory::withTrashed()->get();

        return view('restaurant::admin.edit_item', compact('item', 'categories'));
    }

    public function updateMenuItem(Request $request, MenuItem $item)
    {
        // ... (logic updated to use route model binding) ...
        $request->validate([
            'restaurant_menu_categories_id' => 'required|exists:restaurant_menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'is_available' => 'nullable|boolean',
        ]);

        $item->restaurant_menu_categories_id = $request->input('restaurant_menu_categories_id');
        $item->name = $request->input('name');
        $item->description = $request->input('description');
        $item->price = $request->input('price');
        $item->is_available = $request->boolean('is_available', $item->is_available);

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $path = $request->file('image')->store('dishes', 'public');
            $item->image = $path;
        }

        $item->save();

        return redirect()->route('restaurant.admin.dashboard')->with('success', 'Menu item updated successfully!');
    }

    public function deleteMenuItem(MenuItem $item)
    {
        $item->delete();

        return redirect()->route('restaurant.admin.dashboard')->with('success', 'Menu item deleted successfully!');
    }

    public function restoreMenuItem($id)
    {
        $item = MenuItem::withTrashed()->findOrFail($id);
        $item->restore();

        return redirect()->route('restaurant.admin.dashboard')->with('success', 'Menu item restored successfully!');
    }

    public function updateOrder(Request $request, $order)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,completed',
            'tracking_status' => 'nullable|in:pending,preparing,delivered',
            'payment_method' => 'required_if:status,completed|in:cash,card,mobile_money,transfer',
            'amount_tendered' => 'nullable|numeric|min:0',
        ]);

        $order = Order::findOrFail($order);
        $order->status = $request->input('status');
        if ($order->type === 'online') {
            $order->tracking_status = $request->input('tracking_status');
        }
        $order->save();

        // Completing an unpaid order is a sale, so it goes through the same
        // service the waiter terminal uses rather than a second copy of the
        // posting sequence.
        if ($request->input('status') === 'completed' && ! $order->payments()->where('status', 'completed')->exists()) {
            $this->payments()->takePayment($order, [
                'amount' => (float) $order->grand_total,
                'amount_tendered' => $request->input('amount_tendered', $order->grand_total),
                'method' => $request->input('payment_method', 'cash'),
            ]);
        }

        return redirect()->back()->with('success', 'Order updated successfully!');
    }

    public function showOrder(Order $order)
    {
        $order->load('orderItems.menuItem', 'shift.user');
        $statuses = ['pending', 'accepted', 'completed', 'rejected', 'void'];

        return view('restaurant::admin.order_detail', compact('order', 'statuses'));
    }

    // ─── Phase 1: Payment Processing ─────────────────────────────────

    public function processPayment(Request $request, Order $order)
    {
        $this->guardSettleableOrder($order);

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'amount_tendered' => 'nullable|numeric|min:0',
            'method' => 'required|in:cash,card,mobile_money,transfer',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $balance = $order->balance;
        $amount = $validated['amount'] ?? $balance;
        $amount = min($amount, $balance);

        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'No balance due.'], 422);
        }

        try {
            $result = $this->payments()->takePayment($order, [
                'amount' => $amount,
                'amount_tendered' => $validated['amount_tendered'] ?? null,
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'payment' => $result['payment'],
            'change' => $result['change_due'],
            'balance_remaining' => $result['balance_remaining'],
            'fully_paid' => $result['fully_paid'],
            'order' => $order->fresh(),
        ]);
    }

    public function refundPayment(Request $request, Order $order)
    {
        $this->guardSettleableOrder($order);

        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $payment = $this->payments()->refundPayment($order, $request->input('reason'));
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'payment' => $payment]);
    }

    // ─── Phase 2: Reports & Analytics ────────────────────────────────

    public function salesReport(Request $request)
    {
        $request->validate([
            'period' => 'required|in:today,week,month,custom',
            'date_from' => 'required_if:period,custom|date',
            'date_to' => 'required_if:period,custom|date|after_or_equal:date_from',
        ]);

        $now = now();
        $from = match ($request->period) {
            'today' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            'custom' => $request->date('date_from')->startOfDay(),
        };
        $to = match ($request->period) {
            'today', 'week', 'month' => $now->copy()->endOfDay(),
            'custom' => $request->date('date_to')->endOfDay(),
        };

        $orders = Order::with('payments', 'orderItems.menuItem', 'shift.user')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'completed')
            ->get();

        $totalSales = $orders->sum('grand_total');
        $totalDiscounts = $orders->sum('discount');
        $totalVat = $orders->sum('vat');
        $orderCount = $orders->count();
        $averageOrder = $orderCount > 0 ? $totalSales / $orderCount : 0;

        // Payment method breakdown
        $paymentMethods = Payment::whereIn('restaurant_order_id', $orders->pluck('id'))
            ->completed()
            ->get()
            ->groupBy('method')
            ->map(fn ($items) => [
                'count' => $items->count(),
                'total' => $items->sum('amount'),
            ]);

        // Hourly breakdown
        $hourly = $orders->groupBy(fn ($o) => $o->created_at->format('H'))
            ->map(fn ($items, $hour) => [
                'hour' => $hour.':00',
                'count' => $items->count(),
                'total' => $items->sum('grand_total'),
            ])->values();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_sales' => $totalSales,
                'total_discounts' => $totalDiscounts,
                'total_vat' => $totalVat,
                'order_count' => $orderCount,
                'average_order' => $averageOrder,
                'period' => ['from' => $from, 'to' => $to],
            ],
            'payment_methods' => $paymentMethods,
            'hourly' => $hourly,
        ]);
    }

    public function popularItems(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,custom',
            'date_from' => 'required_if:period,custom|date',
            'date_to' => 'required_if:period,custom|date|after_or_equal:date_from',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $now = now();
        $from = match ($request->period) {
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            'custom' => $request->date('date_from')->startOfDay(),
        };
        $to = match ($request->period) {
            'week', 'month' => $now->copy()->endOfDay(),
            'custom' => $request->date('date_to')->endOfDay(),
        };
        $limit = $request->integer('limit', 20);

        $items = OrderItem::selectRaw('restaurant_menu_item_id, SUM(quantity) as total_qty, SUM(quantity * mi.price) as total_revenue')
            ->join('restaurant_menu_items as mi', 'mi.id', '=', 'restaurant_order_items.restaurant_menu_item_id')
            ->join('restaurant_orders as o', 'o.id', '=', 'restaurant_order_items.restaurant_order_id')
            ->whereBetween('o.created_at', [$from, $to])
            ->where('o.status', 'completed')
            ->groupBy('restaurant_menu_item_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->load('menuItem.category');

        return response()->json([
            'success' => true,
            'items' => $items->map(fn ($i) => [
                'id' => $i->restaurant_menu_item_id,
                'name' => $i->menuItem?->name ?? 'Deleted',
                'category' => $i->menuItem?->category?->name ?? '',
                'quantity' => (int) $i->total_qty,
                'revenue' => (float) $i->total_revenue,
            ]),
        ]);
    }

    public function waiterPerformance(Request $request)
    {
        $request->validate([
            'period' => 'required|in:today,week,month',
            'date_from' => 'required_if:period,custom|date',
            'date_to' => 'required_if:period,custom|date|after_or_equal:date_from',
        ]);

        $now = now();
        $from = match ($request->period) {
            'today' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
        };
        $to = match ($request->period) {
            'today', 'week', 'month' => $now->copy()->endOfDay(),
        };

        $waiters = WaiterShift::with('user')
            ->withCount(['orders' => fn ($q) => $q->where('status', 'completed')])
            ->withSum(['orders' => fn ($q) => $q->where('status', 'completed')], 'grand_total')
            ->whereBetween('clock_in', [$from, $to])
            ->get()
            ->map(fn ($s) => [
                'waiter_id' => $s->user_id,
                'waiter_name' => $s->user?->name ?? 'Deleted',
                'shifts' => 1,
                'orders_taken' => $s->orders_count,
                'total_sales' => (float) $s->orders_sum_grand_total,
                'hours_worked' => $s->clock_out
                    ? round($s->clock_out->diffInHours($s->clock_in), 1)
                    : null,
            ]);

        // Aggregate by waiter
        $aggregated = $waiters->groupBy('waiter_id')->map(fn ($items) => [
            'waiter_name' => $items->first()['waiter_name'],
            'shifts' => $items->sum('shifts'),
            'orders_taken' => $items->sum('orders_taken'),
            'total_sales' => $items->sum('total_sales'),
            'hours_worked' => $items->sum('hours_worked'),
        ])->values();

        return response()->json([
            'success' => true,
            'waiters' => $aggregated,
        ]);
    }

    public function shiftReport(Request $request, $shiftId)
    {
        $shift = WaiterShift::with('user', 'orders.payments')->findOrFail($shiftId);
        $completedOrders = $shift->orders->where('status', 'completed');
        $totalSales = $completedOrders->sum('grand_total');
        $paymentMethods = Payment::whereIn('restaurant_order_id', $completedOrders->pluck('id'))
            ->completed()
            ->get()
            ->groupBy('method')
            ->map(fn ($items) => [
                'count' => $items->count(),
                'total' => $items->sum('amount'),
            ]);
        $expectedEndingCash = $shift->starting_cash + $totalSales;
        $discrepancy = $shift->ending_cash ? $shift->ending_cash - $expectedEndingCash : null;

        return response()->json([
            'success' => true,
            'shift' => [
                'id' => $shift->id,
                'waiter' => $shift->user?->name ?? 'N/A',
                'clock_in' => $shift->clock_in,
                'clock_out' => $shift->clock_out,
                'starting_cash' => $shift->starting_cash,
                'ending_cash' => $shift->ending_cash,
                'expected_ending_cash' => $expectedEndingCash,
                'discrepancy' => $discrepancy,
                'total_sales' => $totalSales,
                'order_count' => $completedOrders->count(),
                'status' => $shift->status,
            ],
            'payment_methods' => $paymentMethods,
            'orders' => $completedOrders->map(fn ($o) => [
                'id' => $o->id,
                'type' => $o->type,
                'customer' => $o->customer_name ?? 'Walk-in',
                'total' => $o->grand_total,
                'paid_at' => $o->updated_at,
            ]),
        ]);
    }

    // ─── Table CRUD ───────────────────────────────────────────────────

    public function tableIndex()
    {
        $tables = Table::all();

        return view('restaurant::admin.tables', compact('tables'));
    }

    public function tableStore(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:restaurant_tables,number',
            'capacity' => 'nullable|integer|min:1',
            'section' => 'nullable|string|max:100',
        ]);

        Table::create($validated);

        return redirect()->route('restaurant.admin.tables')->with('success', 'Table added successfully.');
    }

    public function tableUpdate(Request $request, Table $table)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:restaurant_tables,number,'.$table->id,
            'capacity' => 'nullable|integer|min:1',
            'section' => 'nullable|string|max:100',
        ]);

        $table->update($validated);

        return redirect()->route('restaurant.admin.tables')->with('success', 'Table updated successfully.');
    }

    public function tableDestroy(Table $table)
    {
        $table->delete();

        return redirect()->route('restaurant.admin.tables')->with('success', 'Table deleted successfully.');
    }

    // ─── Phase 3: Kitchen Display ────────────────────────────────────

    public function kdsOrders()
    {
        $orders = Order::with('orderItems.menuItem.category')
            ->where(function ($q) {
                $q->whereIn('tracking_status', ['pending', 'preparing', 'ready'])
                    ->orWhere(function ($q2) {
                        $q2->whereNull('tracking_status')->where('status', 'pending');
                    });
            })
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($o) {
                return $o->tracking_status ?? 'unaccepted';
            });

        return view('restaurant::admin.kitchen_display', compact('orders'));
    }

    public function kdsData()
    {
        $orders = Order::with('orderItems.menuItem.category')
            ->where(function ($q) {
                $q->whereIn('tracking_status', ['pending', 'preparing', 'ready'])
                    ->orWhere(function ($q2) {
                        $q2->whereNull('tracking_status')->where('status', 'pending');
                    });
            })
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'now' => now()->toIso8601String(),
        ]);
    }

    public function kdsAcceptOrder(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Order is not pending.'], 422);
        }

        $order->status = 'accepted';
        $order->tracking_status = 'preparing';
        $order->save();

        return response()->json(['success' => true]);
    }

    // ─── Phase 4: Inventory / Stock Control ──────────────────────────

    public function stockIndex()
    {
        $items = StockItem::withCount('recipeItems')->get();
        $lowStock = $items->filter(fn ($i) => $i->isLowStock());

        return view('restaurant::admin.stock_index', compact('items', 'lowStock'));
    }

    public function stockStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        StockItem::create($validated);

        return redirect()->route('restaurant.admin.stock.index')->with('success', 'Stock item created.');
    }

    public function stockUpdate(Request $request, StockItem $stockItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $stockItem->update($validated);

        return redirect()->route('restaurant.admin.stock.index')->with('success', 'Stock item updated.');
    }

    public function stockDestroy(StockItem $stockItem)
    {
        $stockItem->delete();

        return redirect()->route('restaurant.admin.stock.index')->with('success', 'Stock item deleted.');
    }

    public function recipeStore(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'restaurant_stock_item_id' => 'required|exists:restaurant_stock_items,id',
            'quantity' => 'required|numeric|min:0.001',
        ]);

        RecipeItem::updateOrCreate(
            [
                'restaurant_menu_item_id' => $menuItem->id,
                'restaurant_stock_item_id' => $validated['restaurant_stock_item_id'],
            ],
            ['quantity' => $validated['quantity']]
        );

        return redirect()->back()->with('success', 'Recipe ingredient added.');
    }

    public function recipeDestroy(RecipeItem $recipeItem)
    {
        $recipeItem->delete();

        return redirect()->back()->with('success', 'Ingredient removed from recipe.');
    }

    public function stockMovementStore(Request $request)
    {
        $validated = $request->validate([
            'restaurant_stock_item_id' => 'required|exists:restaurant_stock_items,id',
            'type' => 'required|in:purchase,usage,wastage,adjustment',
            'quantity' => 'required|numeric|min:0.001',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $stockItem = StockItem::findOrFail($validated['restaurant_stock_item_id']);

        if (in_array($validated['type'], ['usage', 'wastage'])) {
            $validated['quantity'] = -abs($validated['quantity']);
        }

        $stockItem->increment('stock_quantity', $validated['quantity']);

        $validated['user_id'] = auth()->id();
        StockMovement::create($validated);

        return redirect()->back()->with('success', 'Stock movement recorded.');
    }

    // ─── Phase 5: Customer CRM ───────────────────────────────────────

    public function customerIndex()
    {
        $customers = Customer::withCount('orders')
            ->orderByDesc('total_spent')
            ->paginate(20);

        return view('restaurant::admin.customers', compact('customers'));
    }

    public function customerShow(Customer $customer)
    {
        $customer->loadCount('orders');
        $orders = Order::where('customer_phone', $customer->phone)
            ->with('orderItems.menuItem', 'payments')
            ->latest()
            ->paginate(10);

        return view('restaurant::admin.customer_show', compact('customer', 'orders'));
    }

    public function customerStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:restaurant_customers,phone',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        Customer::create($validated);

        return redirect()->route('restaurant.admin.customers')->with('success', 'Customer created.');
    }

    // ─── Admin report views ─────────────────────────────────────────

    public function reportSales()
    {
        return view('restaurant::admin.reports.sales');
    }

    public function exportSalesCsv(Request $request)
    {
        $request->validate([
            'period' => 'required|in:today,week,month,custom',
            'date_from' => 'required_if:period,custom|date',
            'date_to' => 'required_if:period,custom|date|after_or_equal:date_from',
        ]);

        $now = now();
        $from = match ($request->period) {
            'today' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            'custom' => $request->date('date_from')->startOfDay(),
        };
        $to = match ($request->period) {
            'today', 'week', 'month' => $now->copy()->endOfDay(),
            'custom' => $request->date('date_to')->endOfDay(),
        };

        $orders = Order::with('payments', 'orderItems.menuItem', 'shift.user')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'completed')
            ->get();

        $filename = 'sales-report-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order #', 'Date', 'Type', 'Customer', 'Items', 'Subtotal', 'Discount', 'VAT', 'Total', 'Payment Method', 'Cashier']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->created_at->format('Y-m-d H:i'),
                    $order->type,
                    $order->customer_name ?? 'Walk-in',
                    $order->orderItems->sum('quantity'),
                    $order->subtotal,
                    $order->discount,
                    $order->vat,
                    $order->grand_total,
                    $order->payments->first()?->method ?? 'N/A',
                    $order->shift?->user?->name ?? 'N/A',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPopularCsv(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,custom',
            'date_from' => 'required_if:period,custom|date',
            'date_to' => 'required_if:period,custom|date|after_or_equal:date_from',
        ]);

        $now = now();
        $from = match ($request->period) {
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            'custom' => $request->date('date_from')->startOfDay(),
        };
        $to = match ($request->period) {
            'week', 'month' => $now->copy()->endOfDay(),
            'custom' => $request->date('date_to')->endOfDay(),
        };

        $items = OrderItem::selectRaw('restaurant_menu_item_id, SUM(quantity) as total_qty')
            ->join('restaurant_menu_items as mi', 'mi.id', '=', 'restaurant_order_items.restaurant_menu_item_id')
            ->join('restaurant_orders as o', 'o.id', '=', 'restaurant_order_items.restaurant_order_id')
            ->whereBetween('o.created_at', [$from, $to])
            ->where('o.status', 'completed')
            ->groupBy('restaurant_menu_item_id')
            ->orderByDesc('total_qty')
            ->get()
            ->load('menuItem.category');

        $filename = 'popular-items-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Item', 'Category', 'Quantity Sold']);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->menuItem?->name ?? 'Deleted',
                    $item->menuItem?->category?->name ?? '',
                    (int) $item->total_qty,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
