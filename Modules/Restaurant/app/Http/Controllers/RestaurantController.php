<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Services\PostingService;
use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Restaurant\Models\Customer;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Models\RecipeItem;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Models\StockItem;
use Modules\Restaurant\Models\StockMovement;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Models\WaiterShift;
use Modules\Restaurant\Services\RestaurantReportService;

class RestaurantController extends Controller
{
    protected $validTypes = ['table', 'room', 'online'];

    public function index()
    {
        $enableRoomService = RestaurantSetting::getValue('enable_room_service', '0') === '1';

        $sources = [
            'table' => [
                'label' => 'Dine-In Tables',
                'items' => Table::all()->map(function ($table) {
                    return ['id' => $table->id, 'number' => $table->number];
                }),
            ],
        ];

        if ($enableRoomService) {
            $sources['room'] = [
                'label' => 'Room Service',
                'items' => Room::all()->map(function ($room) {
                    return ['id' => $room->id, 'name' => $room->name];
                }),
            ];
        }

        return view('restaurant::index', compact('sources', 'enableRoomService'));
    }

    public function selectSource(Request $request)
    {
        $request->validate([
            'type' => 'required|in:table,room',
            'source_id' => 'required|integer',
        ]);

        $type = $request->input('type');
        $sourceId = $request->input('source_id');

        if ($type === 'table' && ! Table::find($sourceId)) {
            return redirect()->back()->with('error', 'Invalid table selected.');
        }
        if ($type === 'room' && ! Room::find($sourceId)) {
            return redirect()->back()->with('error', 'Invalid room selected.');
        }

        return redirect()->route('restaurant.menu', ['type' => $type, 'source' => $sourceId]);
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
            'quantity' => 'required|integer|min:1',
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

    public function addToOrder(Request $request, $type, $source = null)
    {
        if (! in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'order' => 'required|array',
            'order.*.item_id' => 'required|exists:restaurant_menu_items,id',
            'order.*.quantity' => 'required|integer|min:1',
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

        $cartKey = $type.'_cart';
        session()->put($cartKey, $request->input('order'));

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

        return view('restaurant::cart', compact('cart', 'items', 'type', 'sourceModel'));
    }

    public function updateCart(Request $request, $type, $source = null)
    {
        if (! in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'index' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1',
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

        if ($type === 'online') {
            $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'delivery_address' => 'required|string|max:1000',
            ]);
        }

        $cartKey = $type.'_cart';
        $cart = session()->get($cartKey, []);
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
        $vatRate = (float) (RestaurantSetting::getValue('vat_rate', '7.5'));
        $vat = $subtotal * $vatRate / 100;

        $orderData = [
            'type' => $type,
            'status' => 'pending',
            'tracking_status' => $type === 'online' || $type === 'room' ? 'pending' : null,
            'subtotal' => $subtotal,
            'vat' => $vat,
            'vat_rate' => $vatRate,
            'grand_total' => $subtotal + $vat,
        ];

        if ($type === 'table' || $type === 'room') {
            $orderData['source_id'] = $sourceModel->id;
        }
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
                'instructions' => $item['instructions'],
            ]);
        }

        session()->forget($cartKey);

        return redirect()->route(
            $type === 'online' ? 'restaurant.online.order.confirm' : 'restaurant.order.confirm',
            $type === 'online' ? ['order' => $order->id] : ['type' => $type, 'source' => $source, 'order' => $order->id]
        )->with('success', 'Order placed successfully!');
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

        return view('restaurant::confirm', compact('order', 'type', 'sourceModel'));
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
            $orders = Order::where('type', 'online')
                ->where('customer_phone', $phone)
                ->with('orderItems.menuItem')
                ->get();
        }

        return view('restaurant::orders', compact('orders', 'phone'));
    }

    /** Waiter Dashboard and Order Management start */
    public function waiterDashboard()
    {
        $enableRoomService = RestaurantSetting::getValue('enable_room_service', '0') === '1';

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

        return view('restaurant::waiter.dashboard', compact('pendingOrders', 'activeOrders', 'paidOrders', 'categories', 'tables', 'occupiedTableIds', 'enableRoomService'));
    }

    public function waiterDashboardData()
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

        $occupiedTableIds = Order::whereIn('status', ['pending', 'accepted'])
            ->where('type', 'table')
            ->pluck('source_id')
            ->unique()
            ->toArray();

        return response()->json([
            'success' => true,
            'pending_orders' => $pendingOrders,
            'active_orders' => $activeOrders,
            'paid_orders' => $paidOrders,
            'occupied_table_ids' => $occupiedTableIds,
            'now' => now()->toIso8601String(),
        ]);
    }

    public function posAddToCart(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:restaurant_menu_items,id',
            'quantity' => 'required|integer|min:1',
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
            $cart[$existing]['quantity'] += $validated['quantity'];
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
            'quantity' => 'required|integer|min:1',
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
            'source_id' => 'required_if:source_type,table|nullable|integer',
            'customer_name' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
        ]);

        $cart = session()->get('pos_cart', []);
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        $itemIds = array_column($cart, 'item_id');
        $validItems = MenuItem::whereIn('id', $itemIds)->pluck('id')->toArray();
        $invalidItems = array_diff($itemIds, $validItems);
        if (! empty($invalidItems)) {
            return response()->json(['success' => false, 'message' => 'Some items are no longer available.'], 422);
        }

        $subtotal = collect($cart)->sum(fn ($i) => $i['price'] * $i['quantity']);

        $discount = (float) ($validated['discount'] ?? 0);
        $discountType = $validated['discount_type'] ?? null;
        $discountAmount = $discountType === 'percentage' ? $subtotal * $discount / 100 : $discount;

        $vatRate = (float) (RestaurantSetting::getValue('vat_rate', '7.5'));
        $vat = ($subtotal - $discountAmount) * $vatRate / 100;

        $serviceChargeRate = (float) (RestaurantSetting::getValue('service_charge_rate', '0'));
        $serviceCharge = $serviceChargeRate > 0 ? ($subtotal - $discountAmount) * $serviceChargeRate / 100 : 0;

        $grandTotal = $subtotal - $discountAmount + $vat + $serviceCharge;

        $shift = WaiterShift::where('user_id', auth()->id())->where('status', 'open')->first();

        $orderData = [
            'type' => $validated['source_type'],
            'source_id' => $validated['source_type'] === 'table' ? $validated['source_id'] : null,
            'status' => 'pending',
            'tracking_status' => 'pending',
            'customer_name' => $validated['customer_name'] ?? ($validated['source_type'] === 'table' ? 'Table '.Table::find($validated['source_id'])?->number : 'Walk-in'),
            'shift_id' => $shift?->id,
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'discount_type' => $discountType,
            'vat' => $vat,
            'vat_rate' => $vatRate,
            'grand_total' => $grandTotal,
        ];

        $order = Order::create($orderData);

        foreach ($cart as $item) {
            OrderItem::create([
                'restaurant_order_id' => $order->id,
                'restaurant_menu_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
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
            ] : null,
        ]);
    }

    public function startShift(Request $request)
    {
        $validated = $request->validate([
            'starting_cash' => 'nullable|numeric|min:0',
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
            'clock_in' => now(),
            'starting_cash' => $validated['starting_cash'] ?? 0,
            'status' => 'open',
        ]);

        return response()->json(['success' => true, 'shift' => [
            'id' => $shift->id,
            'clock_in' => $shift->clock_in->format('Y-m-d H:i:s'),
            'starting_cash' => $shift->starting_cash,
            'total_sales' => $shift->total_sales,
            'status' => $shift->status,
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
                'enable_room_service' => RestaurantSetting::getValue('enable_room_service', '0'),
                'paystack_public_key' => config('services.paystack.public'),
            ],
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
            'enable_room_service' => RestaurantSetting::getValue('enable_room_service', '0'),
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
            'enable_room_service' => 'required|in:0,1',
        ]);

        RestaurantSetting::setValue('vat_rate', $validated['vat_rate']);
        RestaurantSetting::setValue('service_charge_rate', $validated['service_charge_rate']);
        RestaurantSetting::setValue('discount_limit', $validated['discount_limit']);
        RestaurantSetting::setValue('shift_start_time', $validated['shift_start_time']);
        RestaurantSetting::setValue('shift_end_time', $validated['shift_end_time']);
        RestaurantSetting::setValue('enable_room_service', $validated['enable_room_service']);

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
        ]);

        $order = Order::findOrFail($order);
        $order->status = $request->input('status');
        if ($order->type === 'online') {
            $order->tracking_status = $request->input('tracking_status');
        }
        $order->save();

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
        $validated = $request->validate([
            'amount_tendered' => 'nullable|numeric|min:0',
            'method' => 'required|in:cash,card,mobile_money,transfer,room_charge',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'registration_id' => 'required_if:method,room_charge|nullable|integer',
            'split_group' => 'nullable|string|max:10',
        ]);

        if ($validated['method'] === 'room_charge' && ! $validated['registration_id']) {
            return response()->json(['success' => false, 'message' => 'Registration ID is required for room charge.'], 422);
        }

        if ($validated['method'] === 'room_charge' && RestaurantSetting::getValue('enable_room_service', '0') !== '1') {
            return response()->json(['success' => false, 'message' => 'Room service is not enabled.'], 403);
        }

        // Calculate amount based on split_group
        if (! empty($validated['split_group'])) {
            $groupItems = $order->orderItems()->with('menuItem')->where('split_group', $validated['split_group'])->get();
            if ($groupItems->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No items found for split group '.$validated['split_group']], 422);
            }
            $groupSubtotal = $groupItems->sum(fn ($item) => (float) $item->menuItem->price * $item->quantity);
            $proportion = $groupSubtotal / (float) $order->subtotal;
            $amount = round((float) $order->grand_total * $proportion, 2);
        } else {
            $amount = (float) $order->grand_total;
        }

        $tendered = $validated['amount_tendered'] ?? $amount;
        $change = max(0, $tendered - $amount);

        $paymentData = [
            'restaurant_order_id' => $order->id,
            'amount' => $amount,
            'method' => $validated['method'],
            'reference' => $validated['reference'] ?? null,
            'change_due' => $change,
            'status' => 'completed',
            'paid_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ];

        // Room charge: create folio charge + record AR
        if ($validated['method'] === 'room_charge') {
            $registration = Registration::findOrFail($validated['registration_id']);
            $chargeType = ChargeType::firstOrCreate(
                ['code' => 'restaurant'],
                ['name' => 'Restaurant', 'account_code' => '4100']
            );

            $itemCount = $order->orderItems->count();
            $description = "Order #{$order->id} - {$itemCount} item(s)";
            if (! empty($validated['split_group'])) {
                $description .= " (Group {$validated['split_group']})";
            }

            $registration->folioCharges()->create([
                'charge_type_id' => $chargeType->id,
                'description' => $description,
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
                'posted_by' => auth()->id(),
            ]);

            $paymentData['registration_id'] = $registration->id;
            $paymentData['charge_type_id'] = $chargeType->id;
        }

        $payment = Payment::create($paymentData);

        // Mark order as fully paid if no remaining balance
        $order->refresh();
        if ($order->amount_due <= 0) {
            $order->status = 'completed';
            $order->tracking_status = 'paid';
            $order->save();
        }

        // Finance posting: all methods including room_charge (Debit AR / Credit Revenue)
        try {
            app(PostingService::class)
                ->recordSale('restaurant', (float) $payment->amount, $payment->method, 'restaurant_payment', $payment->id);
        } catch (\Throwable $e) {
            report($e);
        }

        // Track customer if phone exists
        if ($order->customer_phone) {
            $customer = Customer::firstOrCreate(
                ['phone' => $order->customer_phone],
                ['name' => $order->customer_name ?? 'Unknown']
            );
            $customer->increment('visit_count');
            $customer->increment('total_spent', $payment->amount);
            $customer->increment('loyalty_points', (int) floor($payment->amount / 100));
        }

        $order->load('payments');

        return response()->json([
            'success' => true,
            'payment' => $payment,
            'change' => $change,
            'order' => $order,
        ]);
    }

    // ─── Phase 2: Guest Lookup (Charge to Room) ──────────────────────

    public function guestLookup(Request $request)
    {
        if (RestaurantSetting::getValue('enable_room_service', '0') !== '1') {
            return response()->json(['success' => false, 'message' => 'Room service is not enabled'], 403);
        }

        $request->validate([
            'room' => 'required|string|max:20',
        ]);

        $unit = RoomUnit::where('room_number', $request->input('room'))->first();

        if (! $unit) {
            return response()->json(['success' => false, 'message' => 'Room not found'], 404);
        }

        $registration = Registration::where('room_unit_id', $unit->id)
            ->whereIn('stay_status', ['checked_in', 'reserved'])
            ->with('guest')
            ->latest()
            ->first();

        if (! $registration) {
            return response()->json(['success' => false, 'message' => 'No active registration found for this room'], 404);
        }

        return response()->json([
            'success' => true,
            'registration' => [
                'id' => $registration->id,
                'guest_name' => $registration->guest?->full_name ?? 'Unknown',
                'room_number' => $unit->room_number,
                'check_in' => $registration->check_in,
                'check_out' => $registration->check_out,
            ],
        ]);
    }

    // ─── Phase 3: Split Order ────────────────────────────────────────

    public function splitOrder(Request $request, Order $order)
    {
        $validated = $request->validate([
            'type' => 'required|in:even,items',
            'count' => 'required_if:type,even|nullable|integer|min:2|max:10',
            'items' => 'required_if:type,items|nullable|array',
            'items.*.id' => 'required_with:items|integer|exists:restaurant_order_items,id',
            'items.*.group' => 'required_with:items|string|max:10',
        ]);

        $items = $order->orderItems()->with('menuItem')->get();

        if ($validated['type'] === 'even') {
            $count = $validated['count'];
            $groups = range('A', chr(ord('A') + $count - 1));

            foreach ($items as $index => $item) {
                $groupIndex = $index % $count;
                $item->update(['split_group' => $groups[$groupIndex]]);
            }
        } else {
            $itemMap = collect($validated['items'])->keyBy('id');
            foreach ($items as $item) {
                $group = $itemMap->has($item->id) ? $itemMap[$item->id]['group'] : null;
                $item->update(['split_group' => $group]);
            }
        }

        $order->load('orderItems.menuItem');

        return response()->json([
            'success' => true,
            'order' => $order,
            'groups' => $order->orderItems->groupBy('split_group')->map(fn ($groupItems) => [
                'items' => $groupItems,
                'subtotal' => $groupItems->sum(fn ($i) => (float) $i->menuItem->price * $i->quantity),
            ]),
        ]);
    }

    // ─── Phase 4: Reports & Analytics ────────────────────────────────

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

        $report = app(RestaurantReportService::class)->salesReport($from, $to);

        return response()->json(array_merge(['success' => true], $report));
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

        $items = app(RestaurantReportService::class)->popularItems($from, $to, $limit);

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

        $waiters = app(RestaurantReportService::class)->waiterPerformance($from, $to);

        return response()->json([
            'success' => true,
            'waiters' => $waiters,
        ]);
    }

    public function shiftReport(Request $request, $shiftId)
    {
        $report = app(RestaurantReportService::class)->shiftReport($shiftId);

        return response()->json(array_merge(['success' => true], $report));
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

    public function kdsUpdateStatus(Request $request, Order $order)
    {
        $request->validate([
            'tracking_status' => 'required|in:preparing,ready,served',
        ]);

        $order->tracking_status = $request->input('tracking_status');
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
