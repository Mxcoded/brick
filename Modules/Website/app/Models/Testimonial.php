<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Website\Database\Factories\TestimonialFactory;

class Testimonial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'guest_name',
        'text',
        'rating',
        'guest_image',
        'stay_type',
        'approved',
    ];

    // protected static function newFactory(): TestimonialFactory
    // {
    //     // return TestimonialFactory::new();
    // }
}
