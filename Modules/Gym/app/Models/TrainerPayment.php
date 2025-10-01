<?php

namespace Modules\Gym\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Gym\Database\Factories\TrainerPaymentFactory;

class TrainerPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'trainer_id',
        'membership_id',
        'payment_amount',
        'payment_date',
        'payment_type',
        'remaining_balance',
        'payment_mode',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'payment_date' => 'date',
        'payment_type' => 'string',
        'payment_mode' => 'string',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    // protected static function newFactory(): TrainerPaymentFactory
    // {
    //     // return TrainerPaymentFactory::new();
    // }
}
