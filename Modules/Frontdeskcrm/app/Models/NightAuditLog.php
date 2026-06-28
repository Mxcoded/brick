<?php

namespace Modules\Frontdeskcrm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NightAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'night_audit_id',
        'auditable_type',
        'auditable_id',
        'action',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function nightAudit(): BelongsTo
    {
        return $this->belongsTo(NightAudit::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}
