<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show()
    {
        $user = Auth::user();

        return view('profile', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate name and email
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        // Handle password update if provided
        if ($request->filled('new_password')) {
            $request->validate([
                'current_password' => 'required|current_password',
                'new_password' => ['required', 'confirmed', Password::min(8)],
            ]);

            // Logout other devices FIRST, while the user still carries the
            // current hash. logoutOtherDevices() verifies the given password
            // against the in-memory hash, so assigning the new password
            // before this call throws an InvalidArgumentException (500).
            try {
                auth()->logoutOtherDevices($request->current_password);
            } catch (\InvalidArgumentException $e) {
                throw ValidationException::withMessages([
                    'current_password' => 'The current password does not match.',
                ]);
            }

            $user->password = Hash::make($request->new_password);
        }

        // Update name and email
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
