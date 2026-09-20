<?php

namespace Modules\Contracts\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementParty extends Model
{
    protected $fillable = [
        'agreement_id',
        'party_role',
        'legal_name',
        'entity_type',
        'registration_no',
        'contact_person',
        'position',
        'address',
        'email',
        'phone',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
}
