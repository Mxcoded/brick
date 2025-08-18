<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\Table;
use Modules\Website\Models\Room;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RestaurantController extends Controller
{
    protected $validTypes = ['table', 'room', 'online'];

    public function index()
    {
        $sources = [
            'table' => [
                'label' => 'Dine-In Tables',
                'items' => Table::all()->map(function ($table) {
                    return ['id' => $table->id, 'number' => $table->number];
                }),
            ],
            'room' => [
                'label' => 'Room Service',
                'items' => Room::all()->map(function ($room) {
                    return ['id' => $room->id, 'name' => $room->name];
                }),
            ],
        ];
        return view('restaurant::index', compact('sources'));
    }

    public function selectSource(Request $request)
    {
        $request->validate([
            'type' => 'required|in:table,room',
            'source_id' => 'required|integer',
        ]);

        $type = $request->input('type');
        $sourceId = $request->input('source_id');

        if ($type === 'table' && !Table::find($sourceId)) {
            return redirect()->back()->with('error', 'Invalid table selected.');
        }
        if ($type === 'room' && !Room::find($sourceId)) {
            return redirect()->back()->with('error', 'Invalid room selected.');
        }

        return redirect()->route('restaurant.menu', ['type' => $type, 'source' => $sourceId]);
    }

    public function menu($type = 'online', $source = null, Request $request)
    {
        Log::info('Menu method called', [
            'type' => $type,
            'source' => $source,
            'url' => $request->fullUrl(),
            'route_parameters' => $request->route()->parameters(),
        ]);

        if (!in_array($type, $this->validTypes)) {
            Log::error('Invalid order type accessed: ' . $type);
            abort(404, 'Invalid order type.');
        }

        $sourceModel = null;
        if ($type === 'table') {
            $sourceModel = Table::find($source) ?? abort(404, 'Invalid table.');
        } elseif ($type === 'room') {
            $sourceModel = Room::find($source) ?? abort(404, 'Invalid room.');
        } elseif ($type === 'online' && $source) {
            Log::warning('Online order accessed with source: ' . $source);
            abort(404, 'Online orders do not require a source.');
        }

        try {
            $categoryId = $request->query('category');
            if ($categoryId) {
                $category = MenuCategory::with('menuItems')->find($categoryId);
                if (!$category) {
                    Session::flash('error', 'The selected category is not available.');
                    $categories = MenuCategory::with('menuItems')->get();
                } else {
                    $categories = collect([$category]);
                }
            } else {
                $categories = MenuCategory::with('menuItems')->get();
            }
            $category_names = $categories->whereNull('parent_id')->pluck('name')->toArray();
        } catch (\Exception $e) {
            Log::error('Menu loading error: ' . $e->getMessage());
            Session::flash('error', 'Unable to load menu. Please try again later.');
            $categories = collect();
            $category_names = [];
        }

        return view('restaurant::menu', compact('categories', 'category_names', 'type', 'sourceModel'));
    }

    public function addToCart(Request $request, $type, $source = null)
    {
        if (!in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'item_id' => 'required|exists:restaurant_menu_items,id',
            'quantity' => 'required|integer|min:1',
            'instructions' => 'nullable|string|max:255',
        ]);

        if ($type === 'table' && !Table::find($source)) {
            abort(404, 'Invalid table.');
        }
        if ($type === 'room' && !Room::find($source)) {
            abort(404, 'Invalid room.');
        }
        if ($type === 'online' && $source) {
            abort(404, 'Online orders do not require a source.');
        }

        $cartKey = $type . '_cart';
        $cart = session()->get($cartKey, []);
        $newItem = [
            'item_id' => $request->input('item_id'),
            'quantity' => $request->input('quantity'),
            'instructions' => $request->input('instructions', ''),
        ];

        // Check for duplicate item with same instructions
        if (collect($cart)->contains(fn($item) => $item['item_id'] == $newItem['item_id'] && $item['instructions'] == $newItem['instructions'])) {
            return redirect()->back()->with('error', 'Item already exists in the cart with the same instructions.');
        }

        $cart[] = $newItem;
        session()->put($cartKey, $cart);

        return redirect()->back()->with('success', 'Item added to cart!');
    }

    public function addToOrder(Request $request, $type, $source = null)
    {
        if (!in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'order' => 'required|array',
            'order.*.item_id' => 'required|exists:restaurant_menu_items,id',
            'order.*.quantity' => 'required|integer|min:1',
            'order.*.instructions' => 'nullable|string|max:255',
        ]);

        if ($type === 'table' && !Table::find($source)) {
            abort(404, 'Invalid table.');
        }
        if ($type === 'room' && !Room::find($source)) {
            abort(404, 'Invalid room.');
        }
        if ($type === 'online' && $source) {
            abort(404, 'Online orders do not require a source.');
        }

        $cartKey = $type . '_cart';
        session()->put($cartKey, $request->input('order'));
        return response()->json(['success' => 'Cart updated successfully!']);
    }

    public function viewCart($type, $source = null)
    {
        if (!in_array($type, $this->validTypes)) {
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

        $cartKey = $type . '_cart';
        $cart = session()->get($cartKey, []);
        $itemIds = array_column($cart, 'item_id');
        $items = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');

        return view('restaurant::cart', compact('cart', 'items', 'type', 'sourceModel'));
    }

    public function updateCart(Request $request, $type, $source = null)
    {
        if (!in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'index' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($type === 'table' && !Table::find($source)) {
            abort(404, 'Invalid table.');
        }
        if ($type === 'room' && !Room::find($source)) {
            abort(404, 'Invalid room.');
        }
        if ($type === 'online' && $source) {
            abort(404, 'Online orders do not require a source.');
        }

        $cartKey = $type . '_cart';
        $cart = session()->get($cartKey, []);
        $index = $request->input('index');

        if (isset($cart[$index])) {
            $cart[$index]['quantity'] = $request->input('quantity');
            session()->put($cartKey, $cart);
            return redirect()->back()->with('success', 'Cart updated!');
        }

        return redirect()->back()->with('error', 'Invalid cart item.');
    }

    public function removeFromCart(Request $request, $type, $source = null)
    {
        if (!in_array($type, $this->validTypes)) {
            abort(404, 'Invalid order type.');
        }

        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        if ($type === 'table' && !Table::find($source)) {
            abort(404, 'Invalid table.');
        }
        if ($type === 'room' && !Room::find($source)) {
            abort(404, 'Invalid room.');
        }
        if ($type === 'online' && $source) {
            abort(404, 'Online orders do not require a source.');
        }

        $cartKey = $type . '_cart';
        $cart = session()->get($cartKey, []);
        $index = $request->input('index');

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart);
            session()->put($cartKey, $cart);
            return redirect()->back()->with('success', 'Item removed from cart!');
        }

        return redirect()->back()->with('error', 'Invalid cart item.');
    }

    public function submitOrder(Request $request, $type, $source = null)
    {
        if (!in_array($type, $this->validTypes)) {
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

        $cartKey = $type . '_cart';
        $cart = session()->get($cartKey, []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $itemIds = array_column($cart, 'item_id');
        $validItems = MenuItem::whereIn('id', $itemIds)->pluck('id')->toArray();
        $invalidItems = array_diff($itemIds, $validItems);
        if (!empty($invalidItems)) {
            return redirect()->back()->with('error', 'Some items in your cart are no longer available.');
        }

        $orderData = [
            'type' => $type,
            'status' => 'pending',
            'tracking_status' => $type === 'online' || $type === 'room' ? 'pending' : null,
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

    public function confirmOrder($type, $source = null, $order)
    {
        if (!in_array($type, $this->validTypes)) {
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

    public function waiterDashboard()
    {
        $orders = Order::where('status', 'pending')
            ->whereIn('type', ['table', 'room'])
            ->with('orderItems.menuItem')
            ->get();
        return view('restaurant::waiter.dashboard', compact('orders'));
    }

    public function acceptOrder($order)
    {
        $order = Order::findOrFail($order);
        $order->status = 'accepted';
        $order->tracking_status = 'preparing';
        $order->save();
        return redirect()->route('restaurant.waiter.dashboard')->with('success', 'Order accepted!');
    }

    public function adminDashboard()
    {
        $categories = MenuCategory::get();
        $parent_categories = $categories->whereNull('parent_id');
        $orders = Order::with('orderItems.menuItem')->get();
        return view('restaurant::admin.dashboard', compact('categories', 'parent_categories', 'orders'));
    }

    public function addMenuCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_category' => 'nullable|exists:restaurant_menu_categories,id',
            'sub_category' => 'nullable|exists:restaurant_menu_categories,id',
        ]);

        $category = new MenuCategory();
        $category->name = $request->input('name');
        $category->parent_id = !empty($request->input('parent_category')) ? $request->input('parent_category') : $request->input('sub_category');
        $category->save();

        return redirect()->back()->with('success', 'Menu category added successfully!');
    }

    public function addMenuItem(Request $request)
    {
        $request->validate([
            'restaurant_menu_categories_id' => 'required|exists:restaurant_menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $menuItem = new MenuItem();
        $menuItem->restaurant_menu_categories_id = $request->input('restaurant_menu_categories_id');
        $menuItem->name = $request->input('name');
        $menuItem->description = $request->input('description');
        $menuItem->price = $request->input('price');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('dishes', 'public');
            $menuItem->image = $path;
        }

        $menuItem->save();

        return redirect()->back()->with('success', 'Menu item added successfully!');
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

    public function editMenuItem($item)
    {
        $menuItem = MenuItem::findOrFail($item);
        $categories = MenuCategory::all();
        return view('restaurant::admin.edit_item', compact('menuItem', 'categories'));
    }

    public function updateMenuItem(Request $request, $item)
    {
        $request->validate([
            'restaurant_menu_categories_id' => 'required|exists:restaurant_menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $menuItem = MenuItem::findOrFail($item);
        $menuItem->restaurant_menu_categories_id = $request->input('restaurant_menu_categories_id');
        $menuItem->name = $request->input('name');
        $menuItem->description = $request->input('description');
        $menuItem->price = $request->input('price');

        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $path = $request->file('image')->store('dishes', 'public');
            $menuItem->image = $path;
        }

        $menuItem->save();

        return redirect()->back()->with('success', 'Menu item updated successfully!');
    }
}
