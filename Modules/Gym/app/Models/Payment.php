<?php

namespace Modules\Gym\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Gym\Database\Factories\PaymentFactory;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'membership_id',
        'payment_amount',
        'payment_date',
        'payment_status',
        'payment_mode',
        'payment_type',
        'remaining_balance',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'payment_date' => 'datetime',
        'payment_status' => 'string',
        'payment_mode' => 'string',
        'payment_type' => 'string',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    // protected static function newFactory(): PaymentFactory
    // {
    //     // return PaymentFactory::new();
    // }
}
