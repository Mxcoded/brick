<?php

namespace Modules\Contracts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AgreementAmendment extends Model
{
    protected $fillable = [
        'agreement_id',
        'amendment_no',
        'title',
        'description',
        'status',
        'effective_date',
        'created_by',
        'signed_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'signed_at' => 'datetime',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
