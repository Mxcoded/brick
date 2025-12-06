<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreRegistrationRequest;
use Modules\Frontdeskcrm\Http\Requests\FinalizeRegistrationRequest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\GuestType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Modules\Frontdeskcrm\Rules\ValidEmail;
use Modules\Frontdeskcrm\Rules\ValidPhoneNumber;
use Modules\Website\Models\Room;

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
        // If the URL has ?clear=1, wipe the session and redirect clean
        if ($request->has('clear')) {
            session()->forget(['returning_guest', 'guest_data', 'search_query']);
            return redirect()->route('frontdesk.registrations.create');
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
     */
    public function store(StoreRegistrationRequest $request)
    {
        $validated = $request->validated();

        // =================================================================
        // 1. DATA PREPARATION & NORMALIZATION
        // =================================================================

        $normalizePhone = function ($phone) {
            if (!$phone) return null;
            $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
            if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $phone)) {
                $phone = '+234' . substr($phone, 1);
            }
            return $phone;
        };

        // Extract inputs safely (they might be null if hidden in the form)
        $inputPhone = isset($validated['contact_number']) ? $normalizePhone($validated['contact_number']) : null;
        $inputName  = $validated['full_name'] ?? null;
        $inputEmail = $validated['email'] ?? null;

        // Fields that might be hidden for returning guests but need saving/updating
        $inputTitle       = $validated['title'] ?? null;
        $inputBirthday    = $validated['birthday'] ?? null;
        $inputGender      = $validated['gender'] ?? null;
        $inputNationality = $validated['nationality'] ?? null;
        $inputOccupation  = $validated['occupation'] ?? null;
        $inputCompany     = $validated['company_name'] ?? null;
        $inputAddress     = $validated['home_address'] ?? null;
        $inputEmergName   = $validated['emergency_name'] ?? null;
        $inputEmergContact = $validated['emergency_contact'] ?? null;

        // =================================================================
        // 2. GUEST RESOLUTION (FIND OR PREPARE)
        // =================================================================

        $guest = null;

        // A) Check Secure Session (Returning Guest)
        if (session()->has('returning_guest')) {
            $guest = Guest::find(session('returning_guest')['id']);

            if ($guest) {
                // FALLBACK: If form fields were hidden, use existing DB values
                if (empty($inputPhone))       $inputPhone       = $guest->contact_number;
                if (empty($inputName))        $inputName        = $guest->full_name;
                if (empty($inputEmail))       $inputEmail       = $guest->email;
                if (empty($inputTitle))       $inputTitle       = $guest->title;
                if (empty($inputBirthday))    $inputBirthday    = $guest->birthday;
                if (empty($inputGender))      $inputGender      = $guest->gender;
                if (empty($inputNationality)) $inputNationality = $guest->nationality;
                if (empty($inputOccupation))  $inputOccupation  = $guest->occupation;
                if (empty($inputCompany))     $inputCompany     = $guest->company_name;
                if (empty($inputAddress))     $inputAddress     = $guest->home_address;
                if (empty($inputEmergName))   $inputEmergName   = $guest->emergency_name;
                if (empty($inputEmergContact)) $inputEmergContact = $guest->emergency_contact;
            }
        }

        // B) Search by Phone (New Guest fallback or Session Expired)
        if (!$guest && $inputPhone) {
            $guest = Guest::where('contact_number', $inputPhone)->first();
        }

        // =================================================================
        // 3. PERSISTENCE (UPDATE OR CREATE GUEST)
        // =================================================================

        if ($guest) {
            // === RETURNING GUEST UPDATE ===

            // Check for Email Conflict if email is changing
            if (!empty($inputEmail) && $inputEmail !== $guest->email) {
                $emailTaken = Guest::where('email', $inputEmail)
                    ->where('id', '!=', $guest->id)
                    ->exists();

                // Only update email if not taken
                if (!$emailTaken) {
                    $guest->email = $inputEmail;
                }
            }

            // Update profile with resolved values (Merged Input + DB Fallback)
            $guest->update([
                'title'             => $inputTitle,
                'full_name'         => $inputName,
                'nationality'       => $inputNationality,
                'birthday'          => $inputBirthday,
                'gender'            => $inputGender,
                'occupation'        => $inputOccupation,
                'company_name'      => $inputCompany,
                'home_address'      => $inputAddress,
                'emergency_name'    => $inputEmergName,
                'emergency_contact' => $inputEmergContact,
            ]);
        } else {
            // === NEW GUEST CREATE ===

            // Explicit Email Conflict Check for new records
            if (!empty($inputEmail)) {
                if (Guest::where('email', $inputEmail)->exists()) {
                    return back()->withInput()->withErrors([
                        'email' => 'This email address is already registered to another guest profile.'
                    ]);
                }
            }

            $guest = Guest::create([
                'title'             => $inputTitle,
                'full_name'         => $inputName,
                'contact_number'    => $inputPhone,
                'email'             => $inputEmail,
                'birthday'          => $inputBirthday,
                'gender'            => $inputGender,
                'nationality'       => $inputNationality,
                'occupation'        => $inputOccupation,
                'company_name'      => $inputCompany,
                'home_address'      => $inputAddress,
                'emergency_name'    => $inputEmergName,
                'emergency_contact' => $inputEmergContact,
            ]);
        }

        // =================================================================
        // 4. REGISTRATION SNAPSHOT
        // =================================================================

        $registrationData = [
            'guest_id'          => $guest->id,
            'stay_status'       => 'draft_by_guest',
            // Snapshot all current resolved data
            'title'             => $guest->title,
            'full_name'         => $guest->full_name,
            'contact_number'    => $guest->contact_number,
            'email'             => $guest->email,
            'nationality'       => $guest->nationality,
            'birthday'          => $guest->birthday,
            'gender'            => $guest->gender,
            'occupation'        => $guest->occupation,
            'company_name'      => $guest->company_name,
            'home_address'      => $guest->home_address,
            'emergency_name'    => $guest->emergency_name,
            'emergency_relationship' => null, // Add if you have this field in form
            'emergency_contact' => $guest->emergency_contact,

            // Stay Specifics
            'check_in'          => $validated['check_in'],
            'check_out'         => $validated['check_out'],
            'no_of_guests'      => $validated['no_of_guests'],
            'is_group_lead'     => $request->boolean('is_group_lead'),
            'agreed_to_policies' => true,
            'opt_in_data_save'  => $request->boolean('opt_in_data_save'),
        ];

        // Handle Signature
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

        // =================================================================
        // 5. GROUP MEMBERS PROCESSING
        // =================================================================

        if ($request->boolean('is_group_lead') && !empty($validated['group_members'])) {
            foreach ($validated['group_members'] as $memberData) {

                $memberPhone = $normalizePhone($memberData['contact_number'] ?? null);
                $memberGuestId = null;

                // Create/Update basic guest profile for member if phone provided
                if ($memberPhone) {
                    $memberGuest = Guest::firstOrCreate(
                        ['contact_number' => $memberPhone],
                        [
                            'full_name' => $memberData['full_name'],
                            'email'     => $memberData['email'] ?? null,
                        ]
                    );

                    // Always ensure name is up to date
                    if ($memberGuest->full_name !== $memberData['full_name']) {
                        $memberGuest->update(['full_name' => $memberData['full_name']]);
                    }
                    $memberGuestId = $memberGuest->id;
                }

                Registration::create([
                    'parent_registration_id' => $registration->id,
                    'guest_id'       => $memberGuestId,
                    'full_name'      => $memberData['full_name'],
                    'contact_number' => $memberPhone,
                    'email'          => $memberData['email'] ?? null,
                    'check_in'       => $registration->check_in,
                    'check_out'      => $registration->check_out,
                    'stay_status'    => 'draft_by_guest',
                ]);
            }
        }

        // Clean up session
        session()->forget('returning_guest');

        return redirect()->route('frontdesk.registrations.thank-you');
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
        $query = Registration::with('guest')
            ->whereNull('parent_registration_id'); // Only show group leads/individuals

        // 1. Search Filter (Name, Contact, Email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                // Search in Registration Snapshot
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    // Search in Linked Guest Profile (for email or updated info)
                    ->orWhereHas('guest', function ($guestQ) use ($search) {
                        $guestQ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('contact_number', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status Filter
        if ($request->filled('status')) {
            $query->where('stay_status', $request->input('status'));
        }

        // 3. Date Filter (Optional but helpful: Filter by Check-in date)
        if ($request->filled('date')) {
            $query->whereDate('check_in', $request->input('date'));
        }

        $registrations = $query->latest()
            ->paginate(15)
            ->appends($request->all()); // Keep search params in pagination links

        return view('frontdeskcrm::registrations.index', compact('registrations'));
    }
    // --- NEW "WALK-IN" FEATURE (Scenario 3) ---

    /**
     * Show the agent a simple form to create a new walk-in guest.
     */
    public function createWalkin()
    {
        // This view would be a simplified version of 'create.blade.php'
        // For now, we will re-use the 'create' view but with a flag.
        return view('frontdeskcrm::registrations.create-walkin');
        // We need to create this new view file
    }

    /**
     * AJAX Lookup for Walk-in form.
     * Finds a guest by phone number to auto-fill the agent's form.
     */
    public function lookupGuest(Request $request)
    {
        $phone = $request->query('phone');

        if (!$phone) {
            return response()->json(['found' => false]);
        }

        // 1. Normalize Phone (Same logic as store)
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $phone)) {
            $phone = '+234' . substr($phone, 1);
        }

        // 2. Search
        $guest = Guest::where('contact_number', $phone)->first();

        if ($guest) {
            return response()->json([
                'found' => true,
                'guest' => [
                    'full_name' => $guest->full_name,
                    'email' => $guest->email,
                    'gender' => $guest->gender,
                    // Add any other fields you want to auto-fill
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }
    /**
     * Store the new walk-in guest and registration.
     */
    public function storeWalkin(Request $request)
    {
        // 1. Validate Input
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:100', new ValidPhoneNumber],
            'email' => ['nullable', 'email', new ValidEmail],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after_or_equal:check_in'],
        ]);

        // 2. Normalize Phone Number
        $phone = $validated['contact_number'];
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $phone)) {
            $phone = '+234' . substr($phone, 1);
        }
        $validated['contact_number'] = $phone;

        // 3. Search for Guest
        $guest = Guest::where('contact_number', $validated['contact_number'])->first();
        $message = '';

        if ($guest) {
            // === RETURNING GUEST ===

            // Check for Email Conflict
            if (!empty($validated['email']) && $validated['email'] !== $guest->email) {
                $emailExists = Guest::where('email', $validated['email'])
                    ->where('id', '!=', $guest->id)
                    ->exists();

                if ($emailExists) {
                    // FIX: Throw Exception to ensure error is displayed
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => ['This email address is already registered to a different guest profile.'],
                    ]);
                }
                $guest->email = $validated['email'];
            }

            // Update details
            $guest->full_name = $validated['full_name'];
            $guest->gender = $validated['gender'] ?? $guest->gender;
            $guest->save();

            $message = "Welcome back, {$guest->full_name}! Registration created.";
        } else {
            // === NEW GUEST ===

            if (!empty($validated['email'])) {
                if (Guest::where('email', $validated['email'])->exists()) {
                    // FIX: Throw Exception to ensure error is displayed
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => ['This email address is already in use by another guest.'],
                    ]);
                }
            }

            $guest = Guest::create([
                'full_name' => $validated['full_name'],
                'contact_number' => $validated['contact_number'],
                'gender' => $validated['gender'] ?? null,
                'email' => $validated['email'] ?? null,
                'title' => '',
            ]);

            $message = "New guest profile created.";
        }

        // 4. Create Registration Snapshot
        $registration = Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $validated['full_name'],
            'contact_number' => $validated['contact_number'],
            'gender' => $validated['gender'] ?? null,
            'email' => $validated['email'] ?? $guest->email,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'stay_status' => 'draft_by_guest',
            'no_of_guests' => 1,
            'agreed_to_policies' => true,
        ]);

        return redirect()->route('frontdesk.registrations.finalize.form', $registration)
            ->with('success', $message);
    }
    /**
     * Show the form for an agent to finalize a draft.
     * UPDATED: Now fetches Rooms from the Website module.
     */
    public function showFinalizeForm(Registration $registration)
    {
        if ($registration->stay_status !== 'draft_by_guest') {
            return redirect()->route('frontdesk.registrations.show', $registration)
                ->with('error', 'This registration has already been finalized.');
        }

        $groupMembers = Registration::where('parent_registration_id', $registration->id)->get();
        $bookingSources = BookingSource::where('is_active', true)->get();
        $guestTypes = GuestType::where('is_active', true)->get();

        // --- NEW: Fetch Real Rooms for the Dropdown ---
        $rooms = Room::orderBy('name')->get();

        return view('frontdeskcrm::registrations.finalize', compact(
            'registration',
            'groupMembers',
            'bookingSources',
            'guestTypes',
            'rooms' // <--- Passed to view
        ));
    }
    /**
     * Process the agent's finalization.
     * UPDATED: Validates using Room ID (ERP Logic).
     */
    public function finalize(FinalizeRegistrationRequest $request, Registration $registration)
    {
        $validated = $request->validated();
        $nights = $registration->check_in->diffInDays($registration->check_out);
        $billingType = $request->input('billing_type', 'consolidate');

        // =========================================================
        // 1. ERP AVAILABILITY CHECK (Using Room ID)
        // =========================================================

        // Helper to check if a specific Room ID is occupied
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

        // A) Check Lead Room
        // We use 'room_id' from request, falling back to null if they entered manual text
        $leadRoomId = $request->input('room_id');

        if ($leadRoomId && $checkAvailability($leadRoomId, $registration->check_in, $registration->check_out, $registration->id)) {
            $roomName = Room::find($leadRoomId)?->name ?? 'Selected Room';
            return back()->withInput()->withErrors([
                'room_id' => "$roomName is already occupied for these dates."
            ]);
        }

        // B) Check Member Rooms
        if (isset($validated['group_members'])) {
            foreach ($validated['group_members'] as $id => $data) {
                if (($data['status'] ?? '') === 'no_show') continue;

                $memberRoomId = $data['room_id'] ?? null;

                if ($memberRoomId && $checkAvailability($memberRoomId, $registration->check_in, $registration->check_out, $id)) {
                    $roomName = Room::find($memberRoomId)?->name ?? 'Selected Room';
                    return back()->withInput()->withErrors([
                        "group_members.{$id}.room_id" => "$roomName (Member Room) is already occupied."
                    ]);
                }
            }
        }

        // =========================================================
        // 2. PROCESSING & SAVING
        // =========================================================

        // --- Process Group Members ---
        $membersTotalBill = 0;

        if (isset($validated['group_members'])) {
            foreach ($validated['group_members'] as $id => $data) {
                $memberRegistration = Registration::find($id);
                if (!$memberRegistration || $memberRegistration->parent_registration_id !== $registration->id) {
                    continue;
                }

                if (($data['status'] ?? '') === 'no_show') {
                    $memberRegistration->update([
                        'stay_status' => 'no_show',
                        'total_amount' => 0,
                        'room_id' => null,
                        'room_allocation' => null,
                        'room_rate' => 0,
                    ]);
                    continue;
                }

                $memberTotal = $data['room_rate'] * $nights;
                $memberRegistration->update([
                    'room_id' => $data['room_id'] ?? null, // <--- Save ID
                    'room_allocation' => $data['room_allocation'] ?? null, // Save text name as backup
                    'room_rate' => $data['room_rate'],
                    'bed_breakfast' => isset($data['bed_breakfast']),
                    'stay_status' => 'checked_in',
                    'no_of_nights' => $nights,
                    'total_amount' => $memberTotal,
                    'finalized_by_agent_id' => Auth::id(),
                    'guest_type_id' => $validated['guest_type_id'],
                    'booking_source_id' => $validated['booking_source_id'],
                ]);

                if ($memberRegistration->stay_status === 'checked_in') {
                    $membersTotalBill += $memberTotal;
                }
            }
        }

        // --- Process Group Lead ---
        $leadRate = $validated['room_rate'];
        $leadPersonalBill = $leadRate * $nights;
        $finalLeadTotal = $leadPersonalBill;

        if ($billingType === 'consolidate') {
            $finalLeadTotal += $membersTotalBill;
        }

        $registration->update([
            'room_id' => $request->input('room_id'), // <--- Save ID
            'room_allocation' => $validated['room_allocation'], // Keep text for now
            'room_rate' => $leadRate,
            'bed_breakfast' => $request->boolean('bed_breakfast'),
            'guest_type_id' => $validated['guest_type_id'],
            'booking_source_id' => $validated['booking_source_id'],
            'payment_method' => $validated['payment_method'],
            'billing_type' => $billingType,
            'stay_status' => 'checked_in',
            'no_of_nights' => $nights,
            'total_amount' => $finalLeadTotal,
            'finalized_by_agent_id' => Auth::id(),
        ]);
        // =========================================================
        // 3. PARENT BILL SYNC (For Late Arrivals)
        // =========================================================
        // If we just finalized a Child, and the group uses Consolidated Billing,
        // we must update the Parent's total amount to include this new person.

        if ($registration->parent_registration_id) {
            $parent = $registration->parent;

            // Recalculate Parent's Total (Lead Personal + All Children)
            if ($parent && $parent->billing_type === 'consolidate') {
                $leadPersonalBill = $parent->room_rate * $parent->no_of_nights;
                $allChildrenBill = $parent->children()->where('stay_status', 'checked_in')->sum('total_amount');

                $parent->update([
                    'total_amount' => $leadPersonalBill + $allChildrenBill
                ]);
            }
        }
        return redirect()->route('frontdesk.registrations.show', $registration)
            ->with('success', 'Check-in finalized successfully!');
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
        if ($registration->stay_status !== 'draft_by_guest') {
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
}
