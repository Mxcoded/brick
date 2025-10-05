<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreRegistrationRequest;
use Modules\Frontdeskcrm\Models\Registration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestPreference;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\GuestType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class RegistrationController extends Controller
{
    public function index()
    {
        $registrations = Registration::with('guest', 'bookingSource', 'guestType')->latest()->paginate(10);
        return view('frontdeskcrm::registrations.index', compact('registrations'));
    }

    public function show(Registration $registration)
    {
        $registration->load('guest', 'guestType', 'bookingSource', 'groupMaster', 'groupMembers');
        return view('frontdeskcrm::registrations.show', compact('registration'));
    }

    /**
     * Handles the old 'registrations.create' route, redirects to the explicit agent form.
     */
    public function create()
    {
        return redirect()->route('frontdesk.registrations.agent-checkin');
    }

    /**
     * Renders the Agent-facing Full Check-in Form (frontdesk.registrations.agent-checkin).
     * Includes search bar and ALL mandatory booking fields.
     */
    public function showAgentCheckinForm()
    {
        $bookingSources = BookingSource::active()->get();
        $guestTypes = GuestType::active()->get();
        $oldData = old() ?? [];
        return view('frontdeskcrm::registrations.agent-checkin', compact('bookingSources', 'guestTypes', 'oldData'));
    }

    // --- GUEST DRAFT FLOW (Public-facing form for check-in draft) ---

    /**
     * Renders the Guest-facing Draft Form (using the 'create' view).
     * This method would be mapped to a public, non-authenticated route if needed.
     */
    public function showGuestDraftForm()
    {
        $bookingSources = BookingSource::active()->get();
        $guestTypes = GuestType::active()->get();
        $oldData = old() ?? [];
        return view('frontdeskcrm::registrations.create', compact('bookingSources', 'guestTypes', 'oldData'));
    }

    // --- DRAFT FINALIZATION FLOW ---

    /**
     * Renders the Agent-facing Form to finalize a guest draft.
     */
    public function showFinishDraftForm(Registration $registration)
    {
        if ($registration->stay_status !== 'draft_by_guest') {
            return redirect()->route('frontdesk.registrations.show', $registration)->with('error', 'This registration is already finalized.');
        }

        $bookingSources = BookingSource::active()->get();
        $guestTypes = GuestType::active()->get();
        $oldData = $registration->toArray();

        return view('frontdeskcrm::registrations.finalize-draft', compact('registration', 'bookingSources', 'guestTypes', 'oldData'));
    }

    /**
     * Handles the Agent's submission to convert a draft to 'checked_in' status.
     * Only updates booking/financial fields.
     */
    public function finishDraft(Request $request, Registration $registration)
    {
        if ($registration->stay_status !== 'draft_by_guest') {
            return redirect()->route('frontdesk.registrations.show', $registration)->with('error', 'This registration is already finalized.');
        }

        // We validate the booking fields that were missing in the initial draft
        $validated = $request->validate([
            'room_type' => 'required|string|max:100',
            'room_rate' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,pos,transfer',
            'booking_source_id' => 'required|exists:booking_sources,id',
            'guest_type_id' => 'required|exists:guest_types,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'no_of_guests' => 'required|integer|min:1',
            'bed_breakfast' => 'nullable|boolean',
            'is_group_lead' => 'nullable|boolean',
            'group_members' => 'nullable|array',
            'group_members.*.full_name' => 'required_if:is_group_lead,true|string|max:255',
            'group_members.*.contact_number' => 'required_if:is_group_lead,true|string|max:20',
        ]);

        $validated['front_desk_agent'] = Auth::user()->name;
        $validated['stay_status'] = 'checked_in';
        $validated['agreed_to_policies'] = true;

        // Calculate discount and total amount
        $roomRate = floatval($validated['room_rate']);
        $guestType = GuestType::find($validated['guest_type_id']);
        if ($guestType && $guestType->discount_rate > 0) {
            $roomRate *= (1 - ($guestType->discount_rate / 100));
        }

        $checkIn = \Carbon\Carbon::parse($validated['check_in']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out']);
        $noOfNights = $checkIn->diffInDays($checkOut);

        $validated['room_rate'] = $roomRate;
        $validated['no_of_nights'] = $noOfNights;
        $validated['total_amount'] = $roomRate * $noOfNights;

        $registration->update($validated);

        // Group handling (if applicable)
        if ($registration->is_group_lead && $request->has('group_members')) {
            // Logic for creating group members based on final booking details
        }

        return redirect()->route('frontdesk.registrations.show', $registration)
            ->with('success', 'Registration draft finalized and guest checked in!');
    }


    public function search(Request $request)
    {
        $query = $request->input('query');
        $guests = Guest::where('email', 'LIKE', "%{$query}%")
            ->orWhere('contact_number', 'LIKE', "%{$query}%")
            ->orWhere('full_name', 'LIKE', "%{$query}%")
            ->with('preference')
            ->limit(5)
            ->get();

        return response()->json($guests->map(function ($guest) {
            $prefs = $guest->preference;
            return [
                'id' => $guest->id,
                'full_name' => $guest->full_name,
                'email' => $guest->email,
                'contact_number' => $guest->contact_number,
                'title' => $guest->title,
                'nationality' => $guest->nationality,
                'birthday' => $guest->birthday?->format('Y-m-d'),
                'occupation' => $guest->occupation,
                'company_name' => $guest->company_name,
                'home_address' => $guest->home_address,
                'emergency_name' => $guest->emergency_name,
                'emergency_relationship' => $guest->emergency_relationship,
                'emergency_contact' => $guest->emergency_contact,
                'preferred_room_type' => $prefs?->preferences['preferred_room_type'] ?? null,
                'bb_included' => $prefs?->preferences['bb_included'] ?? false,
                'last_visit_at' => $guest->last_visit_at?->format('M d, Y'),
                'is_group_lead' => $guest->is_group_lead ?? false, // If stored on guest, or derive from registrations
            ];
        }));
    }

    public function store(StoreRegistrationRequest $request)
    {
        $isGuestDraft = $request->boolean('is_guest_draft', false);

        if ($isGuestDraft) {
            // GUEST DRAFT SUBMISSION
            $registration = $this->handleStore($request, 'draft_by_guest', null);
            return redirect()->route('frontdesk.registrations.index')
                ->with('success', 'Thank you! Your check-in draft has been submitted. Please see the front desk to finalize your booking.');
        }

        // AGENT FULL CHECK-IN SUBMISSION
        $registration = $this->handleStore($request, 'checked_in', Auth::user()->name);

        return redirect()->route('frontdesk.registrations.show', $registration->id)
            ->with('success', 'Registration completed and guest checked in!');
    }

    private function handleStore($request, $status, $agentName)
    {
        $data = $request->validated();
        $data['front_desk_agent'] = $agentName;
        $data['registration_date'] = now()->toDateString();
        $data['stay_status'] = $status;

        // Discount logic (Only apply if a GuestType is provided and status is checked_in)
        if ($status === 'checked_in' && isset($data['guest_type_id'])) {
            $guestType = GuestType::find($data['guest_type_id']);
            if ($guestType && $guestType->discount_rate > 0) {
                $data['room_rate'] = floatval($data['room_rate']) * (1 - ($guestType->discount_rate / 100));
            }
        }

        // --- Guest create/update logic (Modified for FirstOrCreate/Update) ---
        $guest = Guest::where('contact_number', $data['contact_number'])
            ->when(isset($data['email']) && !empty($data['email']), fn($query) => $query->orWhere('email', $data['email']))
            ->first();

        // If guest not found, create a new one, else update
        if (!$guest) {
            $guest = Guest::create([
                'title' => $data['title'] ?? null,
                'full_name' => $data['full_name'],
                'nationality' => $data['nationality'] ?? null,
                'birthday' => $data['birthday'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'home_address' => $data['home_address'] ?? null,
                'emergency_name' => $data['emergency_name'] ?? null,
                'emergency_relationship' => $data['emergency_relationship'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'opt_in_data_save' => $request->boolean('opt_in_data_save', true),
                'email' => $data['email'] ?? null,
                'contact_number' => $data['contact_number'],
                'visit_count' => 1,
            ]);
        } else {
            $guest->update([
                'title' => $data['title'] ?? $guest->title,
                'full_name' => $data['full_name'],
                'nationality' => $data['nationality'] ?? $guest->nationality,
                'birthday' => $data['birthday'] ?? $guest->birthday,
                'occupation' => $data['occupation'] ?? $guest->occupation,
                'company_name' => $data['company_name'] ?? $guest->company_name,
                'home_address' => $data['home_address'] ?? $guest->home_address,
                'emergency_name' => $data['emergency_name'] ?? $guest->emergency_name,
                'emergency_relationship' => $data['emergency_relationship'] ?? $guest->emergency_relationship,
                'emergency_contact' => $data['emergency_contact'] ?? $guest->emergency_contact,
                'opt_in_data_save' => $request->boolean('opt_in_data_save', $guest->opt_in_data_save ?? true),
            ]);
            $guest->increment('visit_count');
            $guest->update(['last_visit_at' => now()]);
        }

        // Prefs (if opt-in and not a draft)
        if ($guest->opt_in_data_save && $status !== 'draft_by_guest') {
            $prefs = $guest->preference ?? new GuestPreference(['guest_id' => $guest->id]);
            $prefs->preferences = array_merge($prefs->preferences ?? [], [
                'preferred_room_type' => $data['room_type'],
                'bb_included' => $request->boolean('bed_breakfast', false),
            ]);
            $prefs->save();
        }
        // --- End Guest Logic ---

        $data['guest_id'] = $guest->id;
        $data['bed_breakfast'] = $request->boolean('bed_breakfast', false);
        $data['is_group_lead'] = $request->boolean('is_group_lead', false);
        $data['no_of_guests'] = $data['no_of_guests'] ?? 1;
        $data['agreed_to_policies'] = $request->boolean('agreed_to_policies', false);

        // Calculate nights and total amount
        $checkIn = \Carbon\Carbon::parse($data['check_in']);
        $checkOut = \Carbon\Carbon::parse($data['check_out']);
        $noOfNights = $checkIn->diffInDays($checkOut);
        $data['no_of_nights'] = $noOfNights;

        // Only calculate total if we have rate and nights (i.e., not a raw guest draft)
        if ($status !== 'draft_by_guest') {
            $data['total_amount'] = $data['room_rate'] * $noOfNights;
        } else {
            $data['room_rate'] = 0; // Clear financial data for safety in draft
            $data['total_amount'] = 0;
            $data['booking_source_id'] = null; // Clear booking specific IDs
            $data['guest_type_id'] = null;
        }

        // Signature handling
        if (isset($data['guest_signature']) && !empty($data['guest_signature'])) {
            $signature = str_replace(['data:image/png;base64,', ' '], ['', '+'], $data['guest_signature']);
            $fileData = base64_decode($signature);
            $filename = 'signatures/' . uniqid() . '.png';
            Storage::disk('public')->put($filename, $fileData);
            $data['guest_signature'] = $filename;
        }
        Log::info('Signature preview: ' . substr($data['guest_signature'] ?? '', 0, 50));  // Logs first 50 chars
        $registration = Registration::create($data);

        // Group handling is only allowed during final check-in by staff
        if ($data['is_group_lead'] && $request->has('group_members') && $status !== 'draft_by_guest') {
            // ... (Group Member creation logic remains the same)
            foreach ($request->input('group_members', []) as $member) {
                if (empty($member['full_name']) || empty($member['contact_number'])) continue;

                $subGuest = Guest::firstOrCreate(['contact_number' => $member['contact_number']], [
                    'full_name' => $member['full_name'],
                    'opt_in_data_save' => false,
                    'visit_count' => 1,
                ]);

                $subData = [
                    'guest_id' => $subGuest->id,
                    'full_name' => $member['full_name'],
                    'contact_number' => $member['contact_number'],
                    'room_assignment' => $member['room_assignment'] ?? null,
                    'group_master_id' => $registration->id,
                    'is_group_lead' => false,
                    'guest_type_id' => $data['guest_type_id'],
                    'booking_source_id' => $data['booking_source_id'],
                    'check_in' => $data['check_in'],
                    'check_out' => $data['check_out'],
                    'room_type' => $data['room_type'],
                    'room_rate' => $data['room_rate'],
                    'bed_breakfast' => $data['bed_breakfast'],
                    'no_of_guests' => 1,
                    'payment_method' => $data['payment_method'],
                    'agreed_to_policies' => true,
                    'front_desk_agent' => $data['front_desk_agent'],
                    'registration_date' => $data['registration_date'],
                    'stay_status' => 'checked_in',
                    'no_of_nights' => $noOfNights,
                    'total_amount' => $data['room_rate'] * $noOfNights,
                ];
                Registration::create($subData);
            }
        }

        return $registration;
    }



    // Print method (for PDF download)
    public function print(Registration $registration)
    {
        $registration->load('guest', 'guestType', 'bookingSource', 'groupMembers');
        // Add staff signature/name to PDF for print
        $staffName = Auth::user()->name; // <-- This correctly gets the currently logged in staff's name

        $pdf = Pdf::loadView('frontdeskcrm::registrations.print', compact('registration', 'staffName'));
        return $pdf->download('registration-' . $registration->id . '.pdf');
    }

   
    public function preview(Request $request, ?Registration $registration)
    {
        // Preview logic remains for staff use
        if (!$registration) {
            return response()->json(['error' => 'No registration found for preview.'], 400);
        }
        $registration->load('guest', 'guestType', 'bookingSource', 'groupMembers');
        $staffName = Auth::user()->name;
        $pdf = Pdf::loadView('frontdeskcrm::registrations.print', compact('registration', 'staffName'));
        return $pdf->stream('registration-preview-' . ($registration->id ?? 'draft') . '.pdf');
    }
}
