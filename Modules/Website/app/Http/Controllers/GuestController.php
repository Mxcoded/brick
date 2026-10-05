<?php

namespace Modules\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Rules\ValidPhoneNumber;
use Modules\Frontdeskcrm\Support\GuestGender;
use Modules\Frontdeskcrm\Support\GuestIdentificationType;
use Modules\Frontdeskcrm\Support\GuestNationality;
use Modules\Frontdeskcrm\Support\GuestTitle;
use Modules\Restaurant\Support\RoomServiceAccess;
use Modules\Website\Models\Booking;

class GuestController extends Controller
{
    /**
     * Show Guest Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // 1. Stats for the top cards
        $activeBookingsCount = Booking::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_out_date', '>=', now())
            ->count();

        $pendingPaymentCount = Booking::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->count();

        $totalSpent = Booking::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // 2. Recent Activity (Last 3 bookings)
        $recentBookings = Booking::where('user_id', $user->id)
            ->with('room')
            ->latest()
            ->take(3)
            ->get();

        // 3. Room service. The room is derived from the guest's own checked-in
        // stay, never chosen by them, so the dashboard can only ever point at
        // the room they are actually occupying. Guarded on the Restaurant
        // module being present, since it is not a dependency of this one.
        $roomServiceRoom = null;

        if (class_exists(RoomServiceAccess::class)) {
            $roomServiceRoom = app(RoomServiceAccess::class)->roomFor($user);
        }

        return view('website::guest.dashboard', compact(
            'user',
            'activeBookingsCount',
            'pendingPaymentCount',
            'totalSpent',
            'recentBookings',
            'roomServiceRoom'
        ));
    }

    /**
     * Show All Bookings (History)
     */
    public function bookings()
    {
        $user = Auth::user();

        // Fetch bookings with Room details, ordered by latest first
        $bookings = Booking::where('user_id', $user->id)
            ->with('room')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('website::guest.bookings', compact('bookings'));
    }

    /**
     * Allow Guest to Cancel a Booking
     */
    public function cancelBooking($id)
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        // Validation: Cannot cancel past bookings or already cancelled ones
        if ($booking->check_in_date < now()->toDateString()) {
            return back()->with('error', 'You cannot cancel a past booking.');
        }

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'This booking is already cancelled.');
        }

        // Logic: If Paid, maybe refund needed (Manual for now)
        // If Pending, just cancel.

        $booking->update(['status' => 'cancelled']);

        $booking->sendNotification('Booking Cancelled');

        return back()->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Show Profile Form
     */
    public function profile()
    {
        $user = Auth::user();
        // Find CRM Guest by User ID or Email
        $profile = Guest::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        return view('website::guest.profile', compact('user', 'profile'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // 1. Validate ALL fields
        $validated = $request->validate([
            // Core User
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,

            // Personal
            'title' => ['nullable', Rule::in([...GuestTitle::values(), ...GuestTitle::acceptedValues()])],
            // Both spellings accepted: the profile form offers Title case but
            // guests booked through the public site hold lowercase, and
            // re-saving such a profile must not wipe the value.
            'gender' => ['nullable', Rule::in([
                ...GuestGender::registrationValues(),
                ...array_keys(GuestGender::options()),
            ])],
            'birthday' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:100',

            // Contact
            'contact_number' => ['nullable', 'string', 'max:20', new ValidPhoneNumber],
            'home_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',

            // Emergency
            'emergency_name' => 'nullable|string|max:100',
            'emergency_relationship' => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:20',

            // Identification
            // Free text is still accepted: a guest may hold a document this code has no
            // name for, and rejecting it would lose the record. A recognised
            // spelling is reduced to the canonical value.
            'identification_type' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:50',
        ]);

        // 2. Update Login Data
        $user->update([
            'name' => $validated['full_name'],
            'email' => $validated['email'],
        ]);

        // 3. Update/Create CRM Guest Data (Centralized)
        Guest::updateOrCreate(
            ['email' => $user->email], // Match by email
            [
                'user_id' => $user->id,
                'title' => GuestTitle::normalise($validated['title'] ?? null),
                'full_name' => $validated['full_name'],
                'nationality' => GuestNationality::normalise($validated['nationality'] ?? null),
                'zip_code' => $validated['zip_code'],
                'identification_type' => GuestIdentificationType::normalise($validated['identification_type'] ?? null)
                    ?? ($validated['identification_type'] ?? null),
                'identification_number' => $validated['identification_number'],
                'contact_number' => $validated['contact_number'],
                'birthday' => $validated['birthday'],
                'gender' => GuestGender::normalise($validated['gender'] ?? null),
                'occupation' => $validated['occupation'],
                'company_name' => $validated['company_name'],
                'home_address' => $validated['home_address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'emergency_name' => $validated['emergency_name'],
                'emergency_relationship' => $validated['emergency_relationship'],
                'emergency_contact' => $validated['emergency_contact'],
                // 'opt_in_data_save' => true // Default behavior
            ]
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Helper to sync Website User -> CRM Guest
     */
    protected function syncToCrmGuest($user, $profile)
    {
        // Try to find CRM guest by email or phone
        Guest::updateOrCreate(
            ['email' => $user->email],
            [
                'full_name' => $user->name,
                'contact_number' => $profile->phone ?? 'N/A',
                'home_address' => $profile->address.' '.$profile->city,
                'nationality' => $profile->country,
                // Map other fields as needed
            ]
        );
    }
}
