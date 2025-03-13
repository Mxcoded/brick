<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Staff\Database\Factories\EducationalBackgroundFactory;

class EducationalBackground extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'school_name',
        'start_date',
        'end_date',
        'qualification',
        'certificate_path',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // protected static function newFactory(): EducationalBackgroundFactory
    // {
    //     // return EducationalBackgroundFactory::new();
    // }
}
