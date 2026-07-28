<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Shift extends Model implements AuditableContract
{
    protected $fillable = [
        'name', 'start_time', 'end_time', 'grace_minutes', 'description', 'is_active',
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

    use Auditable;
}
