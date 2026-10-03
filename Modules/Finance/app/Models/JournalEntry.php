<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Finance\Services\FiscalPeriodService;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class JournalEntry extends Model implements AuditableContract
{
    protected $table = 'finance_journal_entries';

    protected $fillable = [
        'entry_number',
        'date',
        'reference_type',
        'reference_id',
        'reverses_id',
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

    /**
     * The entry this one cancels, if it was created as a reversal.
     */
    public function reverses(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_id');
    }

    /**
     * The reversal created against this entry, if any.
     */
    public function reversal(): HasOne
    {
        return $this->hasOne(self::class, 'reverses_id');
    }

    public function isReversal(): bool
    {
        return $this->reverses_id !== null;
    }

    /**
     * Sequential per-month number, e.g. JE-202609-0001. Shared by manual entries
     * and auto-posting so the sequence stays gap-free across both.
     */
    public static function nextEntryNumber(): string
    {
        $prefix = 'JE-'.now()->format('Ym').'-';
        $last = static::where('entry_number', 'like', $prefix.'%')->max('entry_number');
        $sequence = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function isReversed(): bool
    {
        return $this->reversal()->exists();
    }

    public function isBalanced(): bool
    {
        $debit = (float) $this->lines->sum('debit');
        $credit = (float) $this->lines->sum('credit');

        return abs($debit - $credit) < 0.005;
    }

    public function post(): void
    {
        if ($this->status === 'posted') {
            return;
        }

        if ($this->lines->count() < 2) {
            throw new \InvalidArgumentException('A journal entry requires at least two lines.');
        }

        if (! $this->isBalanced()) {
            throw new \InvalidArgumentException('Journal entry is not balanced: total debits must equal total credits.');
        }

        app(FiscalPeriodService::class)->assertOpenFor($this->date);

        $this->status = 'posted';
        $this->posted_at = now();
        $this->save();
    }

    use Auditable;
}
