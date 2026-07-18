<?php

namespace Modules\Staff\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasProperty;

    protected $fillable = [
        'name', 'start_time', 'end_time', 'grace_minutes', 'description', 'is_active', 'property_id',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'string',
            'end_time' => 'string',
            'is_active' => 'boolean',
            'grace_minutes' => 'integer',
        ];
    }

    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }
}
