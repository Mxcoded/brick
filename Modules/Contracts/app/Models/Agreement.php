<?php

namespace Modules\Contracts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Contracts\Enums\AgreementStatus;
use Modules\Contracts\Enums\AgreementType;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Agreement extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'agreement_number',
        'template_id',
        'title',
        'type',
        'status',
        'currency',
        'current_version',
        'effective_date',
        'expiry_date',
        'auto_renew',
        'value_amount',
        'deposit_amount',
        'location',
        'department',
        'notes',
        'commercial_terms',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'auto_renew' => 'boolean',
        'value_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'commercial_terms' => 'array',
        'current_version' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(AgreementTemplate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function versions()
    {
        return $this->hasMany(AgreementVersion::class);
    }

    public function clauses()
    {
        return $this->hasMany(AgreementClause::class)->orderBy('sort_order');
    }

    public function parties()
    {
        return $this->hasMany(AgreementParty::class);
    }

    public function clientParties()
    {
        return $this->parties()->where('party_role', 'client');
    }

    public function hotelParty()
    {
        return $this->parties()->where('party_role', 'hotel')->first();
    }

    public function primaryClient()
    {
        return $this->parties()->where('party_role', 'client')->where('is_primary', true)->first()
            ?? $this->clientParties()->first();
    }

    public function signatures()
    {
        return $this->hasMany(AgreementSignature::class);
    }

    public function approvals()
    {
        return $this->hasMany(AgreementApproval::class)->latest();
    }

    public function amendments()
    {
        return $this->hasMany(AgreementAmendment::class)->latest();
    }

    public function obligations()
    {
        return $this->hasMany(AgreementObligation::class);
    }

    public function documents()
    {
        return $this->hasMany(AgreementDocument::class);
    }

    public function statusEnum(): AgreementStatus
    {
        return AgreementStatus::from($this->status);
    }

    public function typeEnum(): AgreementType
    {
        return AgreementType::from($this->type);
    }

    public function getClientNameAttribute()
    {
        return $this->primaryClient()?->legal_name ?? '—';
    }

    public function getExpiryLabelAttribute(): string
    {
        if ($this->status === AgreementStatus::ACTIVE->value && $this->expiry_date) {
            $days = now()->startOfDay()->diffInDays($this->expiry_date, false);

            if ($days < 0) {
                return 'Expired '.abs($days).'d ago';
            }
            if ($days <= 7) {
                return "Expires in {$days}d";
            }
            if ($days <= 30) {
                return "Expires in {$days}d";
            }
        }

        return $this->expiry_date?->format('d M Y') ?? '—';
    }

    public function scopeActive($query)
    {
        return $query->where('status', AgreementStatus::ACTIVE->value);
    }

    public function scopeNotTerminal($query)
    {
        return $query->whereNotIn('status', [
            AgreementStatus::EXPIRED->value,
            AgreementStatus::CANCELLED->value,
        ]);
    }
}
