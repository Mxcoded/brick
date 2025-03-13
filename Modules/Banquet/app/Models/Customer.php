<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'organization',
    ];

    /**
     * Get the banquet orders associated with the customer.
     */
    public function banquetOrders()
    {
        return $this->hasMany(BanquetOrder::class);
    }
}
