<?php

namespace App\Http\Middleware;

use App\Models\Property;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPropertyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $propertyId = $request->query('property_id') ?? session('current_property_id');

            if ($propertyId) {
                $property = Property::find($propertyId);
                if ($property && auth()->user()->properties()->where('property_id', $property->id)->exists()) {
                    session(['current_property_id' => $property->id]);
                }
            } else {
                Property::getDefault();
            }
        }

        return $next($request);
    }
}
