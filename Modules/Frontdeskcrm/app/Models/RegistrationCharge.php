<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationCharge extends Model
{
    protected $fillable = [
        'registration_id', 'charge_type', 'description', 'amount',
        'charge_date', 'is_audited', 'night_audit_log_id',
        'folio_id', 'tax_code', 'tax_rate', 'tax_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'charge_date' => 'date',
        'is_audited' => 'boolean',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function nightAuditLog(): BelongsTo
    {
        return $this->belongsTo(NightAuditLog::class);
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('charge_type', $type);
    }
}
