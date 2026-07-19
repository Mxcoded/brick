<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\Table;

class TableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Table::query();

        if ($propertyId = app(PropertyService::class)->id()) {
            $query->where('property_id', $propertyId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tables = $query->orderBy('number')->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $tables->items(),
            'meta' => [
                'current_page' => $tables->currentPage(),
                'last_page' => $tables->lastPage(),
                'per_page' => $tables->perPage(),
                'total' => $tables->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number' => 'required|string|max:20',
        ]);

        if ($propertyId = app(PropertyService::class)->id()) {
            $validated['property_id'] = $propertyId;
        }

        $table = Table::create($validated);

        return response()->json([
            'message' => 'Table created.',
            'data' => $table,
        ], 201);
    }

    public function show(Table $table): JsonResponse
    {
        return response()->json(['data' => $table]);
    }

    public function update(Request $request, Table $table): JsonResponse
    {
        $validated = $request->validate([
            'number' => 'sometimes|string|max:20',
        ]);

        $table->update($validated);

        return response()->json([
            'message' => 'Table updated.',
            'data' => $table,
        ]);
    }

    public function destroy(Table $table): JsonResponse
    {
        $table->delete();

        return response()->json(['message' => 'Table deleted.'], 204);
    }
}
