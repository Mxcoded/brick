<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class MeetingGallery extends Model implements AuditableContract
{
    protected $fillable = [
        'meeting_page_id',
        'image',
        'alt_text',
        'sort_order',
    ];

    public function meetingPage()
    {
        return $this->belongsTo(MeetingPage::class);
    }
    use Auditable;
}