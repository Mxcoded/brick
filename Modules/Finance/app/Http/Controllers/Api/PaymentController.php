<?php

namespace Modules\Finance\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Restaurant\Models\Payment;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['order']);

        if ($propertyId = app(PropertyService::class)->id()) {
            $query->where('property_id', $propertyId);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date.' 23:59:59');
        }

        $payments = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:restaurant_orders,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,transfer,room_charge,online',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($propertyId = app(PropertyService::class)->id()) {
            $validated['property_id'] = $propertyId;
        }

        $payment = Payment::create($validated);
        $payment->load('order');

        return response()->json([
            'message' => 'Payment recorded.',
            'data' => $payment,
        ], 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load('order');

        return response()->json(['data' => $payment]);
    }
}
