<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreRegistrationRequest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\GuestType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

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
            // Guest found, flash their data to the session and redirect back
            return redirect()->route('frontdesk.registrations.create')
                ->with('guest_data', $guest->toArray())
                ->withInput(); // Also flash the search query
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
        // The StoreRegistrationRequest will handle validation.
        // We assume is_guest_draft is true for this submission.

        // 1. Find or Create the Guest Profile
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

        // 2. Create the Draft Registration Record
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

        // If it's a group lead, handle group members
        if ($request->boolean('is_group_lead') && $request->has('group_members')) {
            foreach ($request->group_members as $memberData) {
                // For simplicity, we create minimal registration records for members
                Registration::create([
                    'full_name' => $memberData['full_name'],
                    'contact_number' => $memberData['contact_number'],
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
    public function finalize(Request $request, Registration $registration)
    {
        $validated = $request->validate([
            // Overall booking details
            'guest_type_id' => 'required|exists:guest_types,id',
            'booking_source_id' => 'required|exists:booking_sources,id',
            'payment_method' => 'required|string|in:cash,pos,transfer',

            // Group Lead's specific details
            'room_allocation' => 'required|string|max:255',
            'room_rate' => 'required|numeric|min:0',
            'bed_breakfast' => 'nullable|boolean',

            // Group Members' specific details
            'group_members' => 'nullable|array',
            'group_members.*.room_allocation' => 'required|string|max:255',
            'group_members.*.room_rate' => 'required|numeric|min:0',
            'group_members.*.bed_breakfast' => 'nullable|boolean',
        ]);

        // --- Increment Visit Count for the Lead Guest ---
        $guest = $registration->guest;
        if ($guest && $registration->stay_status === 'draft_by_guest') {
            $guest->increment('visit_count');
            $guest->last_visit_at = now();
            $guest->save();
        }

        $totalGroupAmount = 0;
        $nights = $registration->check_in->diffInDays($registration->check_out);

        // --- Update Group Members with their individual rates ---
        if ($request->has('group_members')) {
            foreach ($request->group_members as $id => $data) {
                $memberRegistration = Registration::find($id);
                if ($memberRegistration && $memberRegistration->parent_registration_id === $registration->id) {

                    $memberRate = $data['room_rate'];
                    $memberBedBreakfast = isset($data['bed_breakfast']);
                    $memberTotal = $memberRate * $nights;
                    $totalGroupAmount += $memberTotal;

                    $memberRegistration->update([
                        'room_allocation' => $data['room_allocation'],
                        'room_rate' => $memberRate,
                        'bed_breakfast' => $memberBedBreakfast,
                        'guest_type_id' => $validated['guest_type_id'],      // Inherit from lead
                        'booking_source_id' => $validated['booking_source_id'], // Inherit from lead
                        'payment_method' => $validated['payment_method'],   // Inherit from lead
                        'status' => 'checked_in',
                        'no_of_nights' => $nights,
                        'total_amount' => $memberTotal,
                        'finalized_by_agent_id' => Auth::id(),
                    ]);
                }
            }
        }

        // --- Update the Group Lead's Registration ---
        $leadRate = $validated['room_rate'];
        $leadTotal = $leadRate * $nights;
        $totalGroupAmount += $leadTotal;

        $registration->update([
            'room_allocation' => $validated['room_allocation'],
            'room_rate' => $leadRate,
            'bed_breakfast' => $request->boolean('bed_breakfast'),
            'guest_type_id' => $validated['guest_type_id'],
            'booking_source_id' => $validated['booking_source_id'],
            'payment_method' => $validated['payment_method'],
            'stay_status' => 'checked_in',
            'no_of_nights' => $nights,
            // The lead's total amount is now the sum of the entire group's bill.
            'total_amount' => $totalGroupAmount,
            'finalized_by_agent_id' => Auth::id(),
        ]);

        return redirect()->route('frontdesk.registrations.show', $registration)
            ->with('success', 'Group check-in has been successfully finalized!');
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
        $leadBill = ($registration->stay_status === 'checked_in') ? $registration->total_amount : 0;
        $membersBill = $groupMembers->where('stay_status', 'checked_in')->sum('total_amount');

        $groupFinancialSummary = [
            'lead_bill' => $leadBill,
            'members_bill' => $membersBill,
            'total_outstanding' => $leadBill + $membersBill,
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
}
