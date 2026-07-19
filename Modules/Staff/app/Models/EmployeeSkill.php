<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EmployeeSkill extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $fillable = [
        'employee_id',
        'skill_name',
        'category',
        'proficiency_level',
        'years_experience',
        'last_used_date',
        'is_certified',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_used_date' => 'date',
            'is_certified' => 'boolean',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}