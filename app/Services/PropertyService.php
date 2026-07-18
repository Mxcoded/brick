<?php

namespace App\Services;

use App\Models\Property;

class PropertyService
{
    public function current(): ?Property
    {
        if ($this->isViewingAll()) {
            return null;
        }

        return Property::current();
    }

    public function id(): ?int
    {
        return $this->current()?->id;
    }

    public function isViewingAll(): bool
    {
        return session('current_property_id') === 'all';
    }

    public function setCurrent(Property $property): void
    {
        session(['current_property_id' => $property->id]);
    }

    public function setAll(): void
    {
        session(['current_property_id' => 'all']);
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

    public function idsForUser(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        return $user->properties()->active()->pluck('properties.id')->toArray();
    }

    public function scope($query, string $column = 'property_id')
    {
        if ($this->isViewingAll()) {
            $ids = $this->idsForUser();

            return $ids ? $query->whereIn($column, $ids) : $query->whereRaw('1 = 0');
        }

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
