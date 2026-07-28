<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NightAuditLog extends Model
{
    protected $fillable = [
        'business_date', 'started_at', 'completed_at', 'status',
        'rooms_occupied', 'total_revenue_posted', 'charges_posted',
        'notes', 'performed_by',
    ];

    protected $casts = [
        'business_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rooms_occupied' => 'integer',
        'total_revenue_posted' => 'decimal:2',
        'charges_posted' => 'integer',
    ];

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(RegistrationCharge::class, 'night_audit_log_id');
    }
}
