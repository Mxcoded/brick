<?php

namespace Modules\Gym\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Modules\Gym\Database\Factories\TrainerFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Trainer extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'full_name',
        'phone_number',
        'email_address',
        'specialization',
    ];

    public function trainerPayments()
    {
        return $this->hasMany(TrainerPayment::class);
    }

    // protected static function newFactory(): TrainerFactory
    // {
    //     // return TrainerFactory::new();
    // }
}
