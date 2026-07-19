<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EventLead extends Model implements AuditableContract
{
    protected $fillable = [
        'event_id',
        'name',
        'email',
        'phone',
        'company',
        'source',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function leadEvent()
    {
        return $this->belongsTo(LeadEvent::class, 'event_id');
    }
    use Auditable;
}