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
