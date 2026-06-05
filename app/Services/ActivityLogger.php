<?php

namespace App\Services;

use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, ?string $description = null, $model = null): void
    {
        if (!Auth::check()) {
            return;
        }

        $data = [
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];

        if ($model) {
            $data['model_type'] = get_class($model);
            $data['model_id'] = $model->getKey();
        }

        UserActivityLog::create($data);
    }
}