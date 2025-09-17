<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Store;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{
    /**
     * Display a list of all stores.
     */
    public function index(): View
    {
        $stores = Store::all();
        return view('inventory::stores.index', compact('stores'));
    }

    /**
     * Store a new store.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:stores,name',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            Store::create($validatedData);
            return redirect()->route('inventory.stores.index')
                ->with('success', 'Store added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding store: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Error adding store: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified store.
     */
    public function edit(Store $store): View
    {
        return view('inventory::stores.edit', compact('store'));
    }

    /**
     * Update the specified store in storage.
     */
    public function update(Request $request, Store $store)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:stores,name,' . $store->id,
            'address' => 'nullable|string|max:255',
        ]);

        try {
            $store->update($validatedData);
            return redirect()->route('inventory.stores.index')
                ->with('success', 'Store updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating store: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating store: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified store from storage.
     */
    public function destroy(Store $store)
    {
        try {
            $store->delete();
            return redirect()->route('inventory.stores.index')
                ->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting store: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting store: ' . $e->getMessage());
        }
    }
}
