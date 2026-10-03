<?php

namespace Modules\Restaurant\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class WaiterShift extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'waiter_shifts';

    protected $fillable = [
        'user_id',
        'sales_point_id',
        'collection_point_id',
        'clock_in',
        'clock_out',
        'starting_cash',
        'ending_cash',
        'total_sales',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'starting_cash' => 'decimal:2',
            'ending_cash' => 'decimal:2',
            'total_sales' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The outlet this waiter was on for the duration of the shift.
     */
    public function salesPoint()
    {
        return $this->belongsTo(SalesPoint::class);
    }

    /**
     * The physical terminal the takings for this shift went through.
     */
    public function collectionPoint()
    {
        return $this->belongsTo(CollectionPoint::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'shift_id');
    }
}
