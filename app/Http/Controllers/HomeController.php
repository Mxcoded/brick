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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Check if the user has the 'admin' role
        if ($user->hasRole('admin')) {
            Log::info('Redirecting to admin dashboard for user ID: ' . $user->id);
            return redirect()->route('admin.dashboard');
        }
        // Check if the user has the 'staff' role
        elseif ($user->hasRole('staff')) {
            Log::info('Redirecting to staff dashboard for user ID: ' . $user->id);
            return redirect()->route('staff.dashboard');
        }
        // Handle users without 'admin' or 'staff' roles
        else {
            Log::warning('User ID: ' . $user->id . ' has no admin or staff role.');
            // Option 1: Redirect to a default dashboard with an error message
            return redirect()->route('default.dashboard')
                ->with('error', 'You do not have access to any dashboard.');
            // Option 2: Return the home view (uncomment if preferred)
            // return view('home');
        }
    }
}
