<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Modules\Staff\Database\Factories\EducationalBackgroundFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EducationalBackground extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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
