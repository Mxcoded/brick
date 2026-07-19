<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items.menuItem', 'table'])
            ->where('property_id', PropertyService::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:dine_in,takeaway,room_service',
            'table_id' => 'nullable|exists:restaurant_tables,id',
            'registration_id' => 'nullable|exists:registrations,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $propertyId = PropertyService::id();
        $totalAmount = 0;
        $orderItems = [];

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $itemTotal = $menuItem->price * $item['quantity'];
            $totalAmount += $itemTotal;

            $orderItems[] = [
                'menu_item_id' => $item['menu_item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->price,
                'total_price' => $itemTotal,
                'notes' => $item['notes'] ?? null,
                'status' => 'pending',
            ];
        }

        $order = Order::create([
            'property_id' => $propertyId,
            'type' => $validated['type'],
            'table_id' => $validated['table_id'] ?? null,
            'registration_id' => $validated['registration_id'] ?? null,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($orderItems as $oi) {
            $order->items()->create($oi);
        }

        $order->load(['items.menuItem', 'table']);

        return response()->json([
            'message' => 'Order created.',
            'data' => $order,
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['items.menuItem', 'table', 'registration.guest', 'payments']);

        return response()->json(['data' => $order]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,served,completed,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);
        $order->load(['items.menuItem', 'table']);

        return response()->json([
            'message' => 'Order status updated.',
            'data' => $order,
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json([
                'message' => 'Cannot delete completed or cancelled order.',
            ], 422);
        }

        $order->items()->delete();
        $order->delete();

        return response()->json(['message' => 'Order deleted.'], 204);
    }
}
