<?php

namespace Modules\Maintenance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Maintenance\Database\Factories\MaintenanceLogFactory;

class MaintenanceLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'location',
        'complaint_datetime',
        'nature_of_complaint',
        'lodged_by',
        'received_by',
        'cost_of_fixing',
        'completion_date',
        'status',
    ];
    protected $primaryKey = 'id';
    protected $dates =
    [
        'complaint_datetime',
        'completion_date'
    ];
    protected $casts = [
        'complaint_datetime' => 'datetime',
        'completion_date' => 'date',
    ];
    public function getRouteKeyName()
    {
        return 'id'; // Should return 'id' (default)
    }
    // protected static function newFactory(): MaintenanceLogFactory
    // {
    //     // return MaintenanceLogFactory::new();
    // }
}
