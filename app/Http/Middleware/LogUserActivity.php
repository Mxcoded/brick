<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    protected const THROTTLE_SECONDS = 60;

    protected array $excludedPaths = [
        'livewire/*', '_debugbar/*', 'telescope/*', 'ignition/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (! $request->user()) {
            return;
        }

        foreach ($this->excludedPaths as $pattern) {
            if ($request->is($pattern)) {
                return;
            }
        }

        $method = $request->method();

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $action = match ($method) {
                'POST' => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default => 'create',
            };
            ActivityLogger::log($action, $this->describeRequest($request));

            return;
        }

        $cacheKey = 'activity_log_page_'.$request->user()->id;
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, self::THROTTLE_SECONDS);
        ActivityLogger::log('page_view', $this->describeRequest($request));
    }

    protected function describeRequest(Request $request): string
    {
        $route = $request->route();
        $name = $route?->getName() ?? $request->path();

        return $name;
    }
}
