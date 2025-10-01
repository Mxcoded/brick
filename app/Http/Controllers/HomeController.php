<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\EnsureUserRedirection;

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
       $this->middleware(EnsureUserRedirection::class);
    }

    /**
     * Show the application dashboard based on user role.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {

    }
}
