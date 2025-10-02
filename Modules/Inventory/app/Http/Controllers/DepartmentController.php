<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Department;
use Modules\Inventory\Models\Store;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /**
     * Display a list of all departments.
     */
    public function index(): View
    {
        $departments = Department::with('store')->get();
        return view('inventory::departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(): View
    {
        $stores = Store::all();
        return view('inventory::departments.create', compact('stores'));
    }

    /**
     * Store a new department.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'store_id' => 'required|exists:stores,id',
        ]);

        try {
            Department::create($validatedData);
            return redirect()->route('inventory.departments.index')
                ->with('success', 'Department added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding department: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Error adding department: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department): View
    {
        $stores = Store::all();
        return view('inventory::departments.edit', compact('department', 'stores'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'store_id' => 'required|exists:stores,id',
        ]);

        try {
            $department->update($validatedData);
            return redirect()->route('inventory.departments.index')
                ->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating department: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating department: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        try {
            $department->delete();
            return redirect()->route('inventory.departments.index')
                ->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting department: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting department: ' . $e->getMessage());
        }
    }

    /**
     * Get a list of departments for a specific store.
     */
    public function getDepartmentsByStore(Store $store)
    {
        $departments = $store->departments;
        return response()->json($departments);
    }
}
