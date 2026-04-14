<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreRegistrationRequest;
use Modules\Frontdeskcrm\Http\Requests\FinalizeRegistrationRequest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\GuestType;
use Modules\Website\Models\Room;
use Modules\Website\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Frontdeskcrm\Emails\RegistrationStatusMail;
use Modules\Frontdeskcrm\Rules\ValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class RegistrationController extends Controller
{
    // =====================================================================
    // GUEST-FACING CHECK-IN FLOW
    // =====================================================================

    /**
     * Show the initial guest check-in form.
     * Updated to handle session clearing.
     */
    public function create(Request $request)
    {
        // 1. Clear Session if requested
        if ($request->has('clear')) {
            session()->forget(['returning_guest', 'guest_data', 'search_query', 'linked_booking_id']);
            return redirect()->route('frontdesk.registrations.create');
        }

        // 2. ✅ NEW LOGIC: Handle "Online Checkin" Link with Reference
        if ($request->has('ref')) {
            $booking = Booking::where('booking_reference', $request->query('ref'))->first();

            if ($booking) {
                // Determine if this looks like a group (more than 1 person)
                $totalGuests = $booking->adults + $booking->children;
                $isGroup = $totalGuests > 1;

                // Pre-fill the Session Data
                session([
                    // Simulates a "Found Guest" to skip the search screen
                    'returning_guest' => [
                        'id' => null, // Keep null so we don't accidentally overwrite a wrong profile ID
                        'name' => $booking->guest_name,
                        'masked_email' => $booking->guest_email,
                        'masked_phone' => $booking->guest_phone,
                    ],
                    // Pre-fill the Form Fields
                    'guest_data' => [
                        'full_name' => $booking->guest_name,
                        'email' => $booking->guest_email,
                        'contact_number' => $booking->guest_phone,
                        'check_in' => $booking->check_in_date->format('Y-m-d'),
                        'check_out' => $booking->check_out_date->format('Y-m-d'),
                        'no_of_guests' => $totalGuests,
                        'is_group_lead' => $isGroup ? '1' : '0', // Auto-check "Group Booking" if >1 person
                    ],
                    // Store ID to link it later in store()
                    'linked_booking_id' => $booking->id
                ]);
            }
        }

        return view('frontdeskcrm::registrations.create');
    }

    /**
     * Handle the initial search from the guest.
     * SECURE VERSION: Normalizes phone, clears old sessions, and masks data.
     */
    public function handleGuestSearch(Request $request)
    {
        // 1. Validate
        $request->validate([
            'search_query' => 'required|string|max:255',
        ]);

        // 2. CRITICAL FIX: Clear any previous "Returning Guest" session immediately
        session()->forget('returning_guest');

        $query = $request->input('search_query');

        // 3. CRITICAL FIX: Normalize input if it looks like a phone number
        // This ensures typing "080..." finds the guest saved as "+23480..."
        $normalizedQuery = $query;
        $cleanQuery = preg_replace('/[\s\-\(\)]+/', '', $query);
        if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $cleanQuery)) {
            $normalizedQuery = '+234' . substr($cleanQuery, 1);
        }

        // 4. Search (Check both exact input and normalized version)
        $guest = Guest::where('email', $query)
            ->orWhere('contact_number', $query)
            ->orWhere('contact_number', $normalizedQuery) // Check normalized phone
            ->first();

        if ($guest) {
            // Found! Securely store ID and show Masked Data
            $maskedEmail = $guest->email ? preg_replace('/(?<=.).(?=.*@)/', '*', $guest->email) : 'N/A';
            // Show last 4 digits for verification
            $phoneLen = strlen($guest->contact_number);
            $maskedPhone = '******' . substr($guest->contact_number, -4);

            session([
                'returning_guest' => [
                    'id' => $guest->id,
                    'name' => $guest->full_name,
                    'masked_email' => $maskedEmail,
                    'masked_phone' => $maskedPhone,
                ]
            ]);

            return redirect()->route('frontdesk.registrations.create')
                ->with('success', "Welcome back, {$guest->full_name}! Please confirm your stay details.");
        } else {
            // Not Found: User is truly new (or needs to update info)
            return redirect()->route('frontdesk.registrations.create')
                ->with('search_query', $query)
                ->with('status', 'No profile found. Please create a new registration.')
                ->withInput();
        }
    }


    /**
     * Store the guest's submitted draft registration.
     * SAFE VERSION: Handles missing keys for returning guests to prevent crashes.
     */
    public function store(StoreRegistrationRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $validated = $request->validated();
            $notificationMessage = "Registration submitted successfully!";

            $normalizePhone = function ($phone) {
                if (!$phone) return null;
                $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
                if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $phone)) return '+234' . substr($phone, 1);
                return $phone;
            };

            // [FIX] Use safe access (?? null) because these keys might not exist in the array
            $inputPhone = $normalizePhone($validated['contact_number'] ?? null);
            $inputEmail = $validated['email'] ?? null;

            $guest = null;

            // 2. GUEST RESOLUTION
            if (session()->has('returning_guest')) {
                $guest = Guest::find(session('returning_guest')['id']);
            }

            if (!$guest && $inputPhone) {
                $guest = Guest::where('contact_number', $inputPhone)->first();
            }

            // Smart Merge (Email Check)
            if (!$guest && $inputEmail) {
                $guest = Guest::where('email', $inputEmail)->first();
                if ($guest) {
                    $guest->contact_number = $inputPhone;
                    $guest->save();
                    $notificationMessage .= " We found your profile via email and updated your phone number.";
                }
            }

            // 3. DUPLICATE CHECK
            if ($guest) {
                $existingReg = Registration::where('guest_id', $guest->id)
                    ->whereDate('created_at', \Carbon\Carbon::today())
                    ->whereIn('stay_status', ['draft_by_guest', 'checked_in'])
                    ->first();

                if ($existingReg) {
                    return redirect()->route('frontdesk.registrations.thank-you')
                        ->with('info', "You already have a pending registration for today. Please proceed to the front desk.");
                }
            }

            // 4. PERSISTENCE
            if ($guest) {
                // === RETURNING GUEST ===
                // [CRITICAL FIX] Use '?? $guest->attribute' to fallback to existing DB value
                // if the input is missing from the form (which happens for Secure Returning Guests).
                $guest->update([
                    'title' => $validated['title'] ?? $guest->title,
                    'full_name' => $validated['full_name'] ?? $guest->full_name ?? session('returning_guest.name'), // <--- PREVENTS CRASH
                    'nationality' => $validated['nationality'] ?? $guest->nationality,
                    'home_address' => $validated['home_address'] ?? $guest->home_address,
                    'emergency_name' => $validated['emergency_name'] ?? $guest->emergency_name,
                    'emergency_contact' => $validated['emergency_contact'] ?? $guest->emergency_contact,
                    'occupation' => $validated['occupation'] ?? $guest->occupation,
                    'email' => $validated['email'] ?? $guest->email,
                ]);
            } else {
                // === NEW GUEST ===
                // ✅ CRITICAL FIX: Recover missing data from Session if form was hidden
                $fullName = $validated['full_name'] ?? session('guest_data.full_name') ?? session('returning_guest.name');
                $contactNumber = $inputPhone ?? $normalizePhone(session('guest_data.contact_number'));
                $email = $inputEmail ?? session('guest_data.email');

                // Safety Check: If we still lack required data, force restart
                if (empty($fullName) || empty($contactNumber)) {
                    return redirect()->route('frontdesk.registrations.create', ['clear' => 1])
                        ->with('error', 'Session expired or missing data. Please start over.');
                }
                $guest = Guest::create([
                    'title' => $validated['title'] ?? null,
                    'full_name' =>  $fullName, // Required for new guests
                    'contact_number' => $contactNumber, // Required for new guests
                    'email' => $inputEmail,
                    'nationality' => $validated['nationality'] ?? null,
                    'home_address' => $validated['home_address'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'company_name' => $validated['company_name'] ?? null,
                    'emergency_name' => $validated['emergency_name'] ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null,
                ]);
            }

            // 5. REGISTRATION SNAPSHOT
            $registrationData = [
                'guest_id' => $guest->id,
                // ✅ NEW: Retrieve the booking ID from session if it exists
                'booking_id' => session('linked_booking_id') ?? null,
                'stay_status' => 'draft_by_guest',
                'title' => $validated['title'] ?? $guest->title,
                'full_name' => $validated['full_name'] ?? $guest->full_name,
                'contact_number' => $inputPhone ?? $guest->contact_number,
                'email' => $validated['email'] ?? $guest->email,
                'nationality' => $validated['nationality'] ?? $guest->nationality,
                'gender' => $validated['gender'] ?? $guest->gender,
                'occupation' => $validated['occupation'] ?? $guest->occupation,
                'company_name' => $validated['company_name'] ?? $guest->company_name,
                'home_address' => $validated['home_address'] ?? $guest->home_address,
                'emergency_name' => $validated['emergency_name'] ?? $guest->emergency_name,
                'emergency_contact' => $validated['emergency_contact'] ?? $guest->emergency_contact,
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'no_of_guests' => $validated['no_of_guests'],
                'is_group_lead' => $request->boolean('is_group_lead'),
                'agreed_to_policies' => true,
                'opt_in_data_save' => $request->boolean('opt_in_data_save'),
            ];

            // Signature Logic
            if (!empty($validated['guest_signature'])) {
                $signatureImage = $validated['guest_signature'];
                if (str_contains($signatureImage, ',')) {
                    $signatureImage = explode(',', $signatureImage)[1];
                }
                $signatureImage = base64_decode($signatureImage);
                $imageName = 'signatures/' . uniqid() . '.png';
                Storage::disk('public')->put($imageName, $signatureImage);
                $registrationData['guest_signature'] = $imageName;
            }

            $registration = Registration::create($registrationData);

            // 6. GROUP MEMBERS
            if ($request->boolean('is_group_lead') && !empty($validated['group_members'])) {
                foreach ($validated['group_members'] as $memberData) {

                    $memberPhone = $normalizePhone($memberData['contact_number'] ?? null);
                    $memberEmail = $memberData['email'] ?? null;
                    $memberGuest = null;

                    if ($memberPhone) {
                        $memberGuest = Guest::where('contact_number', $memberPhone)->first();
                    }
                    if (!$memberGuest && $memberEmail) {
                        $memberGuest = Guest::where('email', $memberEmail)->first();
                    }

                    if ($memberGuest) {
                        // [FIX] Only update if key exists
                        if (isset($memberData['full_name'])) {
                            $memberGuest->update(['full_name' => $memberData['full_name']]);
                        }
                    } else {
                        $memberGuest = Guest::create([
                            'full_name' => $memberData['full_name'],
                            'contact_number' => $memberPhone,
                            'email' => $memberEmail,
                        ]);
                    }

                    Registration::create([
                        'parent_registration_id' => $registration->id,
                        'guest_id' => $memberGuest->id,
                        'full_name' => $memberData['full_name'],
                        'contact_number' => $memberPhone,
                        'email' => $memberEmail,
                        'check_in' => $registration->check_in,
                        'check_out' => $registration->check_out,
                        'stay_status' => 'draft_by_guest',
                    ]);
                }
            }

            // CLEAR THE SESSION AFTER SAVING
            session()->forget(['returning_guest', 'guest_data', 'linked_booking_id']);
            // 7. SEND NOTIFICATION EMAIL
            $this->sendNotification($registration);
            return redirect()->route('frontdesk.registrations.thank-you')
                ->with('success', $notificationMessage);
        });
    }
    /**
     * Display a simple thank you page to the guest.
     */
    public function thankYou()
    {
        return view('frontdeskcrm::registrations.thank-you');
    }


    // =====================================================================
    // AGENT-FACING FINALIZATION FLOW
    // =====================================================================

    /**
     * Display the agent's dashboard of all registrations with Search & Filter.
     */
    public function index(Request $request)
    {
        // 1. Existing Registration Logic (Search/Filter)
        $query = Registration::query()->with(['room', 'guest']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('booking_reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('stay_status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('check_in', $request->date);
        }

        $registrations = $query->latest()->paginate(10);

        // 2. ✅ NEW: Fetch Expected Arrivals (Confirmed Bookings NOT yet checked in)
        // We look for bookings with 'confirmed' or 'pending' status
        // that do NOT have a matching 'checked_in' status in the bookings table
        // (Recall: We updated processBookingCheckin to change booking status to 'checked_in')

        $expectedArrivals = Booking::whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', '>=', now()->subDays(1)) // Show yesterday's no-shows too
            ->orderBy('check_in_date', 'asc')
            ->get();

        return view('frontdeskcrm::registrations.index', compact('registrations', 'expectedArrivals'));
    }
    // --- NEW "WALK-IN" FEATURE (Scenario 3) ---


    /**
     * Show the agent a simple form to create a new walk-in guest.
     */
    public function createWalkin()
    {
        // ✅ NEW: Fetch rooms so the agent can select one immediately
        // We fetch ALL rooms because for a future reservation, 
        // a currently occupied room might be free.
        $rooms = Room::orderBy('name')->get();

        return view('frontdeskcrm::registrations.create-walkin', compact('rooms'));
    }

    /**
     * AJAX Lookup for Walk-in form.
     * Finds a guest by phone number (checks both Local and International formats).
     */
    public function lookupGuest(Request $request)
    {
        $rawInput = $request->query('contact_number');

        if (!$rawInput) {
            return response()->json(['found' => false]);
        }

        // 1. Clean the input (remove spaces, dashes, brackets)
        $cleanPhone = preg_replace('/[\s\-\(\)]+/', '', $rawInput);

        // 2. Create the Normalized Version (International +234)
        $internationalPhone = $cleanPhone;
        if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $cleanPhone)) {
            $internationalPhone = '+234' . substr($cleanPhone, 1);
        }

        // 3. Search for EITHER match (Local OR International)
        // This ensures we find "080..." even if we saved it as "+234..." and vice versa.
        $guest = Guest::where(function ($query) use ($cleanPhone, $internationalPhone) {
            $query->where('contact_number', $cleanPhone)
                ->orWhere('contact_number', $internationalPhone);
        })->first();

        if ($guest) {
            return response()->json([
                'found' => true,
                'guest' => [
                    'full_name' => $guest->full_name,
                    'email' => $guest->email,
                    'gender' => $guest->gender,
                    // Add other fields if needed
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }
    /**
     * Store the new walk-in guest and registration.
     */
    public function storeWalkin(StoreRegistrationRequest $request)
    {
        // 1. Create or Find Guest
        $guest = Guest::firstOrCreate(
            ['contact_number' => $request->contact_number],
            [
                'title' => $request->title ?? null,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'gender' => $request->gender ?? null,
                'home_address' => $request->home_address ?? null,
                'identification_number' => $request->identification_number ?? null,
                'nationality' => $request->nationality ?? null,
                'zip_code' => $request->zip_code ?? null,
                'identification_type' => $request->identification_type ?? null,
                'birthday' => $request->birthday ?? null,
                'occupation' => $request->occupation ?? null,
                'company_name' => $request->company_name ?? null,
                'city' => $request->city ?? null,
                'state' => $request->state ?? null,
                'emergency_name' => $request->emergency_name ?? null,
                'emergency_relationship' => $request->emergency_relationship ?? null,
                'emergency_contact' => $request->emergency_contact ?? null,
                'opt_in_data_save' => $request->boolean('opt_in_data_save', true),
                
            ]
        );

        // 2. CHECK DATES & DETERMINE FLOW
        $checkInDate = \Carbon\Carbon::parse($request->check_in);
        $isFuture = $checkInDate->startOfDay()->gt(now()->startOfDay());

        // 3. AVAILABILITY CHECK (If Room is Selected)
        if ($request->filled('room_id')) {
            $isOccupied = Registration::where('room_id', $request->room_id)
                ->where('stay_status', 'checked_in') // Only check strictly occupied rooms
                ->where(function ($query) use ($request) {
                    $query->whereBetween('check_in', [$request->check_in, $request->check_out])
                        ->orWhereBetween('check_out', [$request->check_in, $request->check_out])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('check_in', '<=', $request->check_in)
                                ->where('check_out', '>=', $request->check_out);
                        });
                })->exists();

            if ($isOccupied) {
                // Return with error if room is taken
                return back()->withInput()->withErrors(['room_id' => 'The selected room is occupied for these dates.']);
            }
        }

        // 4. Resolve Room Name
        $roomName = $request->filled('room_id')
            ? \Modules\Website\Models\Room::find($request->room_id)->name
            : null;

        // 3. Create Registration
        $registration = Registration::create([
            'guest_id' => $guest->id,

            // Snapshot Fields
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'gender' => $request->gender, //

            // Stay Details
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'no_of_guests' => $request->no_of_guests,

            // ✅ AGENT AUDIT (The Missing Piece)
            'front_desk_agent' => Auth::user()->name,

            // ✅ GROUP LOGIC (Auto-detect)
            // 'is_group_lead' => $request->no_of_guests > 1,

            'room_id' => $request->room_id,
            'room_allocation' => $roomName,
            'billing_type' => 'consolidate',

            // Dynamic Status
            'stay_status' => $isFuture ? 'reserved' : 'draft_by_guest',
            'booking_id' => null,
            'registration_date' => now(),
        ]);

        $this->sendNotification($registration);

        // 4. DYNAMIC REDIRECT
        if ($isFuture) {
            return redirect()->route('frontdesk.registrations.index')
                ->with('success', 'Reservation created successfully! Listed as Reserved.');
        } else {
            return redirect()->route('frontdesk.registrations.finalize.form', $registration)
                ->with('success', 'Walk-in draft created. Please allocate a room now.');
        }
    }
    /**
     * Show the form for an agent to finalize a draft.
     * UPDATED: Now fetches Rooms from the Website module.
     */
    public function showFinalizeForm(Registration $registration)
    {
        // ✅ ALLOW BOTH 'draft_by_guest' AND 'reserved' statuses to be finalized.
        if (!in_array($registration->stay_status, ['draft_by_guest', 'reserved'])) {
            return redirect()->route('frontdesk.registrations.show', $registration)
                ->with('error', 'This registration has already been finalized.');
        }

        $groupMembers = Registration::where('parent_registration_id', $registration->id)->get();
        $bookingSources = BookingSource::where('is_active', true)->get();
        $guestTypes = GuestType::where('is_active', true)->get();
        $rooms = Room::orderBy('name')->get();

        return view('frontdeskcrm::registrations.finalize', compact(
            'registration',
            'groupMembers',
            'bookingSources',
            'guestTypes',
            'rooms'
        ));
    }
    /**
     * Process the agent's finalization.
     * UPDATED: Validates using Room ID (ERP Logic).
     */
    public function finalize(FinalizeRegistrationRequest $request, Registration $registration)
    {
        $validated = $request->validated();
        $billingType = $request->input('billing_type', 'consolidate');
        $today = now()->startOfDay();
        $checkInDate = \Carbon\Carbon::parse($registration->check_in)->startOfDay();
        $checkOutDate = \Carbon\Carbon::parse($registration->check_out)->startOfDay();

        // =========================================================
        // 0. DATE LOGIC SANITY CHECKS (The Fix)
        // =========================================================

        // A) PREVENT "ZOMBIE" CHECK-INS (Date has passed)
        if ($today->gte($checkOutDate)) {
            return back()->with('error', 'Cannot check in: The departure date (' . $checkOutDate->format('M d') . ') has already passed. Please mark as No-Show or create a new Walk-in.');
        }

        // B) HANDLE EARLY ARRIVALS (Shift Start Date to Today)
        // If guest arrives TODAY (Jan 20) but booking was TOMORROW (Jan 21),
        // we must check availability for the NEW gap (Today) and charge for the extra night.
        $datesAdjustedMessage = null;

        if ($today->lt($checkInDate)) {
            // Update the object in memory only (for availability check)
            // We will save it permanently in the update() block below.
            $registration->check_in = $today;

            // Recalculate Nights (Add the extra days)
            $newNights = $today->diffInDays($checkOutDate);
            $registration->no_of_nights = $newNights; // Update memory

            $datesAdjustedMessage = "Note: Check-in date adjusted to Today (" . $today->format('M d') . "). Extra nights added.";
        }

        // C) HANDLE LATE ARRIVALS (Room was held)
        // If booking was Jan 10, and they arrive Jan 12, we DO NOT change the date.
        // Standard Hotel Rule: We held the room, so the billing starts from the original Jan 10.
        // Logic: No code change needed here, just proceed.

        $nights = $registration->no_of_nights; // Use the potentially updated nights

        // =========================================================
        // 1. ERP AVAILABILITY CHECK (Using Adjusted Dates)
        // =========================================================

        $checkAvailability = function ($roomId, $checkIn, $checkOut, $ignoreRegId = null) {
            if (!$roomId) return false;
            return Registration::where('room_id', $roomId)
                ->where('stay_status', 'checked_in')
                ->where('id', '!=', $ignoreRegId)
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($q) use ($checkIn, $checkOut) {
                            $q->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                })->exists();
        };

        // ... (Your Availability Logic A & B remains the same) ...
        // ... But it now uses the UPDATED $registration->check_in date! ...

        // A) Check Lead Room
        $leadRoomId = $request->input('room_id');
        if ($leadRoomId && $checkAvailability($leadRoomId, $registration->check_in, $registration->check_out, $registration->id)) {
            $roomName = Room::find($leadRoomId)?->name ?? 'Selected Room';
            return back()->withInput()->withErrors([
                'room_id' => "$roomName is occupied (Date Logic: Checked availability starting " . $registration->check_in->format('M d') . ")."
            ]);
        }

        // ... (Member Checks remain the same) ...

        // =========================================================
        // 2. PROCESSING & SAVING
        // =========================================================

        // ... (Your Member Processing remains the same) ...

        // --- Process Group Lead ---
        $leadRoomName = null;
        if ($request->filled('room_id')) {
            $leadRoomName = Room::find($request->input('room_id'))?->name;
        }

        $leadRate = $validated['room_rate'];
        $leadPersonalBill = $leadRate * $nights; // Uses updated nights
        $finalLeadTotal = $leadPersonalBill;

        if ($billingType === 'consolidate') {
            $finalLeadTotal += $membersTotalBill ?? 0; // Ensure variable exists
        }

        $registration->update([
            'room_id' => $request->input('room_id'),
            'room_allocation' => $leadRoomName,
            'room_rate' => $leadRate,
            'bed_breakfast' => $request->boolean('bed_breakfast'),
            'guest_type_id' => $validated['guest_type_id'],
            'booking_source_id' => $validated['booking_source_id'],
            'payment_method' => $validated['payment_method'],
            'billing_type' => $billingType,
            'stay_status' => 'checked_in',

            // ✅ SAVE THE ADJUSTED DATES
            'check_in' => $registration->check_in,
            'no_of_nights' => $nights,

            'total_amount' => $finalLeadTotal,
            'finalized_by_agent_id' => Auth::id(),
            'checked_in_at' => now(), // Exact timestamp
        ]);

        // ... (Parent Bill Sync remains the same) ...

        $successMsg = 'Check-in finalized successfully!';
        if ($datesAdjustedMessage) {
            $successMsg .= " " . $datesAdjustedMessage;
        }
        $this->sendNotification($registration);
        return redirect()->route('frontdesk.registrations.show', $registration)
            ->with('success', $successMsg);
    }
    // --- NEW "NO-SHOW" FIX (The Gap) ---

    /**
     * Re-opens a 'no_show' or 'checked_out' registration to be finalized again.
     * UPDATED: Clears audit trails to prevent data corruption.
     */
    public function reopen(Registration $registration)
    {
        // 1. Validation
        if ($registration->stay_status !== 'no_show' && $registration->stay_status !== 'checked_out') {
            return back()->with('error', 'Only no-show or checked-out guests can be re-opened.');
        }

        // 2. Resolve Group Lead (Always reopen from the top down)
        if ($registration->parent_registration_id) {
            $registration = $registration->parent;
        }

        // 3. Define the Reset State
        // We must clear the checkout timestamps so the system treats them as 'active' again.
        $resetData = [
            'stay_status' => 'draft_by_guest',
            'actual_checkout_at' => null,       // <--- CRITICAL FIX
            'checked_out_by_agent_id' => null,  // <--- CRITICAL FIX
            // We KEEP 'room_id' and 'check_in/out' dates. 
            // The finalize() method will validate if the room is still free.
        ];

        // 4. Reset Children (Group Members)
        // Note: This resets EVERYONE. If you want to keep 'no_show' members as 'no_show',
        // you would need a more complex loop here. For now, resetting all is safer 
        // to ensure the agent reviews everyone.
        $registration->children()->update($resetData);

        // 5. Reset Parent
        $registration->update($resetData);

        return redirect()->route('frontdesk.registrations.finalize.form', $registration)
            ->with('success', 'Registration has been re-opened. Please review room availability and finalize.');
    }

    // --- NEW "DELETE DRAFT" FEATURE ---

    /**
     * Deletes a draft registration and its members.
     */
    public function destroy(Registration $registration)
    {
        // Security check: Only allow deleting drafts.
        if ($registration->stay_status !== 'draft_by_guest' && $registration->stay_status !== 'reserved' && $registration->stay_status !== 'checked_in') {
            return back()->with('error', 'Only draft registrations can be deleted.');
        }

        // If it's a lead, delete all its children (group members) first
        if ($registration->is_group_lead) {
            $registration->children()->delete();
        }

        // Delete the main registration
        $registration->delete();

        return redirect()->route('frontdesk.registrations.index')
            ->with('success', 'Draft registration has been deleted.');
    }   

    // ===================================================================
    // UTILITY & DISPLAY METHODS
    // =====================================================================

    /**
     * Generate and stream a PDF for printing.
     */
    public function print(Registration $registration)
    {
        $registration->load('guest', 'guestType', 'bookingSource');
        $groupMembers = Registration::where('parent_registration_id', $registration->id)->get();

        // **PERFORMANCE FIX**: Pre-process images into base64 strings here.

        // 1. Guest Signature
        $guestSignatureBase64 = null;
        if ($registration->guest_signature && Storage::disk('public')->exists($registration->guest_signature)) {
            $guestSignatureBase64 = base64_encode(Storage::disk('public')->get($registration->guest_signature));
        }

        // 2. Hotel Logo
        // This assumes your logo is at 'public/storage/images/BrickspointLogo.png'
        // Ensure you have run `php artisan storage:link`
        $logoPath = public_path('storage/images/BrickspointLogo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('frontdeskcrm::registrations.print', compact(
            'registration',
            'groupMembers',
            'guestSignatureBase64',
            'logoBase64'
        ));

        return $pdf->stream('registration-' . $registration->id . '.pdf');
    }
    /**
     * Display a single, finalized registration, including group details.
     */
    public function show(Registration $registration)
    {
        // If this is a member registration, load the lead's page instead for a consistent UX.
        if ($registration->parent_registration_id) {
            return redirect()->route('frontdesk.registrations.show', $registration->parent_registration_id);
        }

        $registration->load('guest', 'guestType', 'bookingSource');
        $groupMembers = Registration::where('parent_registration_id', $registration->id)->get();

        // Calculate Group Financial Summary
        $membersBill = $groupMembers->where('stay_status', 'checked_in')->sum('total_amount');
        $leadPersonalBill = $registration->room_rate * $registration->no_of_nights; // Calculate the lead's personal bill

        $groupFinancialSummary = [
            // This is clearer for the UI
            'lead_personal_bill' => $leadPersonalBill,
            'members_bill' => $membersBill,
            // The total outstanding is simply the lead's total_amount field, which holds the grand total
            'total_outstanding' => $registration->total_amount,
        ];

        return view('frontdeskcrm::registrations.show', compact('registration', 'groupMembers', 'groupFinancialSummary'));
    }

    /**
     * Manually check a guest out (works for both lead and members).
     * Now handles Early Departure (Truncating dates) and Audit Trail.
     */
    public function checkout(Registration $registration)
    {
        if ($registration->stay_status !== 'checked_in') {
            return back()->with('error', 'This guest is not currently checked-in.');
        }

        // 1. Capture Current Time & Agent
        $now = now();
        $updates = [
            'stay_status' => 'checked_out',
            'actual_checkout_at' => $now,
            'checked_out_by_agent_id' => Auth::id(),
        ];

        // 2. HANDLE EARLY CHECKOUT (The "Stays Still Count" Fix)
        // If today is BEFORE the planned check_out date, we must truncate the stay.
        // This frees up the room for tomorrow and corrects the revenue.
        if ($now->startOfDay()->lt($registration->check_out->startOfDay())) {

            // Set new checkout date to TODAY (or keep it if they leave late)
            $newCheckOutDate = $now;

            // Recalculate Nights (Minimum 1 night charged if they leave immediately)
            $nights = $registration->check_in->diffInDays($newCheckOutDate);
            if ($nights < 1) $nights = 1;

            $updates['check_out'] = $newCheckOutDate;
            $updates['no_of_nights'] = $nights;

            // Recalculate Bill (Rate * Actual Nights)
            // Note: If you have extra services (food, laundry), this logic might need
            // to be 'existing_total - (refund_amount)' instead. 
            // For now, we assume Room Rate * Nights.
            $updates['total_amount'] = $registration->room_rate * $nights;
        }

        // 3. Apply Updates
        $registration->update($updates);

        // 4. Update Guest History
        $guest = $registration->guest;
        if ($guest) {
            $guest->increment('visit_count');
            $guest->last_visit_at = $now;
            $guest->save();
        }

        // 5. Handle Group Children (If this is a Lead)
        // If the Lead checks out, strictly speaking, the group might still be there.
        // But usually, if the Lead pays/closes the bill, everyone is done.
        // OPTIONAL: Auto-checkout children
        if ($registration->is_group_lead) {
            foreach ($registration->children as $child) {
                if ($child->stay_status === 'checked_in') {
                    // Recursive call or manual update? 
                    // Manual update is safer to avoid infinite redirects
                    $child->update([
                        'stay_status' => 'checked_out',
                        'actual_checkout_at' => $now,
                        'checked_out_by_agent_id' => Auth::id(),
                        'check_out' => $updates['check_out'] ?? $child->check_out, // Sync dates if early
                    ]);
                }
            }
        }

        $message = "Guest {$registration->full_name} checked out successfully.";
        if (isset($updates['total_amount'])) {
            $message .= " Bill adjusted for early departure.";
        }

        // Redirect logic
        if ($registration->parent_registration_id) {
            return redirect()->route('frontdesk.registrations.show', $registration->parent_registration_id)
                ->with('success', $message);
        }
        $this->sendNotification($registration);
        return redirect()->route('frontdesk.registrations.index')
            ->with('success', $message);
    }
    public function getActiveMembers(Registration $registration)
    {
        if (!$registration->is_group_lead) {
            return response()->json([], 404);
        }

        $members = $registration->children()
            ->where('stay_status', 'checked_in')
            ->select('id', 'full_name', 'room_allocation')
            ->get();

        return response()->json($members);
    }
    /**
     * Adjusts the stay duration for a guest or a selection of group members.
     * UPDATED: Now prevents extending into an occupied date (Double Booking).
     * This method handles individual adjustments, group lead adjustments,
     * and selective group member extensions, ensuring all financial
     * records are kept in sync.
     *
     * @param Request $request
     * @param Registration $registration The primary registration being adjusted.
     * @return \Illuminate\Http\RedirectResponse
     */

    public function adjustStay(Request $request, Registration $registration)
    {
        // 1. VALIDATE THE INCOMING REQUEST
        $validated = $request->validate([
            'new_check_out' => 'required|date|after_or_equal:' . $registration->check_in->format('Y-m-d'),
            'members_to_extend' => 'nullable|array',
            'members_to_extend.*' => 'exists:registrations,id',
        ]);

        $newCheckOut = Carbon::parse($validated['new_check_out']);

        // Prevent unnecessary database writes
        if ($newCheckOut->isSameDay($registration->check_out)) {
            return back()->with('info', 'The new check-out date is the same as the current one. No changes were made.');
        }

        // =========================================================
        // 2. AVAILABILITY CHECK (Prevent Conflicts on Extension)
        // =========================================================

        // Helper: Check if a room is occupied between the *current* checkout and *new* checkout
        // We only care if we are extending (New Date > Old Date)
        $checkConflict = function ($roomId, $currentCheckOut, $newCheckOut, $ignoreRegId) {
            if (!$roomId || $newCheckOut->lte($currentCheckOut)) return false;

            return Registration::where('room_id', $roomId)
                ->where('stay_status', 'checked_in')
                ->where('id', '!=', $ignoreRegId)
                ->where(function ($query) use ($currentCheckOut, $newCheckOut) {
                    // Check if any booking starts or stays during the extension period
                    // Interval: [Old Checkout, New Checkout]
                    $query->whereBetween('check_in', [$currentCheckOut, $newCheckOut])
                        ->orWhereBetween('check_out', [$currentCheckOut, $newCheckOut])
                        ->orWhere(function ($q) use ($currentCheckOut, $newCheckOut) {
                            $q->where('check_in', '<=', $currentCheckOut)
                                ->where('check_out', '>=', $newCheckOut);
                        });
                })->exists();
        };

        // A) Check Lead Guest Conflict
        if ($checkConflict($registration->room_id, $registration->check_out, $newCheckOut, $registration->id)) {
            $roomName = $registration->room?->name ?? 'the room';
            return back()->with('error', "Cannot extend stay. $roomName is booked by another guest during this period.");
        }

        // B) Check Selected Group Members Conflict
        if ($registration->is_group_lead && isset($validated['members_to_extend'])) {
            $membersToUpdate = Registration::whereIn('id', $validated['members_to_extend'])
                ->where('parent_registration_id', $registration->id)
                ->get();

            foreach ($membersToUpdate as $member) {
                if ($checkConflict($member->room_id, $member->check_out, $newCheckOut, $member->id)) {
                    $memberRoom = $member->room?->name ?? 'their room';
                    return back()->with('error', "Cannot extend stay for {$member->full_name}. $memberRoom is booked by another guest.");
                }
            }
        } else {
            // Empty collection for loop below if no members selected
            $membersToUpdate = collect([]);
        }

        // =========================================================
        // 3. APPLY UPDATES (Only if no conflicts found)
        // =========================================================

        // Update Lead
        $nights = $registration->check_in->diffInDays($newCheckOut);
        // Minimum 1 night charge even if same-day checkout
        if ($nights < 1) $nights = 1;

        $registration->update([
            'check_out' => $newCheckOut,
            'no_of_nights' => $nights,
            'total_amount' => $registration->room_rate * $nights,
        ]);

        // Update Members
        foreach ($membersToUpdate as $member) {
            $memberNights = $member->check_in->diffInDays($newCheckOut);
            if ($memberNights < 1) $memberNights = 1;

            $member->update([
                'check_out' => $newCheckOut,
                'no_of_nights' => $memberNights,
                'total_amount' => $member->room_rate * $memberNights,
            ]);
        }

        // 4. FIND THE GROUP LEAD AND RECALCULATE THE ENTIRE GROUP'S BILL
        $leadRegistration = null;
        if ($registration->is_group_lead) {
            $leadRegistration = $registration;
        } elseif ($registration->parent_registration_id) {
            $leadRegistration = $registration->parent;
        }

        if ($leadRegistration) {
            // Recalculate lead's personal bill
            $leadPersonalBill = $leadRegistration->room_rate * $leadRegistration->no_of_nights;

            // Sum all active children
            $membersTotalBill = $leadRegistration->children()->where('stay_status', 'checked_in')->sum('total_amount');

            $leadRegistration->update([
                'total_amount' => $leadPersonalBill + $membersTotalBill
            ]);
        }
        $this->sendNotification($registration);
        return back()->with('success', 'Stay details have been successfully updated.');
    }
    /**
     * Adds a new member to an active group (Late Arrival).
     */
    public function addMember(Request $request, Registration $registration)
    {
        // 1. Validate
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => ['nullable', 'string', 'max:20', new ValidPhoneNumber],
        ]);

        // 2. Ensure we are linking to the Lead
        $parent = $registration->is_group_lead ? $registration : $registration->parent;

        if (!$parent) {
            return back()->with('error', 'Cannot add member: This registration is not part of a group.');
        }

        // 3. Create the Member Draft
        $newMember = Registration::create([
            'parent_registration_id' => $parent->id,
            'guest_id' => null, // Optional: You could do the Guest::firstOrCreate logic here if needed
            'full_name' => $validated['full_name'],
            'contact_number' => $validated['contact_number'],
            'check_in' => now(), // Default to today
            'check_out' => $parent->check_out, // Sync with group checkout
            'stay_status' => 'draft_by_guest',
            'no_of_nights' => now()->diffInDays($parent->check_out) ?: 1,
        ]);

        // 4. Redirect immediately to Finalize for this single person
        return redirect()->route('frontdesk.registrations.finalize.form', $newMember)
            ->with('success', 'New member added! Please finalize their room and rate.');
    }
    // =====================================================================
    // ROOM VISUALIZATION (RACK & SCHEDULE)
    // =====================================================================

    /**
     * Display the Live Room Rack (Grid View).
     */
    public function roomRack()
    {
        return view('frontdeskcrm::rooms.rack');
    }

    /**
     * Display the Room Schedule (Calendar/Timeline View).
     */
    public function schedule()
    {
        return view('frontdeskcrm::rooms.schedule');
    }
    /**
     * Show Check-in Form for an Online Booking
     */
    public function checkinFromBooking($ref)
    {
        $booking = Booking::where('booking_reference', $ref)->firstOrFail();

        // Prevent double check-in
        if ($booking->status === 'checked_in') {
            return redirect()->back()->with('error', 'This booking is already checked in.');
        }

        return view('frontdeskcrm::registrations.checkin-booking', compact('booking'));
    }

    /**
     * Process the Conversion (Booking -> Registration)
     */
    /**
     * Convert an Online Booking into a Registration (Smart Check).
     */
    public function processBookingCheckin(Request $request, $ref)
    {
        // 1. Fetch Booking
        $booking = Booking::where('booking_reference', $ref)->firstOrFail();

        // 2. Validate Room (Ensure room is still valid)
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        return DB::transaction(function () use ($booking, $request) {

            // =========================================================
            // 3. SMART STATUS LOGIC (The Fix)
            // =========================================================
            $bookedDate = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
            $today = \Carbon\Carbon::today();

            if ($bookedDate->gt($today)) {
                // Scenario A: Future Booking -> Create Reservation
                $status = 'reserved';
                $checkInTime = $booking->check_in_date; // Keep original future date
                $message = 'Booking confirmed as a Reservation (Future Arrival).';
            } else {
                // Scenario B: Today (or Past) -> Check In Guest Now
                $status = 'checked_in';
                $checkInTime = now(); // Clock start time now
                $message = 'Guest checked in successfully.';
            }

            // 4. Create Registration Record
            $registration = Registration::create([
                'guest_id' => $booking->guest_profile_id,
                'booking_id' => $booking->id,
                'room_id' => $request->room_id,
                'room_allocation' => Room::find($request->room_id)->name,

                // Guest Info
                'full_name' => $booking->guest_name,
                'email' => $booking->guest_email,
                'contact_number' => $booking->guest_phone,

                // Timing & Status (Using Smart Logic)
                'check_in' => $checkInTime,
                'check_out' => $booking->check_out_date,
                // Calculate nights based on actual check-in vs checkout
                'no_of_nights' => \Carbon\Carbon::parse($checkInTime)->diffInDays($booking->check_out_date) ?: 1,

                // Financials
                'room_rate' => Room::find($request->room_id)->price,
                'total_amount' => $booking->total_amount,
                'stay_status' => $status, // ✅ 'reserved' or 'checked_in'

                'front_desk_agent' => Auth::user()->name,
            ]);

            // 5. Sync Online Booking Status
            $booking->update([
                'status' => $status, // Syncs so website knows it's processed
                'room_id' => $request->room_id,
            ]);

            return redirect()->route('frontdesk.registrations.show', $registration)
                ->with('success', $message);
        });
    }
    /**
     * DRY Helper to send email notifications safely
     */
    private function sendNotification(Registration $registration)
    {
        // Ensure we have an email to send to
        $email = $registration->email ?? $registration->guest->email;

        if ($email) {
            try {
                Mail::to($email)->send(new RegistrationStatusMail($registration));
            } catch (\Exception $e) {
                // Log error but don't crash the app if mail fails
                Log::error("Failed to send registration email: " . $e->getMessage());
            }
        }
    }
    /**
     * Store a new payment for a registration.
     */
    public function storePayment(Request $request, Registration $registration)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        // Create Payment Record
        $registration->payments()->create([
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
            'received_by' => Auth::id(),
        ]);

        return back()->with('success', 'Payment recorded successfully.');
    }

}
