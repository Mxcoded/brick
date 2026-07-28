<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\FolioService;

class FolioController extends Controller
{
    protected FolioService $folioService;

    public function __construct(FolioService $folioService)
    {
        $this->folioService = $folioService;
    }

    public function index(Registration $registration)
    {
        $folios = $registration->folios()->with('items')->orderBy('created_at')->get();

        return view('frontdeskcrm::folios.index', compact('registration', 'folios'));
    }

    public function show(Folio $folio)
    {
        $folio->load(['items', 'registration', 'registration.guest']);

        return view('frontdeskcrm::folios.show', compact('folio'));
    }

    public function postCharge(Request $request, Folio $folio)
    {
        $validated = $request->validate([
            'charge_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'tax_code' => 'nullable|string|max:20',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'post_date' => 'nullable|date',
        ]);

        $this->folioService->postCharge($folio, $validated, auth()->id());

        return redirect()->route('frontdesk.folios.show', $folio)
            ->with('success', 'Charge posted to folio.');
    }

    public function split(Request $request, Folio $folio)
    {
        $validated = $request->validate([
            'folio_name' => 'required|string|max:100',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:folio_items,id',
        ]);

        $this->folioService->splitFolio($folio, $validated['folio_name'], $validated['item_ids'], auth()->id());

        return redirect()->route('frontdesk.folios.index', $folio->registration_id)
            ->with('success', 'Folio split successfully.');
    }

    public function close(Folio $folio)
    {
        $this->folioService->closeFolio($folio);

        return redirect()->route('frontdesk.folios.index', $folio->registration_id)
            ->with('success', 'Folio closed.');
    }
}
