<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\MenuItem;

class MenuItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::with('category');

        if ($propertyId = app(PropertyService::class)->id()) {
            $query->where('property_id', $propertyId);
        }

        if ($request->filled('category_id')) {
            $query->where('restaurant_menu_categories_id', $request->category_id);
        }

        $items = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'restaurant_menu_categories_id' => 'required|exists:restaurant_menu_categories,id',
            'is_available' => 'boolean',
        ]);

        if ($propertyId = app(PropertyService::class)->id()) {
            $validated['property_id'] = $propertyId;
        }
        $validated['is_available'] = $validated['is_available'] ?? true;

        $item = MenuItem::create($validated);
        $item->load('category');

        return response()->json([
            'message' => 'Menu item created.',
            'data' => $item,
        ], 201);
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        $menuItem->load('category');

        return response()->json(['data' => $menuItem]);
    }

    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'restaurant_menu_categories_id' => 'sometimes|exists:restaurant_menu_categories,id',
            'is_available' => 'boolean',
        ]);

        $menuItem->update($validated);
        $menuItem->load('category');

        return response()->json([
            'message' => 'Menu item updated.',
            'data' => $menuItem,
        ]);
    }

    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $menuItem->delete();

        return response()->json(['message' => 'Menu item deleted.'], 204);
    }
}
