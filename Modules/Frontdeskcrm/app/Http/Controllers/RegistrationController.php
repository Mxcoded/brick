<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Finance\Services\PostingService;
use Modules\Frontdeskcrm\Emails\RegistrationStatusMail;
use Modules\Frontdeskcrm\Http\Requests\FinalizeRegistrationRequest;
use Modules\Frontdeskcrm\Http\Requests\StoreRegistrationRequest;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestType;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Rules\ValidPhoneNumber;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Refund;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\PaymentGatewayManager;
use Modules\Website\Services\RoomAssignmentService;
use Modules\Website\Services\RoomAvailabilityService;

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
                        'no_of_guests' => $booking->adults + $booking->children,
                        'is_group_lead' => $booking->booking_group_id ? '1' : '0',
                    ],
                    // Store ID to link it later in store()
                    'linked_booking_id' => $booking->id,
                ]);
            }
        }

        return view('frontdeskcrm::registrations.create');
    }

    /**
     * Handle the initial search from the guest (Kiosk/Tablet).
     * Optimized with strict pattern matching for BK-style references.
     */
    public function handleGuestSearch(Request $request)
    {
        // 1. Basic validation for the input field
        $request->validate([
            'search_query' => 'required|string|max:255',
        ]);

        session()->forget(['returning_guest', 'guest_data', 'linked_booking_id']);
        $query = strtoupper(trim($request->input('search_query'))); // Normalize to uppercase for BK check

        // ---------------------------------------------------------
        // PATTERN DETECTION (Intent Identification)
        // ---------------------------------------------------------

        // Check for Email
        $isEmail = filter_var($query, FILTER_VALIDATE_EMAIL);

        // Global phone check: 10-15 digits
        $cleanPhone = preg_replace('/[\s\-\(\)]+/', '', $query);
        $isPhone = preg_match('/^\+?[0-9]{10,15}$/', $cleanPhone);

        // STRICT REF CHECK: Must start with BK and be exactly 8 characters
        $isRef = preg_match('/^BK[A-Z0-9]{6}$/', $query);

        // GROUP REF CHECK: Must start with GRP (e.g., GRP-67890ABC)
        $isGroupRef = preg_match('/^GRP-[A-Z0-9]{8}$/', $query);

        // ---------------------------------------------------------
        // ERROR HANDLING: Unrecognized Input
        // ---------------------------------------------------------
        if (! $isEmail && ! $isPhone && ! $isRef && ! $isGroupRef) {
            return redirect()->route('frontdesk.registrations.create')
                ->with('error', 'We didn\'t recognize that format. Please enter a valid Email, Phone, Booking Ref (BK...), or Group Ref (GRP-...).')
                ->withInput();
        }

        // ---------------------------------------------------------
        // TARGETED LOOKUP: Group Booking Reference (GRP-...)
        // ---------------------------------------------------------
        if ($isGroupRef) {
            $bookings = Booking::where('booking_group_id', $query)
                ->with('guest', 'roomType', 'roomUnit')
                ->get();

            if ($bookings->isNotEmpty()) {
                return $this->processFoundGroupBooking($bookings, $query);
            }

            return redirect()->route('frontdesk.registrations.create')
                ->with('error', 'No bookings found with this group reference.')
                ->withInput();
        }

        // ---------------------------------------------------------
        // TARGETED LOOKUP: Single Booking Reference (BK...)
        // ---------------------------------------------------------
        if ($isRef) {
            $booking = Booking::where('booking_reference', $query)->with('guest', 'room')->first();

            if ($booking) {
                // 1. FRAUD PREVENTION: Ensure this specific booking isn't already active
                $alreadyInSystem = Registration::where('booking_id', $booking->id)
                    ->whereIn('stay_status', ['checked_in', 'checked_out', 'reserved'])
                    ->exists();

                if ($alreadyInSystem) {
                    return redirect()->route('frontdesk.registrations.create')
                        ->with('error', 'This booking reference has already been processed. Please see the front desk.')
                        ->withInput();
                }

                // 2. EXPIRED CHECK-IN PROTECTION
                $today = now()->startOfDay();
                $checkInDate = Carbon::parse($booking->check_in_date)->startOfDay();
                $checkOutDate = Carbon::parse($booking->check_out_date)->startOfDay();

                // Scenario A: The entire stay has passed
                if ($today->gt($checkOutDate)) {
                    return redirect()->route('frontdesk.registrations.create')
                        ->with('error', 'This booking has expired (Departure was scheduled for '.$checkOutDate->format('M d, Y').'). Please see the front desk for assistance.')
                        ->withInput();
                }

                // Scenario B: Late Arrival (Check-in was yesterday or earlier, but stay isn't over)
                if ($today->gt($checkInDate)) {
                    // We allow them to proceed but can flash an info message
                    session()->flash('info', 'Welcome! We noticed your scheduled arrival was '.$checkInDate->format('M d').'. We have held your reservation.');
                }

                return $this->processFoundBooking($booking);
            }
        }

        // ---------------------------------------------------------
        // TARGETED LOOKUP: Guest Profile (Phone/Email)
        // ---------------------------------------------------------
        if ($isPhone || $isEmail) {
            // Fallback for Nigerian local 080 format
            $normalizedPhone = (preg_match('/^0[7-9][0-1][0-9]{8}$/', $cleanPhone))
                ? '+234'.substr($cleanPhone, 1)
                : $cleanPhone;

            $guest = Guest::where('email', $query)
                ->orWhere('contact_number', $cleanPhone)
                ->orWhere('contact_number', $normalizedPhone)
                ->first();

            if ($guest) {
                return $this->processFoundGuest($guest);
            }
        }

        // ---------------------------------------------------------
        // REDIRECT: Valid Format but No Record Found (New Guest Path)
        // ---------------------------------------------------------
        return redirect()->route('frontdesk.registrations.create')
            ->with('status', 'Welcome! We couldn’t find an existing record. Let’s start a new registration for you.')
            ->with('search_query', $query);
    }

    /**
     * Helper to process Booking logic to keep code clean
     */
    private function processFoundBooking($booking)
    {
        $totalGuests = $booking->adults + $booking->children;
        $totalNights = $booking->check_in_date->diffInDays($booking->check_out_date);
        $guest = $booking->guest;
        $title = $guest ? (($guest->gender == 'Male') ? 'Mr. ' : 'Ms. ') : '';

        session([
            'returning_guest' => [
                'id' => $booking->guest_profile_id,
                'name' => $booking->guest_name,
                'masked_email' => preg_replace('/(?<=.).(?=.*@)/', '*', $booking->guest_email),
                'masked_phone' => '******'.substr($booking->guest_phone, -4),
            ],
            'guest_data' => [
                // Personal Info (Step 1)
                'title' => $title,
                'full_name' => $booking->guest_name,
                'email' => $booking->guest_email,
                'contact_number' => $booking->guest_phone,
                'birthday' => $guest?->birthday ? Carbon::parse($guest->birthday)->format('Y-m-d') : null,
                'gender' => $guest?->gender,
                'nationality' => $guest?->nationality,
                'home_address' => $guest?->home_address,
                'occupation' => $guest?->occupation,
                'company_name' => $guest?->company_name,
                'identification_type' => $guest?->identification_type,
                'identification_number' => $guest?->identification_number,
                // Emergency Contact (Step 2)
                'emergency_name' => $guest?->emergency_name,
                'emergency_contact' => $guest?->emergency_contact,
                // Stay Details (Step 3)
                'check_in' => $booking->check_in_date->format('Y-m-d'),
                'check_out' => $booking->check_out_date->format('Y-m-d'),
                'no_of_nights' => $totalNights,
                'no_of_guests' => $totalGuests,
                'is_group_lead' => $booking->booking_group_id ? '1' : '0',
                // Billing Info
                'room_rate' => $booking->room->price ?? null,
                'total_amount' => $booking->total_amount,
                'payment_status' => $booking->payment_status,
                'payment_method' => $booking->payment_method,
            ],
            'linked_booking_id' => $booking->id,
        ]);

        return redirect()->route('frontdesk.registrations.create')
            ->with('success', "Booking Found! Welcome, {$booking->guest_name}.");
    }

    /**
     * Helper to process Guest logic to keep code clean
     */
    private function processFoundGuest($guest)
    {
        session([
            'returning_guest' => [
                'id' => $guest->id,
                'name' => $guest->full_name,
                'masked_email' => $guest->email ? preg_replace('/(?<=.).(?=.*@)/', '*', $guest->email) : 'N/A',
                'masked_phone' => '******'.substr($guest->contact_number, -4),
            ],
            'guest_data' => [
                // Personal Info (Step 1)
                'title' => $guest->title,
                'full_name' => $guest->full_name,
                'email' => $guest->email,
                'contact_number' => $guest->contact_number,
                'birthday' => $guest->birthday ? Carbon::parse($guest->birthday)->format('Y-m-d') : null,
                'gender' => $guest->gender,
                'nationality' => $guest->nationality,
                'home_address' => $guest->home_address,
                'occupation' => $guest->occupation,
                'company_name' => $guest->company_name,
                // Emergency Contact (Step 2)
                'emergency_name' => $guest->emergency_name,
                'emergency_contact' => $guest->emergency_contact,
            ],
        ]);

        return redirect()->route('frontdesk.registrations.create')
            ->with('success', "Welcome back, {$guest->full_name}!");
    }

    /**
     * Helper to process Group Booking (multiple rooms under one GRP reference)
     * Stores group data in session for agent review during finalization.
     */
    private function processFoundGroupBooking($bookings, string $groupId)
    {
        $primaryBooking = $bookings->first();
        $totalRooms = $bookings->count();
        $guest = $primaryBooking->guest;

        // Check if any booking in group is already registered
        $existingRegistrations = Registration::whereIn('booking_id', $bookings->pluck('id'))
            ->whereIn('stay_status', ['checked_in', 'checked_out', 'reserved'])
            ->count();

        if ($existingRegistrations > 0) {
            return redirect()->route('frontdesk.registrations.create')
                ->with('error', 'Some rooms in this group booking have already been processed. Please see the front desk.')
                ->withInput();
        }

        // Check for expired bookings
        $today = now()->startOfDay();
        $checkOutDate = Carbon::parse($primaryBooking->check_out_date)->startOfDay();
        $checkInDate = Carbon::parse($primaryBooking->check_in_date)->startOfDay();

        if ($today->gt($checkOutDate)) {
            return redirect()->route('frontdesk.registrations.create')
                ->with('error', 'This group booking has expired (Departure was scheduled for '.$checkOutDate->format('M d, Y').'). Please see the front desk.')
                ->withInput();
        }

        if ($today->gt($checkInDate)) {
            session()->flash('info', 'Welcome! We noticed your scheduled arrival was '.$checkInDate->format('M d').'. We have held your reservation.');
        }

        $title = ($guest && $guest->gender == 'Male') ? 'Mr. ' : 'Ms. ';
        $totalGuests = $bookings->sum(function ($b) {
            return $b->adults + $b->children;
        });
        $totalNights = $primaryBooking->check_in_date->diffInDays($primaryBooking->check_out_date);

        // Build room summary for the group
        $roomsSummary = $bookings->map(function ($booking) {
            return [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'room_type' => $booking->roomType->name ?? 'N/A',
                'room_type_id' => $booking->room_type_id,
                'room_unit_id' => $booking->room_unit_id,
                'adults' => $booking->adults,
                'children' => $booking->children,
                'total_amount' => $booking->total_amount,
                'payment_status' => $booking->payment_status,
            ];
        })->toArray();

        session([
            'returning_guest' => [
                'id' => $primaryBooking->guest_profile_id,
                'name' => $primaryBooking->guest_name,
                'masked_email' => preg_replace('/(?<=.).(?=.*@)/', '*', $primaryBooking->guest_email),
                'masked_phone' => '******'.substr($primaryBooking->guest_phone, -4),
            ],
            'guest_data' => [
                // Personal Info from primary booking guest
                'title' => $title,
                'full_name' => $primaryBooking->guest_name,
                'email' => $primaryBooking->guest_email,
                'contact_number' => $primaryBooking->guest_phone,
                'birthday' => $guest && $guest->birthday ? Carbon::parse($guest->birthday)->format('Y-m-d') : null,
                'gender' => $guest->gender ?? null,
                'nationality' => $guest->nationality ?? null,
                'home_address' => $guest->home_address ?? null,
                'occupation' => $guest->occupation ?? null,
                'company_name' => $guest->company_name ?? null,
                // Emergency Contact
                'emergency_name' => $guest->emergency_name ?? null,
                'emergency_contact' => $guest->emergency_contact ?? null,
                // Stay Details (shared across group)
                'check_in' => $primaryBooking->check_in_date->format('Y-m-d'),
                'check_out' => $primaryBooking->check_out_date->format('Y-m-d'),
                'no_of_nights' => $totalNights,
                'no_of_guests' => $totalGuests,
                'is_group_lead' => '1', // Always group lead for multi-room
                // Group-specific data
                'total_rooms' => $totalRooms,
                'total_amount' => $bookings->sum('total_amount'),
            ],
            // Store group info for finalization
            'linked_booking_group_id' => $groupId,
            'linked_group_bookings' => $roomsSummary,
            // Also store primary booking for backward compatibility
            'linked_booking_id' => $primaryBooking->id,
        ]);

        return redirect()->route('frontdesk.registrations.create')
            ->with('success', "Group Booking Found! Welcome, {$primaryBooking->guest_name}. ({$totalRooms} rooms)");
    }

    /**
     * Store the guest's submitted draft registration.
     * SAFE VERSION: Handles missing keys for returning guests to prevent crashes.
     */
    public function store(StoreRegistrationRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $validated = $request->validated();
            $notificationMessage = 'Registration submitted successfully!';

            $normalizePhone = function ($phone) {
                if (! $phone) {
                    return null;
                }
                $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
                if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $phone)) {
                    return '+234'.substr($phone, 1);
                }

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

            if (! $guest && $inputPhone) {
                $guest = Guest::where('contact_number', $inputPhone)->first();
            }

            // Smart Merge (Email Check) — with phone collision guard
            if (! $guest && $inputEmail) {
                $guest = Guest::where('email', $inputEmail)->first();
                if ($guest) {
                    if ($inputPhone) {
                        $phoneOwner = Guest::where('contact_number', $inputPhone)->where('id', '!=', $guest->id)->first();
                        if (! $phoneOwner) {
                            $guest->contact_number = $inputPhone;
                            $guest->save();
                            $notificationMessage .= ' We found your profile via email and updated your phone number.';
                        }
                    }
                }
            }

            // 3. DUPLICATE CHECK
            if ($guest) {
                $existingReg = Registration::where('guest_id', $guest->id)
                    ->whereDate('created_at', Carbon::today())
                    ->whereIn('stay_status', ['draft_by_guest', 'checked_in'])
                    ->first();

                if ($existingReg) {
                    return redirect()->route('frontdesk.registrations.thank-you')
                        ->with('info', 'You already have a pending registration for today. Please proceed to the front desk.');
                }
            }

            // 4. PERSISTENCE
            if ($guest) {
                // === RETURNING GUEST ===
                // [CRITICAL FIX] Use '?? $guest->attribute' to fallback to existing DB value
                // if the input is missing from the form (which happens for Secure Returning Guests).
                $updateData = [
                    'title' => $validated['title'] ?? $guest->title,
                    'full_name' => $validated['full_name'] ?? $guest->full_name ?? session('returning_guest.name'), // <--- PREVENTS CRASH
                    'nationality' => $validated['nationality'] ?? $guest->nationality,
                    'home_address' => $validated['home_address'] ?? $guest->home_address,
                    'emergency_name' => $validated['emergency_name'] ?? $guest->emergency_name,
                    'emergency_contact' => $validated['emergency_contact'] ?? $guest->emergency_contact,
                    'occupation' => $validated['occupation'] ?? $guest->occupation,
                ];
                // Only update email if it's not already taken by another guest
                $newEmail = $validated['email'] ?? null;
                if ($newEmail && $newEmail !== $guest->email) {
                    $emailOwner = Guest::where('email', $newEmail)->where('id', '!=', $guest->id)->first();
                    if (! $emailOwner) {
                        $updateData['email'] = $newEmail;
                    }
                }
                $guest->update($updateData);
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
                // Double-check phone is still unique (format might differ from earlier query)
                $existingPhoneGuest = Guest::where('contact_number', $contactNumber)->first();
                if ($existingPhoneGuest) {
                    $guest = $existingPhoneGuest;
                } else {
                    $guest = Guest::create([
                        'title' => $validated['title'] ?? null,
                        'full_name' => $fullName, // Required for new guests
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
            }

            // 5. REGISTRATION SNAPSHOT
            // Get booking data from session if available (for online bookings)
            $bookingData = session('guest_data', []);
            $linkedBookingId = session('linked_booking_id');
            $linkedBookingGroupId = session('linked_booking_group_id');

            // Calculate nights
            $checkIn = Carbon::parse($validated['check_in']);
            $checkOut = Carbon::parse($validated['check_out']);
            $noOfNights = $checkIn->diffInDays($checkOut) ?: 1;

            // If linked to a booking, fetch room info
            $roomId = null;
            $roomAllocation = null;
            $roomRate = $bookingData['room_rate'] ?? null;
            $roomTypeId = null;
            $roomUnitId = null;

            if ($linkedBookingId) {
                $linkedBooking = Booking::with(['room', 'roomType', 'roomUnit'])->find($linkedBookingId);
                if ($linkedBooking) {
                    // Legacy room support
                    if ($linkedBooking->room) {
                        $roomId = $linkedBooking->room_id;
                        $roomAllocation = $linkedBooking->room->name;
                        $roomRate = $roomRate ?? $linkedBooking->room->price;
                    }
                    // New room type/unit support
                    $roomTypeId = $linkedBooking->room_type_id;
                    $roomUnitId = $linkedBooking->room_unit_id;

                    // Build room_allocation from roomUnit if available
                    if ($linkedBooking->roomUnit && ! $roomAllocation) {
                        $roomAllocation = $linkedBooking->roomUnit->room_number.' ('.($linkedBooking->roomType->name ?? '').')';
                    }
                }
            }

            $registrationData = [
                'guest_id' => $guest->id,
                // Booking Link
                'booking_id' => $linkedBookingId,
                // Original dates (immutable for audit - only set if from web booking)
                'original_check_in_date' => $linkedBookingId ? $validated['check_in'] : null,
                'original_check_out_date' => $linkedBookingId ? $validated['check_out'] : null,
                // Group booking support
                'booking_group_id' => $linkedBookingGroupId,
                'dates_adjusted' => false,
                'stay_status' => 'draft_by_guest',

                // Guest Snapshot
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

                // Stay Details
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'no_of_nights' => $noOfNights,
                'no_of_guests' => $validated['no_of_guests'],
                'is_group_lead' => $request->boolean('is_group_lead'),

                // Room & Billing (from online booking if available)
                'room_id' => $roomId,
                'room_type_id' => $roomTypeId,
                'room_unit_id' => $roomUnitId,
                'room_allocation' => $roomAllocation,
                'room_rate' => $roomRate,
                'total_amount' => $bookingData['total_amount'] ?? ($roomRate ? $roomRate * $noOfNights : null),
                'payment_status' => $bookingData['payment_status'] ?? null,
                'payment_method' => $bookingData['payment_method'] ?? null,

                // Policies
                'agreed_to_policies' => true,
                'opt_in_data_save' => $request->boolean('opt_in_data_save'),
            ];

            // Signature Logic
            if (! empty($validated['guest_signature'])) {
                $signatureImage = $validated['guest_signature'];
                if (str_contains($signatureImage, ',')) {
                    $signatureImage = explode(',', $signatureImage)[1];
                }
                $signatureImage = base64_decode($signatureImage);
                $imageName = 'signatures/'.uniqid().'.png';
                Storage::disk('public')->put($imageName, $signatureImage);
                $registrationData['guest_signature'] = $imageName;
            }

            $registration = Registration::create($registrationData);

            // 6. GROUP MEMBERS
            if ($request->boolean('is_group_lead') && ! empty($validated['group_members'])) {
                foreach ($validated['group_members'] as $memberData) {

                    $memberPhone = $normalizePhone($memberData['contact_number'] ?? null);
                    $memberEmail = $memberData['email'] ?? null;
                    $memberGuest = null;

                    if ($memberPhone) {
                        $memberGuest = Guest::where('contact_number', $memberPhone)->first();
                    }
                    if (! $memberGuest && $memberEmail) {
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
            session()->forget(['returning_guest', 'guest_data', 'linked_booking_id', 'linked_booking_group_id', 'linked_group_bookings']);
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
     * Display the agent's dashboard of all registrations with Search & Filter,
     * synced with upcoming and overdue website bookings.
     */
    public function index(Request $request)
    {
        // 1. Existing Registration Logic (Search/Filter)
        $query = Registration::query()->with(['room', 'roomUnit', 'roomType', 'guest']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhereHas('booking', fn ($b) => $b->where('booking_reference', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('stay_status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('check_in', $request->date);
        }

        $registrations = $query->latest()->paginate(10);

        // 2. Sync website bookings — bookings that are ready for check-in
        if (class_exists(Booking::class)) {
            $today = now()->startOfDay();
            $bookedIds = Registration::whereNotNull('booking_id')->pluck('booking_id')->toArray();

            // Bookings whose check-in has passed (overdue) — auto-mark no_show after 1 day grace
            $overdueBookings = Booking::whereIn('status', ['pending', 'confirmed'])
                ->where('check_in_date', '<', $today)
                ->whereNotIn('id', $bookedIds)
                ->get();

            foreach ($overdueBookings as $booking) {
                $daysOverdue = $today->diffInDays($booking->check_in_date, false);
                if ($daysOverdue >= 1) {
                    // Auto no-show: check-in was 2+ days ago with no action
                    $booking->update(['status' => 'no_show']);
                    Log::info("Auto no-show for booking {$booking->booking_reference} — {$daysOverdue} day(s) overdue.");
                }
            }

            // Re-fetch overdue bookings excluding those just auto-moved (only today/yesterday)
            $overdueBookings = Booking::whereIn('status', ['pending', 'confirmed'])
                ->where('check_in_date', '<', $today)
                ->whereNotIn('id', $bookedIds)
                ->orderBy('check_in_date', 'asc')
                ->get();

            // Upcoming arrivals (check-in today or in the future)
            $expectedArrivals = Booking::whereIn('status', ['pending', 'confirmed'])
                ->where('check_in_date', '>=', $today)
                ->whereNotIn('id', $bookedIds)
                ->orderBy('check_in_date', 'asc')
                ->get();
        }

        return view('frontdeskcrm::registrations.index', compact('registrations', 'expectedArrivals', 'overdueBookings'));
    }
    // --- NEW "WALK-IN" FEATURE (Scenario 3) ---

    /**
     * Show the agent a simple form to create a new walk-in guest.
     */
    public function createWalkin()
    {
        $rooms = Room::whereIn('status', ['available', 'booked'])->orderBy('name')->get();
        $roomTypes = RoomType::with(['units' => function ($q) {
            $q->whereIn('status', ['available', 'booked'])->orderBy('room_number');
        }])->where('is_active', true)->ordered()->get();

        $roomTypesJson = $roomTypes->map(function ($rt) {
            return [
                'id' => $rt->id,
                'name' => $rt->name,
                'price' => (float) $rt->price,
                'capacity' => $rt->capacity,
                'units' => $rt->units->map(function ($u) {
                    return ['id' => $u->id, 'room_number' => $u->room_number, 'status' => $u->status];
                })->values()->toArray(),
            ];
        })->keyBy('id');

        return view('frontdeskcrm::registrations.create-walkin', compact('rooms', 'roomTypes', 'roomTypesJson'));
    }

    /**
     * AJAX Lookup for Walk-in form.
     * Finds a guest by phone number (checks both Local and International formats).
     */
    public function lookupGuest(Request $request)
    {
        $rawInput = $request->query('contact_number');

        if (! $rawInput) {
            return response()->json(['found' => false]);
        }

        // 1. Clean the input (remove spaces, dashes, brackets)
        $cleanPhone = preg_replace('/[\s\-\(\)]+/', '', $rawInput);

        // 2. Create the Normalized Version (International +234)
        $internationalPhone = $cleanPhone;
        if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $cleanPhone)) {
            $internationalPhone = '+234'.substr($cleanPhone, 1);
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
                    'title' => $guest->title,
                    'nationality' => $guest->nationality,
                    'home_address' => $guest->home_address,
                    'contact_number' => $guest->contact_number,
                    'occupation' => $guest->occupation,
                    'company_name' => $guest->company_name,
                    'identification_type' => $guest->identification_type,
                    'identification_number' => $guest->identification_number,
                    'birthday' => $guest->birthday?->format('Y-m-d'),
                ],
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
        $checkInDate = Carbon::parse($request->check_in);
        $isFuture = $checkInDate->startOfDay()->gt(now()->startOfDay());

        // 3. AVAILABILITY CHECK (If Room or Room Unit is Selected)
        $resolvedRoomUnitId = $request->filled('room_unit_id') ? $request->room_unit_id : null;
        $resolvedRoomId = $request->filled('room_id') ? $request->room_id : null;
        $roomName = null;

        if ($resolvedRoomUnitId) {
            $selUnit = RoomUnit::find($resolvedRoomUnitId);
            if ($selUnit && in_array($selUnit->status, ['maintenance', 'blocked'])) {
                return back()->withInput()->withErrors(['room_unit_id' => 'The selected room unit is currently unavailable.']);
            }
            $availabilityService = app(RoomAvailabilityService::class);
            if ($selUnit && ! $availabilityService->isUnitAvailable($selUnit->id, $request->check_in, $request->check_out)) {
                return back()->withInput()->withErrors(['room_unit_id' => 'This room is not available for the selected dates.']);
            }
            $roomName = 'Room '.$selUnit->room_number;
        } elseif ($resolvedRoomId) {
            $room = Room::find($resolvedRoomId);
            if ($room && $room->status === 'maintenance') {
                return back()->withInput()->withErrors(['room_id' => 'The selected room is under maintenance and cannot be assigned.']);
            }
            $isOccupied = Registration::where('room_id', $resolvedRoomId)
                ->where('stay_status', 'checked_in')
                ->where(function ($query) use ($request) {
                    $query->whereBetween('check_in', [$request->check_in, $request->check_out])
                        ->orWhereBetween('check_out', [$request->check_in, $request->check_out])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('check_in', '<=', $request->check_in)
                                ->where('check_out', '>=', $request->check_out);
                        });
                })->exists();
            if ($isOccupied) {
                return back()->withInput()->withErrors(['room_id' => 'The selected room is occupied for these dates.']);
            }
            $selRoomUnit = RoomUnit::where('room_number', $room->name)->first();
            if ($selRoomUnit) {
                $availabilityService = app(RoomAvailabilityService::class);
                if (! $availabilityService->isUnitAvailable($selRoomUnit->id, $request->check_in, $request->check_out)) {
                    return back()->withInput()->withErrors(['room_id' => 'Room not available (stop sell, maintenance, or blocked).']);
                }
                $resolvedRoomUnitId = $selRoomUnit->id;
            }
            $roomName = $room->name;
        }

        // 4. Generate Reservation Code (similar to booking code format)
        do {
            $reservationCode = 'FD'.date('y').strtoupper(Str::random(4));
        } while (Registration::where('reservation_code', $reservationCode)->exists());

        // 5. Create Registration
        $registration = Registration::create([
            'guest_id' => $guest->id,
            'reservation_code' => $reservationCode,

            // Snapshot Fields
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'gender' => $request->gender,

            // Stay Details
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'no_of_guests' => $request->no_of_guests,

            // Room Assignment
            'room_id' => $resolvedRoomId,
            'room_unit_id' => $resolvedRoomUnitId,
            'room_type_id' => $request->room_type_id,
            'room_allocation' => $roomName,
            'room_rate' => $request->room_rate,

            // Billing & Options
            'billing_type' => $request->billing_type ?? 'consolidate',
            'payment_method' => $request->payment_method,
            'bed_breakfast' => $request->boolean('bed_breakfast'),

            // Agent Audit
            'front_desk_agent' => Auth::user()->name,

            // Dynamic Status
            'stay_status' => $isFuture ? 'reserved' : 'draft_by_guest',
            'registration_date' => now(),
        ]);

        $this->sendNotification($registration);

        // 6. DYNAMIC REDIRECT with reservation code in message
        if ($isFuture) {
            return redirect()->route('frontdesk.registrations.index')
                ->with('success', "Reservation [{$reservationCode}] created! Guest can sign at the kiosk (".route('frontdesk.kiosk.sign').') using this code.');
        } else {
            return redirect()->route('frontdesk.registrations.finalize.form', $registration)
                ->with('success', "Walk-in [{$reservationCode}] created. Ask guest to sign at the kiosk (".route('frontdesk.kiosk.sign').') using this code.');
        }
    }

    /**
     * Show the form for an agent to finalize a draft.
     * UPDATED: Now fetches Rooms from the Website module.
     */
    public function showFinalizeForm(Registration $registration)
    {
        // Allow both 'draft_by_guest' AND 'reserved' statuses to be finalized.
        if (! in_array($registration->stay_status, ['draft_by_guest', 'reserved'])) {
            return redirect()->route('frontdesk.registrations.show', $registration)
                ->with('error', 'This registration has already been finalized.');
        }

        // Load relationships for comprehensive display
        $registration->load(['booking.roomType', 'booking.roomUnit', 'roomUnit', 'roomType', 'guest']);

        $groupMembers = Registration::where('parent_registration_id', $registration->id)->get();
        $bookingSources = BookingSource::where('is_active', true)->get();
        $guestTypes = GuestType::where('is_active', true)->get();

        $availabilityService = app(RoomAvailabilityService::class);

        // Get room types and their units that pass the full availability check
        $roomTypes = RoomType::active()
            ->ordered()
            ->get()
            ->map(function ($roomType) use ($availabilityService, $registration) {
                $result = $availabilityService->getAvailableUnits(
                    $roomType->id,
                    $registration->check_in,
                    $registration->check_out,
                    null,
                    $registration->id
                );
                $roomType->setRelation('units', $result);

                return $roomType;
            });

        // Get available room units using the unified availability service
        $availableUnits = collect();
        foreach ($roomTypes as $roomType) {
            $availableUnits = $availableUnits->merge($roomType->units);
        }
        $availableUnits = $availableUnits->sortBy('room_number');

        // Legacy rooms fallback (filtered to non-maintenance)
        $rooms = Room::whereIn('status', ['available', 'booked'])->orderBy('name')->get();

        return view('frontdeskcrm::registrations.finalize', compact(
            'registration',
            'groupMembers',
            'bookingSources',
            'guestTypes',
            'roomTypes',
            'availableUnits',
            'rooms'
        ));
    }

    /**
     * Process the agent's finalization.
     * Handles: Walk-ins, Web Bookings, Group Bookings, Early/Late arrivals
     */
    public function finalize(FinalizeRegistrationRequest $request, Registration $registration)
    {
        Log::info('Finalize method called', ['registration_id' => $registration->id]);

        try {
            return DB::transaction(function () use ($request, $registration) {
                $validated = $request->validated();
                Log::info('Validation passed', ['validated' => $validated]);
                $billingType = $request->input('billing_type', 'consolidate');
                $today = now()->startOfDay();
                $checkInDate = Carbon::parse($registration->check_in)->startOfDay();
                $checkOutDate = Carbon::parse($registration->check_out)->startOfDay();

                // =========================================================
                // 0. DATE LOGIC SANITY CHECKS
                // =========================================================

                // A) PREVENT "ZOMBIE" CHECK-INS (Date has passed)
                if ($today->gte($checkOutDate)) {
                    return back()->with('error', 'Cannot check in: The departure date ('.$checkOutDate->format('M d').') has already passed. Please mark as No-Show or create a new Walk-in.');
                }

                $datesAdjustedMessage = null;
                $billingPolicyMessage = null;
                $effectiveCheckIn = $checkInDate;

                // B) HANDLE EARLY ARRIVALS (Guest arrives before booked date)
                if ($today->lt($checkInDate)) {
                    $effectiveCheckIn = $today;
                    $datesAdjustedMessage = 'Check-in adjusted to today ('.$today->format('M d').').';
                }

                // C) HANDLE LATE ARRIVALS WITH BILLING POLICY
                $billingPolicy = $request->input('billing_policy', 'strict');
                $originalCheckIn = $request->input('original_check_in') ? Carbon::parse($request->input('original_check_in')) : null;

                if ($today->gt($checkInDate)) {
                    if ($billingPolicy === 'flexible') {
                        $effectiveCheckIn = $today;
                        $billingPolicyMessage = 'Flexible billing: Charged from actual arrival.';
                    } elseif ($billingPolicy === 'strict' && $originalCheckIn) {
                        $effectiveCheckIn = $originalCheckIn;
                        $billingPolicyMessage = 'Strict billing: Charged from original booking date.';
                    } else {
                        $effectiveCheckIn = $checkInDate;
                    }
                }

                // Calculate nights
                $nights = $effectiveCheckIn->diffInDays($checkOutDate);
                if ($nights < 1) {
                    $nights = 1;
                }

                // =========================================================
                // 1. AVAILABILITY CHECK (Unified)
                // =========================================================
                $availabilityService = app(RoomAvailabilityService::class);

                // =========================================================
                // 2. VALIDATE LEAD ROOM
                // =========================================================
                $leadRoomUnitId = $request->input('room_unit_id');
                $leadRoomTypeId = $request->input('room_type_id');
                $leadRoomAllocation = $request->input('room_allocation');

                if (! $leadRoomUnitId) {
                    return back()->withInput()->withErrors(['room_unit_id' => 'Please select a room for the guest.']);
                }

                if (! $availabilityService->isUnitAvailable($leadRoomUnitId, $effectiveCheckIn, $checkOutDate, $registration->booking_id, $registration->id)) {
                    $roomUnit = RoomUnit::find($leadRoomUnitId);
                    $roomName = $roomUnit ? "Room {$roomUnit->room_number}" : 'Selected Room';

                    return back()->withInput()->withErrors(['room_unit_id' => "$roomName is not available (occupied, stop sell, or maintenance) for the selected dates."]);
                }

                // Get room allocation name if not provided
                if (! $leadRoomAllocation && $leadRoomUnitId) {
                    $roomUnit = RoomUnit::with('roomType')->find($leadRoomUnitId);
                    if ($roomUnit) {
                        $leadRoomAllocation = $roomUnit->room_number.' ('.($roomUnit->roomType->name ?? 'Unknown').')';
                        $leadRoomTypeId = $leadRoomTypeId ?? $roomUnit->room_type_id;
                    }
                }

                // =========================================================
                // 3. PROCESS GROUP MEMBERS (if any)
                // =========================================================
                $membersTotalBill = 0;
                $groupMembersData = $request->input('group_members', []);

                if ($registration->is_group_lead && ! empty($groupMembersData)) {
                    foreach ($groupMembersData as $memberId => $memberData) {
                        $member = Registration::find($memberId);
                        if (! $member || $member->parent_registration_id !== $registration->id) {
                            continue; // Skip invalid members
                        }

                        $memberStatus = $memberData['status'] ?? 'checked_in';

                        if ($memberStatus === 'no_show') {
                            // Mark as no-show
                            $member->update(['stay_status' => 'no_show']);

                            continue;
                        }

                        // Validate member room
                        $memberRoomUnitId = $memberData['room_unit_id'] ?? null;
                        $memberRoomTypeId = $memberData['room_type_id'] ?? null;
                        $memberRoomAllocation = $memberData['room_allocation'] ?? null;
                        $memberRate = is_numeric(str_replace(',', '', $memberData['room_rate'] ?? 0))
                            ? (float) str_replace(',', '', $memberData['room_rate'])
                            : 0;

                        if (! $memberRoomUnitId) {
                            return back()->withInput()->withErrors([
                                "group_members.{$memberId}.room_unit_id" => "Please select a room for {$member->full_name}.",
                            ]);
                        }

                        if (! $availabilityService->isUnitAvailable($memberRoomUnitId, $effectiveCheckIn, $checkOutDate, $member->booking_id, $member->id)) {
                            $roomUnit = RoomUnit::find($memberRoomUnitId);
                            $roomName = $roomUnit ? "Room {$roomUnit->room_number}" : 'Selected Room';

                            return back()->withInput()->withErrors([
                                "group_members.{$memberId}.room_unit_id" => "$roomName is not available for {$member->full_name}.",
                            ]);
                        }

                        // Get room allocation name if not provided
                        if (! $memberRoomAllocation && $memberRoomUnitId) {
                            $roomUnit = RoomUnit::with('roomType')->find($memberRoomUnitId);
                            if ($roomUnit) {
                                $memberRoomAllocation = $roomUnit->room_number.' ('.($roomUnit->roomType->name ?? 'Unknown').')';
                                $memberRoomTypeId = $memberRoomTypeId ?? $roomUnit->room_type_id;
                            }
                        }

                        // Calculate member bill
                        $memberBill = $memberRate * $nights;
                        $membersTotalBill += $memberBill;

                        // Update member registration
                        $member->update([
                            'room_unit_id' => $memberRoomUnitId,
                            'room_type_id' => $memberRoomTypeId,
                            'room_allocation' => $memberRoomAllocation,
                            'room_rate' => $memberRate,
                            'bed_breakfast' => ! empty($memberData['bed_breakfast']),
                            'check_in' => $effectiveCheckIn,
                            'check_out' => $checkOutDate,
                            'no_of_nights' => $nights,
                            'total_amount' => $billingType === 'individual' ? $memberBill : 0,
                            'stay_status' => 'checked_in',
                        ]);

                        RoomUnit::where('id', $memberRoomUnitId)->update(['status' => 'occupied']);
                    }
                }

                // =========================================================
                // 4. CALCULATE LEAD BILLING
                // =========================================================
                $leadRate = is_numeric(str_replace(',', '', $validated['room_rate']))
                    ? (float) str_replace(',', '', $validated['room_rate'])
                    : 0;
                $leadPersonalBill = $leadRate * $nights;
                $finalLeadTotal = $leadPersonalBill;

                if ($billingType === 'consolidate') {
                    $finalLeadTotal += $membersTotalBill;
                }

                // =========================================================
                // 5. UPDATE LEAD REGISTRATION
                // =========================================================
                $registration->update([
                    // Room assignment
                    'room_unit_id' => $leadRoomUnitId,
                    'room_type_id' => $leadRoomTypeId,
                    'room_allocation' => $leadRoomAllocation,
                    // Billing
                    'room_rate' => $leadRate,
                    'bed_breakfast' => $request->boolean('bed_breakfast'),
                    'guest_type_id' => $validated['guest_type_id'],
                    'booking_source_id' => $validated['booking_source_id'],
                    'payment_method' => $validated['payment_method'],
                    'billing_type' => $billingType,
                    'billing_policy' => $billingPolicy,
                    'stay_status' => 'checked_in',
                    // Dates
                    'check_in' => $effectiveCheckIn,
                    'no_of_nights' => $nights,
                    'dates_adjusted' => $datesAdjustedMessage !== null || $billingPolicyMessage !== null,
                    // Totals
                    'total_amount' => $finalLeadTotal,
                    'finalized_by_agent_id' => Auth::id(),
                    'checked_in_at' => now(),
                ]);

                // =========================================================
                // 6. UPDATE ROOM UNIT STATUS
                // =========================================================
                RoomUnit::where('id', $leadRoomUnitId)->update(['status' => 'occupied']);

                // =========================================================
                // 7. SYNC ONLINE PAYMENT
                // =========================================================
                if ($registration->booking && $registration->booking->amount_paid > 0) {
                    $existing = $registration->payments()
                        ->where('reference', $registration->booking->booking_reference)
                        ->exists();

                    if (! $existing) {
                        $registration->payments()->create([
                            'amount' => $registration->booking->amount_paid,
                            'payment_method' => $registration->booking->payment_method ?? 'online',
                            'payment_date' => now()->toDateString(),
                            'reference' => $registration->booking->booking_reference,
                            'notes' => 'Online payment synced from booking.',
                            'received_by' => Auth::id(),
                        ]);
                    }
                }

                // =========================================================
                // 8. SYNC & NOTIFY
                // =========================================================
                $this->syncBookingStatus($registration);

                $successMsg = 'Check-in finalized successfully!';
                if ($datesAdjustedMessage) {
                    $successMsg .= ' '.$datesAdjustedMessage;
                }
                if ($billingPolicyMessage) {
                    $successMsg .= ' '.$billingPolicyMessage;
                }
                if ($membersTotalBill > 0 && $billingType === 'consolidate') {
                    $successMsg .= ' Group total: ₦'.number_format($finalLeadTotal);
                }

                $this->sendNotification($registration);

                Log::info('Finalize completed successfully', ['registration_id' => $registration->id]);

                return redirect()->route('frontdesk.registrations.show', $registration)
                    ->with('success', $successMsg);
            });
        } catch (\Exception $e) {
            Log::error('Finalize method error', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'An error occurred during check-in: '.$e->getMessage());
        }
    }

    // ===================================================================
    // NO-SHOW MANAGEMENT
    // ===================================================================

    /**
     * Mark a registration as No-Show.
     * Handles both single registrations and group bookings.
     *
     * Scenarios:
     * - Draft/Reserved that never showed up
     * - Group lead marking entire group as no-show
     * - Group lead marking individual member as no-show (via finalize form)
     */
    public function markNoShow(Request $request, Registration $registration)
    {
        // 1. Validate registration can be marked as no-show
        $allowedStatuses = ['draft_by_guest', 'reserved'];
        if (! in_array($registration->stay_status, $allowedStatuses)) {
            return back()->with('error', 'Only pending or reserved registrations can be marked as No-Show. Current status: '.ucfirst(str_replace('_', ' ', $registration->stay_status)));
        }

        // 2. Check if checkout date has passed (actual no-show scenario)
        $today = now()->startOfDay();
        $checkInDate = Carbon::parse($registration->check_in)->startOfDay();

        // Allow marking as no-show if:
        // - Today is past check-in date (they didn't show up)
        // - OR manually triggered by agent for valid reasons
        $isLateNoShow = $today->gt($checkInDate);

        return DB::transaction(function () use ($registration, $isLateNoShow) {
            $guestName = $registration->full_name;
            $isGroupLead = $registration->is_group_lead;

            // 3. Release room unit if assigned
            if ($registration->room_unit_id) {
                RoomUnit::where('id', $registration->room_unit_id)->update(['status' => 'available']);
            }

            // 4. Update registration status (clear room assignment)
            $registration->update([
                'stay_status' => 'no_show',
                'room_unit_id' => null,
                'checked_in_at' => null,
                'finalized_by_agent_id' => Auth::id(),
            ]);

            // 5. Handle group members if this is a group lead
            $membersMarked = 0;
            if ($isGroupLead) {
                $children = Registration::where('parent_registration_id', $registration->id)
                    ->whereIn('stay_status', ['draft_by_guest', 'reserved'])
                    ->get();

                foreach ($children as $child) {
                    if ($child->room_unit_id) {
                        RoomUnit::where('id', $child->room_unit_id)->update(['status' => 'available']);
                    }
                    $child->update([
                        'stay_status' => 'no_show',
                        'room_unit_id' => null,
                        'checked_in_at' => null,
                    ]);
                    $membersMarked++;
                }
            }

            // 5. Sync booking status back to Website module
            $this->syncBookingStatus($registration);

            // 6. Log the action
            Log::info('Registration marked as No-Show', [
                'registration_id' => $registration->id,
                'guest_name' => $guestName,
                'is_group_lead' => $isGroupLead,
                'members_affected' => $membersMarked,
                'marked_by' => Auth::id(),
                'is_late_no_show' => $isLateNoShow,
            ]);

            // 7. Build success message
            $message = "{$guestName} has been marked as No-Show.";
            if ($membersMarked > 0) {
                $message .= " {$membersMarked} group member(s) were also marked as No-Show.";
            }
            if ($isLateNoShow) {
                $message .= ' (Late arrival - check-in date was '.Carbon::parse($registration->check_in)->format('M d, Y').')';
            }

            // 8. Send notification email (if configured)
            try {
                $this->sendNotification($registration);
            } catch (\Exception $e) {
                Log::warning('Failed to send no-show notification', ['error' => $e->getMessage()]);
            }

            return redirect()->route('frontdesk.registrations.index')
                ->with('success', $message);
        });
    }

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

        return $pdf->stream('registration-'.$registration->id.'.pdf');
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

        $registration->load('guest', 'guestType', 'bookingSource', 'roomUnit', 'roomType', 'room', 'booking');
        $groupMembers = Registration::with(['roomUnit', 'roomType', 'room'])->where('parent_registration_id', $registration->id)->get();

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

        // 2. HANDLE EARLY CHECKOUT (Truncate stay)
        // Use startOfDay() to compare dates without the interference of current time
        if ($now->startOfDay()->lt($registration->check_out->startOfDay())) {
            $newCheckOutDate = $now;
            $nights = $registration->check_in->diffInDays($newCheckOutDate);
            if ($nights < 1) {
                $nights = 1;
            }

            $updates['check_out'] = $newCheckOutDate;
            $updates['no_of_nights'] = $nights;
            $updates['total_amount'] = $registration->room_rate * $nights;
        }

        // 3. HANDLE LATE CHECKOUT / OVERSTAY (Extend stay)
        // If today is AFTER the planned check_out date, extend to today
        elseif ($now->startOfDay()->gt($registration->check_out->startOfDay())) {
            $newCheckOutDate = $now;

            // Calculate total nights from original check-in to today
            $totalNights = $registration->check_in->diffInDays($newCheckOutDate);

            $updates['check_out'] = $newCheckOutDate;
            $updates['no_of_nights'] = $totalNights;

            // Recalculate bill to include the extra nights overstayed
            $updates['total_amount'] = $registration->room_rate * $totalNights;
        }

        // 4. Apply Updates
        $registration->update($updates);

        // 5. Release Room Unit
        if ($registration->room_unit_id) {
            RoomUnit::where('id', $registration->room_unit_id)->update(['status' => 'available']);
        }

        // 6. Update Guest History
        $guest = $registration->guest;
        if ($guest) {
            $guest->increment('visit_count');
            $guest->last_visit_at = $now;
            $guest->save();
        }

        // 6. Handle Group Children (Sync dates)
        if ($registration->is_group_lead) {
            foreach ($registration->children as $child) {
                if ($child->stay_status === 'checked_in') {
                    $child->update([
                        'stay_status' => 'checked_out',
                        'actual_checkout_at' => $now,
                        'checked_out_by_agent_id' => Auth::id(),
                        // Sync the check_out date for children to match the lead's final date
                        'check_out' => $updates['check_out'] ?? $child->check_out,
                        'no_of_nights' => $updates['no_of_nights'] ?? $child->no_of_nights,
                        'total_amount' => isset($updates['no_of_nights']) ? ($child->room_rate * $updates['no_of_nights']) : $child->total_amount,
                    ]);

                    if ($child->room_unit_id) {
                        RoomUnit::where('id', $child->room_unit_id)->update(['status' => 'available']);
                    }
                }
            }
        }

        // 7. Success Message Customization
        $message = "Guest {$registration->full_name} checked out successfully.";
        if ($now->startOfDay()->lt($registration->check_out->startOfDay())) {
            $message .= ' Bill adjusted for early departure.';
        } elseif ($now->startOfDay()->gt($registration->check_out->startOfDay())) {
            $message .= ' Stay extended and bill updated for overstay.';
        }

        // Sync booking status back to Website module
        $this->syncBookingStatus($registration);

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
        if (! $registration->is_group_lead) {
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
     * @param  Registration  $registration  The primary registration being adjusted.
     * @return RedirectResponse
     */
    public function adjustStay(Request $request, Registration $registration)
    {
        // 1. VALIDATE THE INCOMING REQUEST
        $validated = $request->validate([
            'new_check_out' => 'required|date|after_or_equal:'.$registration->check_in->format('Y-m-d'),
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
            if (! $roomId || $newCheckOut->lte($currentCheckOut)) {
                return false;
            }

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
        if ($nights < 1) {
            $nights = 1;
        }

        $registration->update([
            'check_out' => $newCheckOut,
            'no_of_nights' => $nights,
            'total_amount' => $registration->room_rate * $nights,
        ]);

        // Update Members
        foreach ($membersToUpdate as $member) {
            $memberNights = $member->check_in->diffInDays($newCheckOut);
            if ($memberNights < 1) {
                $memberNights = 1;
            }

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
                'total_amount' => $leadPersonalBill + $membersTotalBill,
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

        if (! $parent) {
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
    public function schedule(Request $request)
    {
        $expectedArrivals = Booking::whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', '>=', now()->subDays(1)) // Show yesterday's no-shows too
            ->orderBy('check_in_date', 'asc')
            ->get();

        return view('frontdeskcrm::rooms.schedule', compact('expectedArrivals'));
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
        $booking = Booking::where('booking_reference', $ref)->firstOrFail();

        $request->validate([
            'room_unit_id' => 'nullable|exists:room_units,id',
        ]);

        $roomUnit = null;
        $autoAssigned = false;

        if ($request->filled('room_unit_id')) {
            $roomUnit = RoomUnit::findOrFail($request->room_unit_id);
            if ($roomUnit->status === 'maintenance') {
                return back()->with('error', 'The selected room is under maintenance and cannot be assigned.');
            }
        } else {
            $assignmentService = app(RoomAssignmentService::class);
            $roomUnit = $assignmentService->autoAssign($booking);

            if (! $roomUnit) {
                return back()->with('error', 'No room unit could be auto-assigned. Please select one manually.');
            }

            $autoAssigned = true;
        }

        return DB::transaction(function () use ($booking, $roomUnit, $autoAssigned) {
            $bookedDate = Carbon::parse($booking->check_in_date)->startOfDay();
            $today = Carbon::today();

            if ($bookedDate->gt($today)) {
                $status = 'reserved';
                $checkInTime = $booking->check_in_date;
                $message = 'Booking confirmed as a Reservation (Future Arrival).';
            } else {
                $status = 'checked_in';
                $checkInTime = now();
                $message = 'Guest checked in successfully.';
            }

            if ($autoAssigned) {
                $message .= ' (Room '.$roomUnit->room_number.' auto-assigned)';
            }

            $nights = Carbon::parse($checkInTime)->diffInDays($booking->check_out_date) ?: 1;
            $roomRate = $nights > 0 ? round($booking->total_amount / $nights, 2) : $booking->total_amount;

            $registration = Registration::create([
                'guest_id' => $booking->guest_profile_id,
                'booking_id' => $booking->id,
                'room_type_id' => $roomUnit->room_type_id,
                'room_unit_id' => $roomUnit->id,
                'room_allocation' => $roomUnit->room_number,

                'full_name' => $booking->guest_name,
                'email' => $booking->guest_email,
                'contact_number' => $booking->guest_phone,

                'check_in' => $checkInTime,
                'check_out' => $booking->check_out_date,
                'no_of_nights' => $nights,

                'room_rate' => $roomRate,
                'total_amount' => $booking->total_amount,
                'stay_status' => $status,
                'front_desk_agent' => Auth::user()->name,
            ]);

            if ($status === 'checked_in') {
                $roomUnit->update(['status' => 'occupied']);
            }

            if ($booking->amount_paid > 0) {
                $registration->payments()->create([
                    'amount' => $booking->amount_paid,
                    'payment_method' => $booking->payment_method ?? 'online',
                    'payment_date' => now()->toDateString(),
                    'reference' => $booking->booking_reference,
                    'notes' => 'Online payment synced from booking.',
                    'received_by' => Auth::id(),
                ]);
            }

            $booking->update([
                'status' => $status,
                'room_unit_id' => $roomUnit->id,
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
        $email = $registration->email ?? $registration->guest?->email;

        if ($email) {
            try {
                Mail::to($email)->send(new RegistrationStatusMail($registration));
            } catch (\Exception $e) {
                // Log error but don't crash the app if mail fails
                Log::error('Failed to send registration email: '.$e->getMessage());
            }
        }
    }

    /**
     * Sync Registration status back to Website Booking.
     * This ensures real-time data integrity between CRM and Website modules.
     *
     * Status mapping:
     * - 'checked_in' -> Booking status: 'checked_in'
     * - 'checked_out' -> Booking status: 'completed'
     * - 'no_show' -> Booking status: 'no_show'
     */
    private function syncBookingStatus(Registration $registration): void
    {
        if (! $registration->booking_id) {
            return; // No web booking linked, nothing to sync
        }

        $booking = $registration->booking;
        if (! $booking) {
            return;
        }

        $statusMap = [
            'checked_in' => 'checked_in',
            'checked_out' => 'completed',
            'no_show' => 'no_show',
            'reserved' => 'confirmed',
        ];

        $newBookingStatus = $statusMap[$registration->stay_status] ?? null;

        if ($newBookingStatus && $booking->status !== $newBookingStatus) {
            $booking->update(['status' => $newBookingStatus]);

            Log::info('Booking status synced', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'registration_id' => $registration->id,
                'new_status' => $newBookingStatus,
            ]);
        }

        // If this is a group booking, sync all related bookings
        if ($registration->booking_group_id) {
            $this->syncGroupBookingStatus($registration);
        }
    }

    /**
     * Sync status for all bookings in a group when the group lead changes.
     */
    private function syncGroupBookingStatus(Registration $registration): void
    {
        if (! $registration->booking_group_id || ! $registration->is_group_lead) {
            return;
        }

        $statusMap = [
            'checked_in' => 'checked_in',
            'checked_out' => 'completed',
            'no_show' => 'no_show',
        ];

        $newStatus = $statusMap[$registration->stay_status] ?? null;
        if (! $newStatus) {
            return;
        }

        // Update all bookings in the group
        Booking::where('booking_group_id', $registration->booking_group_id)
            ->update(['status' => $newStatus]);

        Log::info('Group booking status synced', [
            'booking_group_id' => $registration->booking_group_id,
            'new_status' => $newStatus,
        ]);
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
        $payment = $registration->payments()->create([
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
            'received_by' => Auth::id(),
        ]);

        try {
            app(PostingService::class)
                ->recordSale('frontdesk', (float) $payment->amount, $payment->payment_method, 'registration_payment', $payment->id);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Post an incidental charge to the registration's folio.
     */
    public function postCharge(Request $request, Registration $registration)
    {
        $validated = $request->validate([
            'charge_type' => 'required|string|max:50',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'charge_date' => 'nullable|date',
        ]);

        $folio = app(FolioService::class)->ensureFolio($registration);
        $folio->refresh();

        app(FolioService::class)->postCharge($folio, [
            'charge_type' => $validated['charge_type'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'post_date' => $validated['charge_date'] ?? now()->toDateString(),
        ], Auth::id());

        RegistrationCharge::create([
            'registration_id' => $registration->id,
            'charge_type' => $validated['charge_type'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'charge_date' => $validated['charge_date'] ?? now()->toDateString(),
            'folio_id' => $folio->id,
        ]);

        return back()->with('success', 'Charge posted successfully.');
    }

    /**
     * Mark a website booking as no-show directly from the dashboard.
     */
    public function markBookingNoShow(Booking $booking)
    {
        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Booking cannot be marked as no-show in its current state.');
        }

        $booking->update(['status' => 'no_show']);
        Log::info("Booking {$booking->booking_reference} marked as no-show by agent.");

        return back()->with('success', "Booking {$booking->booking_reference} marked as no-show.");
    }

    /**
     * Initiate a Paystack refund for a paid booking.
     *
     * The original Paystack transaction reference is the booking reference for
     * single bookings, or the group id (GRP...) for multi-room group bookings.
     */
    public function refundBooking(Request $request, $ref)
    {
        $booking = Booking::where('booking_reference', $ref)->firstOrFail();

        if (! in_array($booking->payment_status, ['paid', 'partial'])) {
            return back()->with('error', 'Only paid bookings can be refunded.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $amount = isset($validated['amount'])
            ? (float) $validated['amount']
            : (float) $booking->amount_paid;

        $paystackReference = $booking->booking_group_id ?? $booking->booking_reference;

        $driver = app(PaymentGatewayManager::class)->driver();
        $result = $driver->refund($paystackReference, $amount, $validated['reason'] ?? 'Booking refund', [
            'booking_reference' => $booking->booking_reference,
            'initiated_by' => Auth::id(),
        ]);

        if (! ($result['status'] ?? false)) {
            return back()->with('error', 'Refund failed: '.($result['message'] ?? 'Gateway error'));
        }

        $refundData = $result['data'] ?? [];

        Refund::create([
            'gateway' => $driver->code(),
            'gateway_reference' => $refundData['reference'] ?? null,
            'transaction_reference' => $paystackReference,
            'refundable_type' => Booking::class,
            'refundable_id' => $booking->id,
            'amount' => $amount,
            'currency' => 'NGN',
            'status' => $refundData['status'] ?? 'pending',
            'reason' => $validated['reason'] ?? 'Booking refund',
            'metadata' => $refundData,
            'created_by' => Auth::id(),
            'processed_at' => ($refundData['status'] ?? null) === 'processed' ? now() : null,
        ]);

        // Flag the booking so it can't be double-refunded while pending.
        $booking->update(['payment_status' => 'refund_pending']);

        return back()->with('success', 'Refund initiated. Reference: '.($refundData['reference'] ?? 'N/A'));
    }
}
