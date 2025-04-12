<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard based on user role.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name'); // Get the collection of role names

        // Log the roles for debugging (optional)
        Log::info('User roles retrieved:', ['user_id' => $user->id, 'roles' => $userRoles->toArray()]);

        // Define role-to-route mappings with priority order
        $roleRoutes = [
            'admin' => 'admin.dashboard',
            'staff' => 'staff.dashboard',
            'guest' => 'website.guest.dashboard', // Added mapping for guest role
        ];

        // Check each role in the mapping against the user's roles
        foreach ($roleRoutes as $role => $route) {
            if ($userRoles->contains($role)) { // Use contains() to check if role exists in collection
                Log::info("Redirecting to {$route} for user ID: " . $user->id);
                // Store the user's roles in the session for other modules
                session(['user_roles' => $userRoles->toArray()]);
                return redirect()->route($route);
            }
        }

        // Fallback for users without recognized roles
        Log::warning('User ID: ' . $user->id . ' has no recognized role.');
        return redirect()->route('default.dashboard')
            ->with('error', 'You do not have access to any dashboard.');
    }
}
