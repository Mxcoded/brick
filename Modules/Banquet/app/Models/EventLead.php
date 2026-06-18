<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;

class EventLead extends Model
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
}
