<?php

namespace Modules\Restaurant\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaiterShift extends Model
{
    use HasFactory;

    protected $table = 'waiter_shifts';

    protected $fillable = [
        'user_id',
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

    public function orders()
    {
        return $this->hasMany(Order::class, 'shift_id');
    }
}
