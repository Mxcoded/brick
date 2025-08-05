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
            $categories = collect([$category]);
        } else {
            $categories = MenuCategory::with('menuItems')->get();
        }
        return view('restaurant::menu', compact('categories', 'table'));
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
        // dd(session()->get('cart'));

        return redirect()->back()->with('success', 'Item added to cart!');
    }

    public function viewCart($table)
    {
        $table = Table::findOrFail($table);
        $cart = session()->get('cart', []);
        $itemIds = array_column($cart, 'item_id');
        $items = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');
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
            $cart = array_values($cart); // Reindex array
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

        // Validate menu item IDs
        $itemIds = array_column($cart, 'item_id');
        $validItems = MenuItem::whereIn('id', $itemIds)->pluck('id')->toArray();
        $invalidItems = array_diff($itemIds, $validItems);
        if (!empty($invalidItems)) {
            return redirect()->back()->with('error', 'Some items in your cart are no longer available.');
        }

        $order = Order::create([
            'restaurant_table_id' => $table,
            'status' => 'pending',
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
            ->with('orderItems.menuItem', 'table')
            ->get();
        return view('restaurant::waiter.dashboard', compact('orders'));
    }

    public function acceptOrder($order)
    {
        $order = Order::findOrFail($order);
        $order->status = 'accepted';
        $order->save();
        return redirect()->back()->with('success', 'Order accepted!');
    }

    public function adminDashboard()
    {
        $categories = MenuCategory::get();
        $parent_categories = $categories->whereNull('parent_id');
        return view('restaurant::admin.dashboard', compact('categories', 'parent_categories'));
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
}
