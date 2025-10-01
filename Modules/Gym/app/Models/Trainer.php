<?php

namespace Modules\Gym\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Gym\Database\Factories\TrainerFactory;

class Trainer extends Model
{
    use HasFactory;

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
