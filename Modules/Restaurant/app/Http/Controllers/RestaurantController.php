<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\Table;
use Illuminate\Support\Facades\Session;

class RestaurantController extends Controller
{
    public function index()
    {
        $tables = Table::all();
        return view('restaurant::index', compact('tables'));
    }

    public function selectTable(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:restaurant_tables,id',
        ]);

        return redirect()->route('restaurant.menu', $request->table_id);
    }

    public function menu($table, $categoryId = null)
    {
        if ($categoryId) {
            $category = MenuCategory::with('menuItems')->findOrFail($categoryId);

        } else {
            $categories = MenuCategory::with('menuItems')->get();
            $category_names = $categories->where('parent_id', NULL)->pluck('name')->toArray();

            
        }
        return view('restaurant::menu', compact('categories', 'category_names', 'table'));
    }

    public function addToCart(Request $request, $table)
    {
        $request->validate([
            'item_id' => 'required|exists:restaurant_menu_items,id',
            'quantity' => 'required|integer|min:1',
            'instructions' => 'nullable|string|max:255',
        ]);

        $cart = session()->get('cart', []);
        $cart[] = [
            'item_id' => $request->input('item_id'),
            'quantity' => $request->input('quantity'),
            'instructions' => $request->input('instructions', ''),
        ];
        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Item added to cart!');
    }

    public function addOrder(Request $request, $table)
    {
        $request->validate([
            'order' => 'required',
        ]);

        session()->put('cart', []);
        $cart = session()->get('cart', []);
        $cart[] = [
            'order' => $request->input('order'),
        ];
        session()->put('cart', $cart);

        return response()->json(['success' => 'Data received successfully!', 'data' => $request->input('order')]);
    }

    public function viewCart($table)
    {
        $table = Table::findOrFail($table);
        $cart = session()->get('cart', []);
        $cart = array_values($cart[0]); // Re-index the array to avoid gaps in indices
        $cart = $cart[0];
        $itemIds = array_column($cart, 'item_id');
        $items = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');
        //dd($itemIds, $items, $cart);
        return view('restaurant::cart', compact('cart', 'items', 'table'));
    }

    public function updateCart(Request $request, $table)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);
        $index = $request->input('index');

        if (isset($cart[$index])) {
            $cart[$index]['quantity'] = $request->input('quantity');
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Cart updated!');
        }

        return redirect()->back()->with('error', 'Invalid cart item.');
    }

    public function removeFromCart(Request $request, $table)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $cart = session()->get('cart', []);
        $index = $request->input('index');

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart);
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Item removed from cart!');
        }

        return redirect()->back()->with('error', 'Invalid cart item.');
    }

    public function submitOrder($table)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $itemIds = array_column($cart, 'item_id');
        $validItems = MenuItem::whereIn('id', $itemIds)->pluck('id')->toArray();
        $invalidItems = array_diff($itemIds, $validItems);
        if (!empty($invalidItems)) {
            return redirect()->back()->with('error', 'Some items in your cart are no longer available.');
        }

        $order = Order::create([
            'restaurant_table_id' => $table,
            'type' => 'table',
            'status' => 'pending',
            'tracking_status' => null,
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'restaurant_order_id' => $order->id,
                'restaurant_menu_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'instructions' => $item['instructions'],
            ]);
        }

        session()->forget('cart');
        return redirect()->route('restaurant.order.confirm', ['table' => $table, 'order' => $order->id])
            ->with('success', 'Order placed successfully!');
    }

    public function confirmOrder($table, $order)
    {
        $order = Order::with('orderItems.menuItem', 'table')->findOrFail($order);
        if ($order->restaurant_table_id != $table) {
            abort(403, 'Unauthorized access to this order.');
        }
        return view('restaurant::order.confirm', compact('order', 'table'));
    }

    public function waiterDashboard()
    {
        $orders = Order::where('status', 'pending')
            ->where('type', 'table')
            ->with('orderItems.menuItem', 'table')
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
        $orders = Order::with('orderItems.menuItem', 'table')->get();
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

    public function onlineMenu()
    {
        $categories = MenuCategory::with('menuItems')->get();
        return view('restaurant::online.menu', compact('categories'));
    }

    public function addToOnlineCart(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:restaurant_menu_items,id',
            'quantity' => 'required|integer|min:1',
            'instructions' => 'nullable|string|max:255',
        ]);

        $cart = session()->get('online_cart', []);
        $cart[] = [
            'item_id' => $request->input('item_id'),
            'quantity' => $request->input('quantity'),
            'instructions' => $request->input('instructions', ''),
        ];
        session()->put('online_cart', $cart);

        return redirect()->back()->with('success', 'Item added to cart!');
    }

    public function viewOnlineCart()
    {
        $cart = session()->get('online_cart', []);
        $itemIds = array_column($cart, 'item_id');
        $items = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');
        return view('restaurant::online.cart', compact('cart', 'items'));
    }

    public function updateOnlineCart(Request $request)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('online_cart', []);
        $index = $request->input('index');

        if (isset($cart[$index])) {
            $cart[$index]['quantity'] = $request->input('quantity');
            session()->put('online_cart', $cart);
            return redirect()->back()->with('success', 'Cart updated!');
        }

        return redirect()->back()->with('error', 'Invalid cart item.');
    }

    public function removeFromOnlineCart(Request $request)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $cart = session()->get('online_cart', []);
        $index = $request->input('index');

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart);
            session()->put('online_cart', $cart);
            return redirect()->back()->with('success', 'Item removed from cart!');
        }

        return redirect()->back()->with('error', 'Invalid cart item.');
    }

    public function submitOnlineOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:1000',
        ]);

        $cart = session()->get('online_cart', []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $itemIds = array_column($cart, 'item_id');
        $validItems = MenuItem::whereIn('id', $itemIds)->pluck('id')->toArray();
        $invalidItems = array_diff($itemIds, $validItems);
        if (!empty($invalidItems)) {
            return redirect()->back()->with('error', 'Some items in your cart are no longer available.');
        }

        $order = Order::create([
            'type' => 'online',
            'customer_name' => $request->input('customer_name'),
            'customer_phone' => $request->input('customer_phone'),
            'delivery_address' => $request->input('delivery_address'),
            'status' => 'pending',
            'tracking_status' => 'pending',
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'restaurant_order_id' => $order->id,
                'restaurant_menu_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'instructions' => $item['instructions'],
            ]);
        }

        session()->forget('online_cart');
        return redirect()->route('restaurant.online.order.confirm', ['order' => $order->id])
            ->with('success', 'Order placed successfully!');
    }

    public function confirmOnlineOrder($order)
    {
        $order = Order::with('orderItems.menuItem')->findOrFail($order);
        if ($order->type !== 'online') {
            abort(403, 'Unauthorized access to this order.');
        }
        return view('restaurant::online.confirm', compact('order'));
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

        return view('restaurant::online.orders', compact('orders', 'phone'));
    }
}
