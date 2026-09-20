<?php

namespace Modules\Contracts\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementClause extends Model
{
    protected $fillable = [
        'agreement_id',
        'template_id',
        'version',
        'title',
        'content',
        'sort_order',
    ];

    protected $casts = [
        'version' => 'integer',
        'sort_order' => 'integer',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    public function template()
    {
        return $this->belongsTo(AgreementTemplate::class);
    }
}
