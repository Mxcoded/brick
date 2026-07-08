<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'image_url',
        'opening_hours',
        'cuisine_type',
        'dress_code',
        'menu_link',
        'menu_pdf',
        'is_featured',
    ];

    // protected static function newFactory(): DiningFactory
    // {
    //     // return DiningFactory::new();
    // }
}
