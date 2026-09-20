<?php

namespace Modules\Contracts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AgreementDocument extends Model
{
    protected $fillable = [
        'agreement_id',
        'name',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
