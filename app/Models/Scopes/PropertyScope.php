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
        $service = App::make(PropertyService::class);

        if ($service->isViewingAll()) {
            $ids = $service->idsForUser();
            if ($ids) {
                $builder->whereIn($model->getTable().'.property_id', $ids);
            } else {
                $builder->whereRaw('1 = 0');
            }

            return;
        }

        $propertyId = $service->id();
        if ($propertyId) {
            $builder->where($model->getTable().'.property_id', $propertyId);
        }
    }
}
