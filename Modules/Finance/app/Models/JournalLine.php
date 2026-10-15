<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Finance\Support\SubLedger;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class JournalLine extends Model implements AuditableContract
{
    protected $table = 'finance_journal_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'party_type',
        'party_id',
        'debit',
        'credit',
        'cost_center',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * The guest or company this line is booked against, for AR/AP lines.
     */
    public function party(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The party's readable name, resolved without loading the relation so it is
     * safe to call in a list. Null when the line has no sub-ledger.
     */
    public function getPartyLabelAttribute(): ?string
    {
        return SubLedger::label($this->party_type, $this->party_id);
    }

    /**
     * Whether this line carries a sub-ledger.
     */
    public function hasParty(): bool
    {
        return (bool) $this->party_type && (bool) $this->party_id;
    }

    use Auditable;
}
