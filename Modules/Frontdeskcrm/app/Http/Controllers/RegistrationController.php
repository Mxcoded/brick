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

class RegistrationController extends Controller
{
    // =====================================================================
    // GUEST-FACING CHECK-IN FLOW
    // =====================================================================

    /**
     * Show the initial guest check-in form (search box).
     * This is the public starting point.
     */
    public function create()
    {
        return view('frontdeskcrm::registrations.create');
    }

    /**
     * Handle the initial search from the guest.
     * Finds a guest and reloads the create form with pre-filled data.
     */
    public function handleGuestSearch(Request $request)
    {
        $request->validate([
            'search_query' => 'required|string|max:255',
        ]);

        $query = $request->input('search_query');

        // Search for a guest by email or contact number
        $guest = Guest::where('email', $query)
            ->orWhere('contact_number', $query)
            ->first();

        if ($guest) {
            // dd($guest->toArray());
            // Guest found, flash their data to the session and redirect back
            return redirect()->route('frontdesk.registrations.create')
                ->with('guest_data', $guest->toArray())
                ->withInput(); // Also flash the search query
                dd ('$guest_data');
        } else {
            // No guest found, just flash the search query back
            return redirect()->route('frontdesk.registrations.create')
                ->with('search_query', $query)
                ->with('stay_status', 'No guest found. Please fill out the form to register.')
                ->withInput();
        }
    }

    /**
     * Store the guest's submitted draft registration.
     * This is where the two-stage process begins.
     */
    public function store(StoreRegistrationRequest $request)
    {
        // 1. Find or Create the Guest Profile for the LEAD GUEST
        $guest = Guest::firstOrCreate(
            ['contact_number' => $request->contact_number],
            $request->only([
                'title',
                'full_name',
                'nationality',
                'birthday',
                'email',
                'gender',
                'occupation',
                'company_name',
                'home_address',
                'emergency_name',
                'emergency_relationship',
                'emergency_contact'
            ])
        );

        // If the guest was found and details were blank, update them now
        if ($guest->wasRecentlyCreated === false) {
            $guest->update($request->only([
                'title',
                'full_name',
                'nationality',
                'birthday',
                'email',
                'gender',
                'occupation',
                'company_name',
                'home_address',
                'emergency_name',
                'emergency_relationship',
                'emergency_contact'
            ]));
        }

        // 2. Create the Draft Registration Record for the LEAD GUEST
        $registrationData = $request->validated();
        $registrationData['guest_id'] = $guest->id;
        $registrationData['stay_status'] = 'draft_by_guest';

        // Handle the signature image
        if ($request->has('guest_signature')) {
            $signatureImage = $request->input('guest_signature');
            $signatureImage = str_replace('data:image/png;base64,', '', $signatureImage);
            $signatureImage = str_replace(' ', '+', $signatureImage);
            $imageName = 'signatures/' . uniqid() . '.png';
            Storage::disk('public')->put($imageName, base64_decode($signatureImage));
            $registrationData['guest_signature'] = $imageName;
        }

        // Create the registration
        $registration = Registration::create($registrationData);

        // 3. Handle Group Members
        if ($request->boolean('is_group_lead') && $request->has('group_members')) {
            foreach ($request->group_members as $memberData) {

                // --- START OF THE FIX ---
                // This block solves Problem 1 and Problem 2

                $memberGuestId = null;

                // Only search/create a guest if a contact number is provided
                // This matches the DTO validation ('contact_number' => ['nullable', 'string'])
                if (!empty($memberData['contact_number'])) {
                    $memberGuest = Guest::firstOrCreate(
                        ['contact_number' => $memberData['contact_number']],
                        [
                            'full_name' => $memberData['full_name'],
                            // Add email if you ever add it to the group form
                            // 'email' => $memberData['email'] ?? null 
                        ]
                    );
                    $memberGuestId = $memberGuest->id;
                }

                // --- END OF THE FIX ---

                // Create the child registration, now with the correct guest_id
                Registration::create([
                    'full_name' => $memberData['full_name'],
                    'contact_number' => $memberData['contact_number'],
                    'guest_id' => $memberGuestId, // <-- THE FIX IS APPLIED HERE
                    'parent_registration_id' => $registration->id,
                    'stay_status' => 'draft_by_guest', // Members are also drafts
                    'check_in' => $registration->check_in,
                    'check_out' => $registration->check_out,
                ]);
            }
        }

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
     * Display the agent's dashboard of all registrations.
     */
    public function index()
    {
        $registrations = Registration::with('guest')
            ->whereNull('parent_registration_id') // Only show group leads/individuals
            ->latest()
            ->paginate(15);
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
     * Store the new walk-in guest and registration.
     * This bypasses the signature/policy steps and immediately creates a draft.
     */
    public function storeWalkin(Request $request)
    {
        // Use a simpler validation for walk-ins
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:100', new ValidPhoneNumber],
            'email' => ['nullable', 'email', new ValidEmail],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after_or_equal:check_in'],
        ]);

        // Find or Create the Guest Profile
        $guest = Guest::firstOrCreate(
            ['contact_number' => $validated['contact_number']],
            [
                'full_name' => $validated['full_name'],
                'email' => $validated['email'] ?? null,
            ]
        );

        // Create the Draft Registration Record
        $registration = Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'stay_status' => 'draft_by_guest', // <-- Set as draft
            'no_of_guests' => 1,
            'agreed_to_policies' => true, // Agent is responsible
        ]);

        // Redirect to the finalize form to assign a room and rate
        return redirect()->route('frontdesk.registrations.finalize.form', $registration)
            ->with('success', 'Walk-in guest created. Please finalize the registration.');
    }
    /**
     * Show the form for an agent to finalize a draft.
     */
    public function showFinalizeForm(Registration $registration)
    {
        // dd($registration->stay_status);
        if ($registration->stay_status !== 'draft_by_guest') {
            return redirect()->route('frontdesk.registrations.show', $registration)
                ->with('error', 'This registration has already been finalized.');
        }

        $groupMembers = Registration::where('parent_registration_id', $registration->id)->get();
        $bookingSources = BookingSource::where('is_active', true)->get();
        $guestTypes = GuestType::where('is_active', true)->get();

        return view('frontdeskcrm::registrations.finalize', compact('registration', 'groupMembers', 'bookingSources', 'guestTypes'));
    }
    /**
     * Process the agent's finalization form submission for individuals or groups.
     */
    public function finalize(FinalizeRegistrationRequest $request, Registration $registration)
    {
        // Use the validated data from your Form Request class
        $validated = $request->validated();
        $nights = $registration->check_in->diffInDays($registration->check_out);
        $billingType = $request->input('billing_type', 'consolidate');

        // --- 1. Process Group Members ---
        if (isset($validated['group_members'])) {
            foreach ($validated['group_members'] as $id => $data) {
                $memberRegistration = Registration::find($id);
                if (!$memberRegistration || $memberRegistration->parent_registration_id !== $registration->id) {
                    continue; // Skip if member not found or doesn't belong to this group
                }

                // A) Handle No-Show Members
                if ($data['status'] === 'no_show') {
                    $memberRegistration->update([
                        'stay_status' => 'no_show',
                        'total_amount' => 0,
                        'room_allocation' => null,
                        'room_rate' => 0,
                    ]);
                    continue; // Move to the next member
                }

                // B) Handle Checked-in Members
                $memberTotal = $data['room_rate'] * $nights;
                $memberRegistration->update([
                    'room_allocation' => $data['room_allocation'],
                    'room_rate' => $data['room_rate'],
                    'bed_breakfast' => isset($data['bed_breakfast']),
                    'stay_status' => 'checked_in',
                    'no_of_nights' => $nights,
                    'total_amount' => $memberTotal,
                    'finalized_by_agent_id' => Auth::id(),
                    'guest_type_id' => $validated['guest_type_id'],
                    'booking_source_id' => $validated['booking_source_id'],
                ]);
            }
        }

        // --- 2. Process Group Lead ---
        $leadRate = $validated['room_rate'];
        $leadPersonalBill = $leadRate * $nights;
        $finalLeadTotal = $leadPersonalBill;

        // --- 3. Apply Billing Logic ---
        if ($billingType === 'consolidate') {
            // Add the sum of all *checked-in* children's bills to the lead's bill
            $membersTotalBill = $registration->children()->where('stay_status', 'checked_in')->sum('total_amount');
            $finalLeadTotal += $membersTotalBill;
        }
        // If billingType is 'individual', the lead's total is just their personal bill.

        $registration->update([
            'room_allocation' => $validated['room_allocation'],
            'room_rate' => $leadRate,
            'bed_breakfast' => $request->boolean('bed_breakfast'),
            'guest_type_id' => $validated['guest_type_id'],
            'booking_source_id' => $validated['booking_source_id'],
            'payment_method' => $validated['payment_method'],
            'billing_type' => $billingType, // Save the billing choice
            'stay_status' => 'checked_in',
            'no_of_nights' => $nights,
            'total_amount' => $finalLeadTotal, // The final calculated total
            'finalized_by_agent_id' => Auth::id(),
        ]);

        return redirect()->route('frontdesk.registrations.show', $registration)
            ->with('success', 'Group check-in has been successfully finalized!');
    }
    // --- NEW "NO-SHOW" FIX (The Gap) ---

    /**
     * Re-opens a 'no_show' or 'checked_out' registration to be finalized again.
     */
    public function reopen(Registration $registration)
    {
        // Only allow reopening for 'no-show' or 'checked_out'
        if ($registration->stay_status !== 'no_show' && $registration->stay_status !== 'checked_out') {
            return back()->with('error', 'Only no-show or checked-out guests can be re-opened.');
        }

        // If it's a child, re-open the parent instead
        if ($registration->parent_registration_id) {
            $registration = $registration->parent;
        }

        // Reset all children (group members) to 'draft_by_guest'
        $registration->children()->update([
            'stay_status' => 'draft_by_guest',
        ]);

        // Reset the parent to 'draft_by_guest'
        $registration->update([
            'stay_status' => 'draft_by_guest',
        ]);

        return redirect()->route('frontdesk.registrations.finalize.form', $registration)
            ->with('success', 'Registration has been re-opened. Please finalize it again.');
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
     */
    public function checkout(Registration $registration)
    {
        if ($registration->stay_status !== 'checked_in') {
            return back()->with('error', 'This guest is not currently checked-in.');
        }
        // --- Increment Visit Count for the Lead Guest ---
        $guest = $registration->guest;
        if ($guest && $registration->stay_status === 'checked_in') {
            $guest->increment('visit_count');
            $guest->last_visit_at = now();
            $guest->save();
        }
        $registration->update(['stay_status' => 'checked_out']);

        $message = "Guest {$registration->full_name} has been successfully checked out.";

        // If a member was checked out, redirect back to the group lead's page.
        if ($registration->parent_registration_id) {
            return redirect()->route('frontdesk.registrations.show', $registration->parent_registration_id)
                ->with('success', $message);
        }

        // If the lead was checked out, redirect to the index page.
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
            'members_to_extend.*' => 'exists:registrations,id', // Ensure all provided IDs are valid registrations
        ]);

        $newCheckOut = Carbon::parse($validated['new_check_out']);

        // Prevent unnecessary database writes if the date hasn't changed.
        if ($newCheckOut->isSameDay($registration->check_out)) {
            return back()->with('info', 'The new check-out date is the same as the current one. No changes were made.');
        }

        // 2. UPDATE THE PRIMARY REGISTRATION (THE ONE CLICKED BY THE AGENT)
        $nights = $registration->check_in->diffInDays($newCheckOut);
        $registration->update([
            'check_out' => $newCheckOut,
            'no_of_nights' => $nights,
            'total_amount' => $registration->room_rate * $nights,
        ]);

        // 3. HANDLE GROUP MEMBER EXTENSIONS (IF APPLICABLE)
        // This block only runs if the agent is adjusting the group lead and selected members.
        if ($registration->is_group_lead && isset($validated['members_to_extend'])) {
            $memberIds = $validated['members_to_extend'];

            // Ensure we only update members that actually belong to this group lead for security.
            $membersToUpdate = Registration::whereIn('id', $memberIds)
                ->where('parent_registration_id', $registration->id)
                ->get();

            foreach ($membersToUpdate as $member) {
                $memberNights = $member->check_in->diffInDays($newCheckOut);
                $member->update([
                    'check_out' => $newCheckOut,
                    'no_of_nights' => $memberNights,
                    'total_amount' => $member->room_rate * $memberNights,
                ]);
            }
        }

        // 4. FIND THE GROUP LEAD AND RECALCULATE THE ENTIRE GROUP'S BILL
        // This ensures data integrity for ALL scenarios (individual, member, or group adjust).
        $leadRegistration = null;
        if ($registration->is_group_lead) {
            $leadRegistration = $registration;
        } elseif ($registration->parent_registration_id) {
            $leadRegistration = $registration->parent; // Using the defined relationship
        }

        if ($leadRegistration) {
            // Recalculate the lead's personal bill based on its own nights and rate.
            $leadPersonalBill = $leadRegistration->room_rate * $leadRegistration->no_of_nights;

            // Sum the total amounts of all children registrations.
            $membersTotalBill = $leadRegistration->children()->sum('total_amount');

            // The lead's new total amount is their bill plus all their members' bills.
            $leadRegistration->update([
                'total_amount' => $leadPersonalBill + $membersTotalBill
            ]);
        }

        return back()->with('success', 'Stay details have been successfully updated.');
    }
}
