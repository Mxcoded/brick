<?php

namespace Modules\Staff\Models;

use App\Models\Traits\HasProperty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingRecord extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = [
        'employee_id',
        'property_id',
        'course_name',
        'provider',
        'training_type',
        'start_date',
        'end_date',
        'duration_hours',
        'status',
        'certification_name',
        'certification_url',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeCompleted($q)
    {
        return $q->where('status', 'completed');
    }

    public function scopeExpiringSoon($q, int $days = 30)
    {
        return $q->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::today()->addDays($days))
            ->where('expiry_date', '>=', Carbon::today());
    }

    public function scopeExpired($q)
    {
        return $q->whereNotNull('expiry_date')
            ->where('expiry_date', '<', Carbon::today());
    }
}
