<?php

namespace Modules\Staff\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'property_id',
        'review_date',
        'review_period',
        'rating_punctuality',
        'rating_teamwork',
        'rating_communication',
        'rating_quality',
        'rating_initiative',
        'overall_score',
        'strengths',
        'areas_for_improvement',
        'comments',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Employee::class, 'reviewer_id');
    }
}
