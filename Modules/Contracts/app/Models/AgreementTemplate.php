<?php

namespace Modules\Contracts\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function clauses()
    {
        return $this->hasMany(AgreementClause::class, 'template_id')->orderBy('sort_order');
    }

    public function agreements()
    {
        return $this->hasMany(Agreement::class, 'template_id');
    }
}
