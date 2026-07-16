<?php

namespace Modules\Inventory\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcurementRole
{
    protected array $roles = [
        'line_manager', 'purchaser', 'gm', 'finance', 'auditor', 'ggm',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isStaff() && ! $user->hasAnyRole($this->roles))) {
            abort(403, 'You do not have procurement access.');
        }

        return $next($request);
    }
}
