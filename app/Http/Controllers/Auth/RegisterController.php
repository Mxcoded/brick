<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Enums\RoleEnum;
use Modules\Website\Models\GuestProfile;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     * We direct to /home so HomeController can route them to the Guest Dashboard.
     */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // 1. SECURITY: Force every public signup to be a Guest
        // This prevents anyone from registering as an Admin by manipulating forms
        $user->assignRole(RoleEnum::GUEST->value);

        // 2. DATA: Create the Guest Profile link immediately
        // This prevents "Call to a member function on null" errors in the dashboard
        GuestProfile::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
        ]);

        return $user;
    }
}
