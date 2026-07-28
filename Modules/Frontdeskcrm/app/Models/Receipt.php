<?php

namespace Modules\Frontdeskcrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'receipt_number', 'invoice_id', 'payment_id',
        'amount', 'payment_method', 'receipted_at',
        'printed_by', 'print_count',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'receipted_at' => 'datetime',
        'print_count' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(RegistrationPayment::class, 'payment_id');
    }

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'RCT-'.now()->format('Ymd');
        $last = static::where('receipt_number', 'like', $prefix.'-%')
            ->orderBy('receipt_number', 'desc')
            ->value('receipt_number');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
