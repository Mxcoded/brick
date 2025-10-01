<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Website\Database\Factories\ContactMessageFactory;

class ContactMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'message',
        'status',
    ];
    protected $casts = [
        'status' => 'string',
    ];

    // protected static function newFactory(): ContactMessageFactory
    // {
    //     // return ContactMessageFactory::new();
    // }
}
