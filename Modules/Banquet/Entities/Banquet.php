<?php

namespace Modules\Banquet\Entities;

use Illuminate\Database\Eloquent\Model;

class Banquet extends Model
{
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function locationTimes()
    {
        return $this->hasMany(LocationTime::class);
    }

    public function menuSelections()
    {
        return $this->hasMany(MenuSelection::class);
    }
}
