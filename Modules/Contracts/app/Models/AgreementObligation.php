<?php

namespace Modules\Contracts\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementObligation extends Model
{
    protected $fillable = [
        'agreement_id',
        'obligation_type',
        'title',
        'description',
        'responsible_party',
        'amount',
        'due_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-success',
            'overdue' => 'bg-danger',
            'in_progress' => 'bg-info',
            default => 'bg-warning',
        };
    }
}
