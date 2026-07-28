<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    protected $fillable = [
        'credit_note_number', 'invoice_id', 'amount',
        'reason', 'issue_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateCreditNoteNumber(): string
    {
        $prefix = 'CN-'.now()->format('Ymd');
        $last = static::where('credit_note_number', 'like', $prefix.'-%')
            ->orderBy('credit_note_number', 'desc')
            ->value('credit_note_number');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
