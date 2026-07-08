<?php

namespace App\Http\Middleware;

use App\Models\Property;
use App\Services\PropertyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectWebsiteProperty
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            $subdomain = $parts[0];

            $property = Property::where('domain', $subdomain)
                ->where('is_active', true)
                ->first();

            if ($property) {
                app(PropertyService::class)->setCurrent($property);

                return $next($request);
            }
        }

        app(PropertyService::class)->clear();

        return $next($request);
    }
}
