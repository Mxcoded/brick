<?php

namespace Modules\Contracts\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementSignature extends Model
{
    protected $fillable = [
        'agreement_id',
        'party_role',
        'party_name',
        'position',
        'signature_type',
        'signature_data',
        'signed_at',
        'ip_address',
        'user_agent',
        'verification',
        'hash',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
}
