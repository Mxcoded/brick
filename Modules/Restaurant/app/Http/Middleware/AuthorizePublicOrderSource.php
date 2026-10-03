<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Restaurant\Support\RoomServiceAccess;
use Symfony\Component\HttpFoundation\Response;

/**
 * Decides whether a public order request may use the source it names.
 *
 * The restaurant has three ways in, and they do not have the same audience:
 *
 *  - online: open to anyone, the only flow the web may start;
 *  - room:   a checked-in guest, and only to the room they are occupying;
 *  - table:  staff, on the waiter terminal, never the public.
 *
 * The generic `/restaurant/{type}/{source}/...` routes are shared by all three,
 * so this is the one place that holds the rule. Without it, the table and room
 * ids in those URLs are just numbers a visitor can change.
 */
class AuthorizePublicOrderSource
{
    public function __construct(private RoomServiceAccess $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        $type = $request->route('type') ?? $request->input('type');

        if ($type === 'online') {
            return $next($request);
        }

        if ($type === 'room') {
            $roomId = $request->route('source') ?? $request->input('source');

            if ($this->access->allows($request->user(), $roomId)) {
                return $next($request);
            }

            abort(403, 'Room service is available to guests checked into that room.');
        }

        // Dine-in tables are taken by staff on the terminal. There is no public
        // table flow left to authorize, so the route does not exist for them.
        abort(404);
    }
}
