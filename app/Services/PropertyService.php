<?php

namespace App\Services;

use App\Models\Property;

class PropertyService
{
    public function current(): ?Property
    {
        return Property::current();
    }

    public function id(): ?int
    {
        return $this->current()?->id;
    }

    public function setCurrent(Property $property): void
    {
        session(['current_property_id' => $property->id]);
    }

    public function clear(): void
    {
        session()->forget('current_property_id');
    }

    public function forUser(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        return $user->properties()->active()->get()->all();
    }

    public function scope($query, string $column = 'property_id')
    {
        $propertyId = $this->id();
        if ($propertyId) {
            return $query->where($column, $propertyId);
        }

        return $query;
    }

    public function taxRate(): float
    {
        return $this->current()?->getTaxRate() ?? 7.5;
    }
}
