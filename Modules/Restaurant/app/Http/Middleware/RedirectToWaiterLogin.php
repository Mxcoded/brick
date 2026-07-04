<?php

namespace Modules\Restaurant\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;

class RedirectToWaiterLogin extends Authenticate
{
    protected function redirectTo($request)
    {
        return route('restaurant.waiter.login');
    }
}
