<?php

namespace Modules\Banquet\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, HasProperty;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'property_id',
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
