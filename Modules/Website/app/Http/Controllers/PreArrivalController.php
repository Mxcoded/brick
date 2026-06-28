<?php

namespace Modules\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Models\GuestDocument;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\PreArrivalService;

class PreArrivalController extends Controller
{
    protected PreArrivalService $preArrival;

    public function __construct(PreArrivalService $preArrival)
    {
        $this->preArrival = $preArrival;
    }

    public function index()
    {
        return view('website::guest.pre-arrival.index');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'reservation_code' => 'required|string|max:20',
            'contact' => 'required|string|max:191',
        ]);

        $registration = $this->preArrival->lookupByCode(
            $request->reservation_code,
            $request->contact
        );

        if (!$registration) {
            return back()->withErrors([
                'reservation_code' => 'No upcoming reservation found with those details.',
            ])->withInput();
        }

        if (!$registration->pre_arrival_token) {
            $this->preArrival->generateToken($registration);
        }

        $this->setSessionToken($registration);

        return redirect()->route('guest.pre-arrival.details', $registration);
    }

    public function token(string $token)
    {
        $registration = $this->preArrival->lookupByToken($token);

        if (!$registration) {
            return redirect()->route('guest.pre-arrival')
                ->with('error', 'This pre-arrival link is invalid or expired.');
        }

        $this->setSessionToken($registration);

        return redirect()->route('guest.pre-arrival.details', $registration);
    }

    public function details(Registration $registration)
    {
        $this->authorizeAccess($registration);
        $steps = $this->preArrival->getSteps($registration);

        return view('website::guest.pre-arrival.details', compact('registration', 'steps'));
    }

    public function updateDetails(Request $request, Registration $registration)
    {
        $this->authorizeAccess($registration);

        $data = $request->validate([
            'title' => 'nullable|string|max:20',
            'full_name' => 'required|string|max:191',
            'nationality' => 'nullable|string|max:100',
            'contact_number' => 'required|string|max:30',
            'email' => 'nullable|email|max:191',
            'occupation' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:191',
            'home_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'emergency_name' => 'nullable|string|max:191',
            'emergency_contact' => 'nullable|string|max:30',
            'emergency_relationship' => 'nullable|string|max:100',
            'special_requests' => 'nullable|string|max:1000',
            'estimated_arrival_at' => 'nullable|date',
            'no_of_guests' => 'required|integer|min:1|max:20',
            'opt_in_marketing' => 'boolean',
        ]);

        $this->preArrival->updateGuestDetails($registration, $data);

        return redirect()->route('guest.pre-arrival.documents', $registration)
            ->with('success', 'Details saved. Now please upload your ID.');
    }

    public function documents(Registration $registration)
    {
        $this->authorizeAccess($registration);
        $steps = $this->preArrival->getSteps($registration);

        return view('website::guest.pre-arrival.documents', compact('registration', 'steps'));
    }

    public function uploadDocument(Request $request, Registration $registration)
    {
        $this->authorizeAccess($registration);

        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'type' => 'required|in:passport,driver_license,national_id,visa,other',
        ]);

        $this->preArrival->uploadDocument(
            $registration, $request->file('document'), $request->type
        );

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function deleteDocument(Registration $registration, GuestDocument $document)
    {
        $this->authorizeAccess($registration);
        abort_if($document->registration_id !== $registration->id, 403);

        $this->preArrival->deleteDocument($document);

        return back()->with('success', 'Document removed.');
    }

    public function signature(Registration $registration)
    {
        $this->authorizeAccess($registration);
        $steps = $this->preArrival->getSteps($registration);

        return view('website::guest.pre-arrival.signature', compact('registration', 'steps'));
    }

    public function submitSignature(Request $request, Registration $registration)
    {
        $this->authorizeAccess($registration);

        $request->validate([
            'signature' => 'required|string',
        ]);

        $this->preArrival->submitSignature($registration, $request->signature);

        return redirect()->route('guest.pre-arrival.confirmation', $registration);
    }

    public function confirmation(Registration $registration)
    {
        $this->authorizeAccess($registration);

        if ($registration->stay_status !== 'draft_by_guest') {
            $this->preArrival->complete($registration);
        }

        return view('website::guest.pre-arrival.confirmation', compact('registration'));
    }

    private function setSessionToken(Registration $registration): void
    {
        session(['pre_arrival_token_' . $registration->id => $registration->pre_arrival_token]);
    }

    private function authorizeAccess(Registration $registration): void
    {
        $allowedToken = session('pre_arrival_token_' . $registration->id);

        abort_unless(
            $allowedToken && $allowedToken === $registration->pre_arrival_token,
            403,
            'Unauthorized access to this pre-arrival session.'
        );
    }
}
