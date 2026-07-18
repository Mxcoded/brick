<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\Traits\HasProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateAccount extends Model
{
    use HasFactory, HasProperty;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'credit_limit',
        'current_balance',
        'payment_terms',
        'notes',
        'is_active',
        'property_id',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(CityLedgerTransaction::class, 'corporate_account_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAvailableCreditAttribute(): float
    {
        return $this->credit_limit - $this->current_balance;
    }
}
