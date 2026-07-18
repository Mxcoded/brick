<?php

namespace Modules\Gym\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Gym\Database\Factories\TrainerFactory;

class Trainer extends Model
{
    use HasFactory, HasProperty;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'full_name',
        'phone_number',
        'email_address',
        'specialization',
        'property_id',
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
