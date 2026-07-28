<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Modules\Inventory\Database\Factories\SupplierFactory;

/**
 * Class Supplier
 * Represents a supplier or vendor of items.
 */
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Supplier extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = ['name', 'contact_person', 'email', 'phone', 'address'];

    /**
     * Get the items from this supplier.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
