<?php

namespace Modules\Contracts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AgreementApproval extends Model
{
    protected $fillable = [
        'agreement_id',
        'status',
        'comments',
        'approved_by',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
