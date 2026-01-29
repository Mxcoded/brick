<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Account\Database\Factories\InvoicesFactory;

class Invoices extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): InvoicesFactory
    // {
    //     // return InvoicesFactory::new();
    // }

    public function order() {
        return $this->belongsTo(Orders::class);
    }

    public function transactions() {
        return $this->hasMany(Transactions::class);
    }
}
