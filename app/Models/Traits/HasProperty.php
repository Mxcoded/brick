<?php

namespace App\Models\Traits;

use App\Models\Scopes\PropertyScope;
use App\Services\PropertyService;
use Illuminate\Database\Eloquent\Model;

trait HasProperty
{
    protected static function bootHasProperty(): void
    {
        static::addGlobalScope(new PropertyScope);

        static::creating(function (Model $model) {
            if (empty($model->property_id)) {
                $propertyId = app(PropertyService::class)->id();
                if ($propertyId) {
                    $model->property_id = $propertyId;
                }
            }
        });
    }
}
