<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSequence extends Model
{
    protected $fillable = ['prefix', 'next_number'];

    protected $casts = ['next_number' => 'integer'];
}
