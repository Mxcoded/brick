<?php

namespace Modules\Inventory\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcurementRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isStaff() && ! $user->hasAnyRole(RoleEnum::procurementRoles()))) {
            abort(403, 'You do not have procurement access.');
        }

        return $next($request);
    }
}
