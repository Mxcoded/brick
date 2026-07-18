<?php

namespace Modules\Finance\Models;

use App\Models\Traits\HasProperty;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    use HasProperty;

    protected $table = 'finance_journal_entries';

    protected $fillable = [
        'property_id',
        'entry_number',
        'date',
        'reference_type',
        'reference_id',
        'description',
        'status',
        'posted_at',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isBalanced(): bool
    {
        $debit = (float) $this->lines->sum('debit');
        $credit = (float) $this->lines->sum('credit');

        return abs($debit - $credit) < 0.005;
    }

    public function post(): void
    {
        if ($this->lines->count() < 2) {
            throw new \InvalidArgumentException('A journal entry requires at least two lines.');
        }

        if (! $this->isBalanced()) {
            throw new \InvalidArgumentException('Journal entry is not balanced: total debits must equal total credits.');
        }

        $this->status = 'posted';
        $this->posted_at = now();
        $this->save();
    }
}
