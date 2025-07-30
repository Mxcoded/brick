<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\Table;
use Illuminate\Support\Facades\Session; // Assuming you have an Order model and OrderItem model

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
    /**
     * Display the restaurant menu.
     */
    public function menu($table)
    {
       
        $categories = MenuCategory::with('menuItems')->get();
        return view('restaurant::menu', compact('categories', 'table'));
    }

    /**
     * Add a menu item to the cart.
     */
    public function addToCart(Request $request, $table)
    {
        $request->validate([
            'item_id' => 'required|exists:menu_items,id',
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
    /**
     * View the cart.
     */
    public function viewCart($table)
    {
        $cart = session()->get('cart', []);
        $itemIds = array_column($cart, 'item_id');
        $items = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');
        return view('restaurant::cart', compact('cart', 'items', 'table'));
    }

    public function submitOrder($table)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $order = Order::create([
            'table_id' => $table,
            'status' => 'pending',
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'instructions' => $item['instructions'],
            ]);
        }

        session()->forget('cart');
        return redirect()->route('restaurant.menu', $table)->with('success', 'Order placed successfully!');
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

    public function addMenuCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:restaurant_menu_categories,id',
        ]);

        $category = new MenuCategory();
        $category->name = $request->input('name');
        $category->parent_id = $request->input('parent_id');
        $category->save();

        return redirect()->back()->with('success', 'Menu category added successfully!');
    }
}
