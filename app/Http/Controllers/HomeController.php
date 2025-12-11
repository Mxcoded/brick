<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\RoleEnum;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * Actually, we just redirect to the correct Module Dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Super Admin & Admin
        if ($user->hasRole([RoleEnum::SUPER_ADMIN->value, RoleEnum::ADMIN->value])) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Staff
        if ($user->hasRole(RoleEnum::STAFF->value)) {
            return redirect()->route('staff.dashboard');
        }

        // 3. Guest (Default)
        return redirect()->route('website.guest.dashboard');
    }
}
