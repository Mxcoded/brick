<?php

namespace Modules\Gym\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Modules\Gym\Database\Factories\SubscriptionConfigFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class SubscriptionConfig extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'monthly_fee',
        'quarterly_fee',
        'six_months_fee',
        'yearly_fee',
        'session_fee',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'quarterly_fee' => 'decimal:2',
        'six_months_fee' => 'decimal:2',
        'yearly_fee' => 'decimal:2',
        'session_fee' => 'decimal:2',
    ];

    // protected static function newFactory(): SubscriptionConfigFactory
    // {
    //     // return SubscriptionConfigFactory::new();
    // }
}
