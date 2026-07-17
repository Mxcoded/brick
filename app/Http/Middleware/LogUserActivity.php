<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Staff\Models\Employee;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    protected const THROTTLE_SECONDS = 60;

    protected array $excludedPaths = [
        'livewire/*', '_debugbar/*', 'telescope/*', 'ignition/*',
    ];

    // Leading route-name segments that are area/module prefixes, not resources.
    protected array $areaPrefixes = ['website', 'admin', 'frontdesk', 'frontdeskcrm'];

    /**
     * Route parameter name -> model class overrides, used when the parameter
     * name does not match the model's class name (e.g. "staff" -> Employee).
     */
    protected array $paramModelAliases = [
        'staff' => Employee::class,
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
            $model = $this->resolveAffectedModel($request);
            $action = $this->resolveAction($request, $method);
            $description = $this->buildDescription($request, $model);

            ActivityLogger::log($action, $description, $model);

            return;
        }

        $cacheKey = 'activity_log_page_'.$request->user()->id;
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, self::THROTTLE_SECONDS);
        ActivityLogger::log('page_view', $this->buildDescription($request, null));
    }

    /**
     * Build a meaningful action label like "staff.update" or "bookings.assign-room"
     * from the route name, instead of the generic create/update/delete.
     */
    protected function resolveAction(Request $request, string $method): string
    {
        $route = $request->route();
        $name = $route?->getName();

        if ($name) {
            $segments = explode('.', $name);
            $verb = array_pop($segments);
            $segments = array_values(array_filter(
                $segments,
                fn ($s) => ! in_array($s, $this->areaPrefixes)
            ));
            $resource = implode('.', $segments) ?: 'record';

            return $resource.'.'.$verb;
        }

        return match ($method) {
            'POST' => 'record.create',
            'DELETE' => 'record.delete',
            default => 'record.update',
        };
    }

    /**
     * Find the Eloquent model targeted by the request so the log records
     * exactly which record was affected.
     *
     * This project's admin controllers mostly resolve records via
     * `findOrFail($id)` (no implicit route-model binding), so we also infer
     * the model from the route parameter name convention:
     *   {booking}   -> Booking
     *   {room_type} -> RoomType
     *   {testimonial} -> Testimonial
     */
    protected function resolveAffectedModel(Request $request): ?Model
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        // 1. Explicit route-model binding (param is already a Model instance).
        foreach ($route->parameters() as $param) {
            if ($param instanceof Model) {
                return $param;
            }
        }

        // 2. Param-name convention -> resolve via the id value.
        foreach ($route->parameterNames() as $name) {
            if ($name === 'id') {
                continue; // ambiguous generic key, skip
            }

            if ($modelClass = $this->modelClassForParam($name)) {
                $instance = $modelClass::find($route->parameter($name));
                if ($instance) {
                    return $instance;
                }
            }
        }

        return null;
    }

    /**
     * Map a route parameter name to its Eloquent model class using the
     * project's naming convention. Result is cached per parameter name.
     */
    protected function modelClassForParam(string $name): ?string
    {
        static $map = [];

        if (array_key_exists($name, $map)) {
            return $map[$name];
        }

        if (isset($this->paramModelAliases[$name])) {
            $aliased = $this->paramModelAliases[$name];
            if (class_exists($aliased) && is_subclass_of($aliased, Model::class)) {
                $map[$name] = $aliased;

                return $aliased;
            }
        }

        $studly = Str::studly(Str::singular($name));

        $candidates = ["App\\Models\\{$studly}"];

        if (class_exists(Module::class)) {
            foreach (Module::all() as $module) {
                $candidates[] = 'Modules\\'.$module->getName().'\\Models\\'.$studly;
            }
        }

        $found = null;
        foreach ($candidates as $class) {
            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $found = $class;
                break;
            }
        }

        $map[$name] = $found;

        return $found;
    }

    /**
     * Build a human-readable description that names the resource and the
     * specific record that was touched, e.g. "Staff: John Doe (#5)".
     */
    protected function buildDescription(Request $request, ?Model $model): string
    {
        $resource = $this->resourceLabel($request);

        if ($model) {
            $name = $this->modelDisplayName($model);
            $identifier = $model->getKey();

            return "{$resource}: {$name} (#{$identifier})";
        }

        // Fallback: the route name / path so the entry is still traceable.
        return $resource;
    }

    protected function resourceLabel(Request $request): string
    {
        $route = $request->route();
        $name = $route?->getName();

        if ($name) {
            $segments = explode('.', $name);
            array_pop($segments); // drop the verb
            $segments = array_values(array_filter(
                $segments,
                fn ($s) => ! in_array($s, $this->areaPrefixes)
            ));
            $resource = implode(' ', $segments) ?: 'record';

            return ucwords(str_replace(['-', '_'], ' ', $resource));
        }

        return ucwords(str_replace(['-', '/', '_'], ' ', $request->path()));
    }

    protected function modelDisplayName(Model $model): string
    {
        foreach (['name', 'full_name', 'guest_name', 'title', 'email', 'room_number', 'booking_reference', 'reference', 'username'] as $attr) {
            if (! empty($model->{$attr})) {
                return (string) $model->{$attr};
            }
        }

        return class_basename($model);
    }
}
