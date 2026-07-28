<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folio extends Model
{
    protected $fillable = [
        'registration_id', 'folio_number', 'folio_name',
        'status', 'balance', 'notes', 'created_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FolioItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(RegistrationCharge::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public static function generateFolioNumber(): string
    {
        $prefix = 'FOL-'.now()->format('Ymd');
        $last = static::where('folio_number', 'like', $prefix.'-%')
            ->orderBy('folio_number', 'desc')
            ->value('folio_number');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
