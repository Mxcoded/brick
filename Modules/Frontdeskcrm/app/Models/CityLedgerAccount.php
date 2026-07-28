<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CityLedgerAccount extends Model
{
    protected $fillable = [
        'code', 'name', 'contact_person', 'email', 'phone', 'address',
        'tax_id', 'credit_limit', 'balance', 'payment_terms', 'status',
        'notes', 'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public static function generateCode(): string
    {
        $prefix = 'CL-'.now()->format('Ymd');
        $last = static::where('code', 'like', $prefix.'-%')
            ->orderBy('code', 'desc')
            ->value('code');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CityLedgerTransaction::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
