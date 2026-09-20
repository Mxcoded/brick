<?php

namespace Modules\Contracts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AgreementVersion extends Model
{
    protected $fillable = [
        'agreement_id',
        'version',
        'status',
        'changes_summary',
        'created_by',
    ];

    protected $casts = [
        'version' => 'integer',
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
