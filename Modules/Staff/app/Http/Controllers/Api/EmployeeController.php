<?php

namespace Modules\Staff\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Staff\Models\Employee;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with('department')
            ->where('property_id', PropertyService::id());

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $employees = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $employees->items(),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string|max:255',
            'employee_id' => 'required|string|max:50|unique:employees,employee_id',
            'date_of_joining' => 'required|date',
        ]);

        $validated['property_id'] = PropertyService::id();

        $employee = Employee::create($validated);
        $employee->load('department');

        return response()->json([
            'message' => 'Employee created.',
            'data' => $employee,
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['department', 'user']);

        return response()->json(['data' => $employee]);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'nullable|string|max:50',
            'department_id' => 'sometimes|exists:departments,id',
            'position' => 'sometimes|string|max:255',
        ]);

        $employee->update($validated);
        $employee->load('department');

        return response()->json([
            'message' => 'Employee updated.',
            'data' => $employee,
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Employee deleted.'], 204);
    }
}
