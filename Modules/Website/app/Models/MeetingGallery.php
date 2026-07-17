<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingGallery extends Model
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
}
