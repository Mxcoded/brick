<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Models\ChargeType;

class ChargeTypeController extends Controller
{
    public function index(Request $request)
    {
        $chargeTypes = ChargeType::ordered()->paginate(20);

        return view('frontdeskcrm::charge-types.index', compact('chargeTypes'));
    }

    public function create()
    {
        return view('frontdeskcrm::charge-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:30|unique:charge_types,code',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        ChargeType::create($validated);

        return redirect()->route('frontdesk.charge-types.index')
            ->with('success', 'Charge type created.');
    }

    public function edit(ChargeType $chargeType)
    {
        return view('frontdeskcrm::charge-types.edit', compact('chargeType'));
    }

    public function update(Request $request, ChargeType $chargeType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:30|unique:charge_types,code,'.$chargeType->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        $chargeType->update($validated);

        return redirect()->route('frontdesk.charge-types.index')
            ->with('success', 'Charge type updated.');
    }

    public function destroy(ChargeType $chargeType)
    {
        if ($chargeType->folioCharges()->exists()) {
            return back()->with('error', 'Cannot delete charge type with existing charges.');
        }

        $chargeType->delete();

        return redirect()->route('frontdesk.charge-types.index')
            ->with('success', 'Charge type deleted.');
    }
}
