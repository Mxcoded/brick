<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Supplier;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * Display a list of all suppliers.
     */
    public function index(): View
    {
        $suppliers = Supplier::all();
        return view('inventory::suppliers.index', compact('suppliers'));
    }

    /**
     * Store a new supplier.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            Supplier::create($validatedData);
            return redirect()->route('inventory.suppliers.index')
                ->with('success', 'Supplier added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding supplier: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Error adding supplier: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('inventory::suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            $supplier->update($validatedData);
            return redirect()->route('inventory.suppliers.index')
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating supplier: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('inventory.suppliers.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting supplier: ' . $e->getMessage());
        }
    }
}
