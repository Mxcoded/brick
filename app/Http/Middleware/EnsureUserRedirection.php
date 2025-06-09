<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EnsureUserRedirection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')[0]; // Get the collection of role names

        // Log the roles for debugging (optional)
        Log::info('User roles retrieved:', ['user_id' => $user->id, 'roles' => $userRoles]);

        // Define role-to-route mappings with priority order
        $roleRoutes = [
            'admin' => 'admin.dashboard',
            'staff' => 'staff.dashboard',
            'guest' => 'website.guest.dashboard', // Added mapping for guest role
        ];
      
        switch ($userRoles) {
            case 'admin':
            case 'staff':
            case 'guest':
                $route = $roleRoutes[$userRoles];
                Log::info("Redirecting to {$route} for user ID: " . $user->id);
                session(['user_roles' => $userRoles]);
                return redirect()->route($route);

            default:
                Log::warning('User ID: ' . $user->id . ' has no recognized role.');
                return redirect()->route('default.dashboard')
                    ->with('error', 'You do not have access to any dashboard.');
        }
    }
    // Check each role in the mapping against the user's roles
    //     foreach ($roleRoutes as $role => $route) {
    //         if ($userRoles->contains($role)) { // Use contains() to check if role exists in collection
    //             Log::info("Redirecting to {$route} for user ID: " . $user->id);
    //             // Store the user's roles in the session for other modules
    //             session(['user_roles' => $userRoles->toArray()]);
    //             return redirect()->route($route);
    //         }
    //     }

    //     // Fallback for users without recognized roles
    //     Log::warning('User ID: ' . $user->id . ' has no recognized role.');
    //     return redirect()->route('default.dashboard')
    //         ->with('error', 'You do not have access to any dashboard.');
    // }

}