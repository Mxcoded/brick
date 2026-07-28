<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Customer extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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
