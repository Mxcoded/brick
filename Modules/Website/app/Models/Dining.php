<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Website\Database\Factories\DiningFactory;

class Dining extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'image',
        'opening_hours',
        'cuisine_type',
        'dress_code',
        'menu_link',
        'is_featured',
    ];

    // protected static function newFactory(): DiningFactory
    // {
    //     // return DiningFactory::new();
    // }
}
