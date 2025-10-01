<?php

namespace Modules\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Website\Models\GuestProfile;
use Modules\Website\Models\Booking;

class GuestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guest']);
    }
    public function index()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)->get(); // Fetch the guest's bookings
        return view('website::guest.dashboard', compact('bookings'));
    }
    /**
     * Display the guest's bookings.
     */
    public function bookings()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)->get();
        return view('website::guest.bookings', compact('bookings'));
    }
    public function claimBooking(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            // 'booking_ref_number' => 'required|string|exists:bookings,booking_ref_number',
            'booking_id' => 'required|exists:bookings,id',
            'guest_email' => 'required|email',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Find the booking with matching ID, email, and no user_id
        $booking = Booking::where('id', $validated['booking_id'])
            ->where('guest_email', $validated['guest_email'])
            ->whereNull('user_id')
            ->first();
        // Booking::where('booking_ref_number', $validated['booking_ref_number'])
        //     ->where('guest_email', $validated['guest_email'])
        //     ->whereNull('user_id')
        //     ->first();

        if ($booking) {
            // Check if the logged-in user's email matches the booking's guest_email
            if ($user->email === $validated['guest_email']) {
                $booking->update([
                    'user_id' => $user->id,
                    'confirmation_token' => null,
                ]);
                return redirect()->route('website.guest.dashboard')
                    ->with('success', 'Booking linked to your account.');
            } else {
                return redirect()->route('website.guest.dashboard')
                    ->with('error', 'You can only claim bookings made with your email address.');
            }
        }

        return redirect()->route('website.guest.dashboard')
            ->with('error', 'Booking not found or already claimed.');
    }
    /**
     * Display the guest's profile.
     */
    public function profile()
    {
        $user = Auth::user();
        $profile = $user->guestProfile ?? new GuestProfile(['user_id' => $user->id]);

        return view('website::guest.profile', compact('profile'));
    }

    /**
     * Update the guest's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'preferences' => 'nullable|array',
        ]);

        $profile = $user->guestProfile;

        if ($profile) {
            $profile->update($validated);
        } else {
            $validated['user_id'] = $user->id;
            GuestProfile::create($validated);
        }

        return redirect()->route('website.guest.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
