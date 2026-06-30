<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Rules\ValidEmail;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,60');
    }

    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'name' => [
                'required', 'string', 'max:255', 'min:3',
                function ($attribute, $value, $fail) {
                    if (strpos(trim($value), ' ') === false) {
                        $fail('Please enter your full name (first and last name).');
                    }
                    if (! preg_match('/^[\pL\s\'\-.]+$/u', $value)) {
                        $fail('The :attribute contains invalid characters.');
                    }
                },
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', new ValidEmail],
            'contact_number' => ['required', 'string', 'max:191', 'unique:guests,contact_number'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'website' => ['nullable', 'string', 'max:0'],
            'register_time' => ['required', 'integer'],
        ], [
            'website.max' => 'Invalid request.',
            'register_time.required' => 'Invalid request.',
            'register_time.integer' => 'Invalid request.',
        ]);

        if (! empty($data['website'])) {
            $validator->errors()->add('website', 'Invalid request.');
        }

        if (! empty($data['register_time']) && ((int) $data['register_time'] > 0) && (time() - (int) $data['register_time']) < 3) {
            $validator->errors()->add('register_time', 'Please wait a moment before submitting.');
        }

        return $validator;
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'type' => 'guest',
        ]);

        $user->assignRole(RoleEnum::GUEST->value);

        Guest::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'contact_number' => $data['contact_number'] ?? null,
        ]);

        return $user;
    }
}
