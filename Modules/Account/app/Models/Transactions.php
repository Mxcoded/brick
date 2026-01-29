<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Account\Database\Factories\TransactionsFactory;

class Transactions extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): TransactionsFactory
    // {
    //     // return TransactionsFactory::new();
    // }
}
