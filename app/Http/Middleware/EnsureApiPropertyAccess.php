<?php

namespace App\Http\Middleware;

use App\Models\Property;
use App\Services\PropertyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiPropertyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $propertyId = $request->header('X-Property-ID') ?? $request->query('property_id');

        if ($propertyId) {
            $user = $request->user();

            if ($user && ! $user->properties()->where('properties.id', $propertyId)->exists()) {
                return response()->json([
                    'message' => 'You do not have access to this property.',
                ], 403);
            }

            $property = Property::find($propertyId);
            if ($property) {
                $propertyService = App::make(PropertyService::class);
                $propertyService->setCurrent($property);
            }
        }

        return $next($request);
    }
}
