<?php

namespace App\Http\Controllers;

use App\Support\LandingPage;
use Illuminate\Http\Request;

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
     * Send the signed-in user to the landing page for their capabilities.
     *
     * This used to re-derive the destination with its own if-chain, which
     * drifted from the login redirect (it knew nothing about Finance or
     * Restaurant). LandingPage is the single source of truth, so both paths now
     * give the same answer.
     */
    public function index(Request $request)
    {
        return redirect()->route(LandingPage::routeFor($request->user()));
    }
}
