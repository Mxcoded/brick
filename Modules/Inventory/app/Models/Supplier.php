<?php

namespace Modules\Inventory\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// use Modules\Inventory\Database\Factories\SupplierFactory;

/**
 * Class Supplier
 * Represents a supplier or vendor of items.
 */
class Supplier extends Model
{
    use HasFactory, HasProperty, SoftDeletes;

    protected $fillable = ['name', 'contact_person', 'email', 'phone', 'address', 'property_id'];

    /**
     * Get the items from this supplier.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
