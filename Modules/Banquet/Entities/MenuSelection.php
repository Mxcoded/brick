<?php

namespace Modules\Banquet\Entities;

use Illuminate\Database\Eloquent\Model;

class MenuSelection extends Model
{
    protected $guarded = [];

    /**
     * Get the banquet that owns the menu selection
     */
    public function banquet()
    {
        return $this->belongsTo(Banquet::class);
    }

    /**
     * Cast price to float
     */
    protected $casts = [
        'price' => 'float',
    ];

    /**
     * Define meal types as constants
     */
    const MEAL_TYPES = [
        'breakfast',
        'tea_break',
        'lunch',
        'dinner'
    ];
}
