<?php

namespace Modules\Tasks\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Modules\Staff\Models\Employee;

/**
 * Single source of truth for the Tasks module's permissions.
 *
 * Tasks already had a granular set (`tasks.create`, `tasks.read`, ...) but they
 * were scattered as string literals across the controller and the views, so a
 * typo silently meant "no access" rather than an error. Everything now resolves
 * through here, which is also what makes the Filament resource and the legacy
 * controller enforce the same rules.
 */
class TaskPermission
{
    public const DASHBOARD_ACCESS = 'access_tasks_dashboard';

    public const READ = 'tasks.read';

    public const CREATE = 'tasks.create';

    public const UPDATE = 'tasks.update';

    public const DELETE = 'tasks.delete';

    public const ASSIGN = 'tasks.assign';

    /**
     * Every Tasks permission, in the order the admin UI should list them.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            self::DASHBOARD_ACCESS,
            self::READ,
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::ASSIGN,
        ];
    }

    /**
     * The baseline every member of staff already holds, granted by the
     * isStaff() blanket in AppServiceProvider rather than by a role.
     *
     * Tasks is a working tool, not a management console: anyone on the payroll
     * should be able to open it, raise a piece of work and keep it up to date.
     * That is why the baseline is not expressed as a role grant - a new
     * employee should not need a role created before they can track their day.
     *
     * @var array<int, string>
     */
    public const MEMBER = [
        self::DASHBOARD_ACCESS,
        self::READ,
        self::CREATE,
        self::UPDATE,
    ];

    /**
     * Roles allowed to hand a task to somebody else.
     *
     * Assignment is the one genuinely privileged action in the module: it is
     * the only way to make somebody else's workload, so it is kept off the staff
     * blanket and granted per role. Everyone else can still raise a task, and
     * TaskService assigns it to themselves.
     *
     * @var array<int, string>
     */
    public const DELEGATOR_ROLES = [
        'gm',
        'ggm',
        'hr_manager',
        'restaurant_manager',
        'gym_supervisor',
        'maintenance_engineer',
        'event_manager',
        'store_keeper',
        'line_manager',
        'purchaser',
    ];

    /**
     * The extra permissions a role holds on top of the staff baseline.
     *
     * Admin already receives every permission through the superset in
     * RoleSeeder, so it needs nothing added.
     *
     * @return array<int, string>
     */
    public static function extrasFor(string $role): array
    {
        return in_array($role, self::DELEGATOR_ROLES, true)
            ? [self::ASSIGN]
            : [];
    }

    /**
     * Can this role delegate a task to another person?
     */
    public static function canDelegate(string $role): bool
    {
        return in_array($role, self::DELEGATOR_ROLES, true);
    }

    public static function allows(?string $permission): bool
    {
        return static::can(auth()->user(), $permission);
    }

    public static function allowsAny(string ...$permissions): bool
    {
        $user = auth()->user();

        foreach ($permissions as $permission) {
            if (static::can($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    public static function can(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can($permission) || Gate::allows($permission);
    }

    /**
     * The employee record a user belongs to, if the hotel links the two.
     *
     * Assignees are employees rather than users, so almost every permission
     * question ("is this person an assignee?") is really an employee question.
     */
    public static function employeeIdFor(?User $user): ?int
    {
        if (! $user || ! class_exists(Employee::class)) {
            return null;
        }

        $id = Employee::where('user_id', $user->id)->value('id');

        return $id === null ? null : (int) $id;
    }
}
