<?php

namespace App\Models\Scopes;

use App\Services\PropertyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

class PropertyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $propertyId = App::make(PropertyService::class)->id();
        if ($propertyId) {
            $builder->where($model->getTable().'.property_id', $propertyId);
        }
    }
}
