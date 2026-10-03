<?php

namespace Modules\Admin\Http\Middleware;

use App\Support\LandingPage;
use Closure;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;

/**
 * Keeps Filament's guest behaviour (redirect to /admin/login) but replaces the
 * bare 403 that authenticated users without panel access used to get with a
 * redirect to somewhere they can actually use.
 *
 * This covers entry to the panel only. Filament denies a single page from
 * inside the Livewire render, which is later than any middleware can wrap, so
 * that case is converted in bootstrap/app.php instead.
 */
class RedirectUnauthorizedPanelUsers extends Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        $user = Filament::auth()->user();

        if ($user && method_exists($user, 'canAccessPanel')
            && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())) {
            if ($request->expectsJson()) {
                abort(403, 'You do not have access to the admin panel.');
            }

            return redirect()
                ->route(LandingPage::routeFor($user))
                ->with('warning', 'You do not have access to the admin panel.');
        }

        return parent::handle($request, $next, ...$guards);
    }
}
