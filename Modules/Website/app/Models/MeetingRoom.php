<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class MeetingRoom extends Model implements AuditableContract
{
    protected $fillable = [
        'meeting_page_id',
        'name',
        'size_sqm',
        'boardroom',
        'classroom',
        'theatre',
        'cocktail',
        'banquet',
        'cabaret',
        'ushape',
        'double_u',
        'triple_u',
        'sort_order',
    ];

    public function meetingPage()
    {
        return $this->belongsTo(MeetingPage::class);
    }

    use Auditable;
}
