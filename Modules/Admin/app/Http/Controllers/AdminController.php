<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\AccountCreated;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserLoginLog;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Banquet\Models\BanquetEnquiry;
use Modules\Banquet\Models\BanquetOrder;
use Modules\Banquet\Models\BanquetPayment;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Rules\ValidEmail;
use Modules\Gym\Models\Membership;
use Modules\Gym\Models\Payment as GymPayment;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Maintenance\Models\MaintenanceLog;
use Modules\Maintenance\Models\MaintenanceReading;
use Modules\Restaurant\Models\Order as RestaurantOrder;
use Modules\Staff\Helpers\DepartmentHelper;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveRequest;
use Modules\Tasks\Models\Task;
use Modules\Website\Models\Booking;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Models\Settings;
use Nwidart\Modules\Facades\Module;
use OwenIt\Auditing\Events\AuditCustom;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $activeModules = collect(Module::all())->filter(fn ($m) => $m->isEnabled())->count();

        // ── Financial KPIs ──
        $banquetRevenue = BanquetPayment::sum('amount');
        $banquetOutstanding = BanquetOrder::sum(DB::raw('total_revenue - (SELECT COALESCE(SUM(amount),0) FROM banquet_payments WHERE banquet_payments.banquet_order_id = banquet_orders.id)'));
        $frontdeskRevenue = Registration::whereIn('stay_status', ['checked_out', 'checked_in'])->sum('total_amount');
        $gymRevenue = GymPayment::sum('payment_amount');
        $websiteRevenue = Booking::where('payment_status', 'paid')->sum('total_amount');
        $totalRevenue = $banquetRevenue + $frontdeskRevenue + $gymRevenue + $websiteRevenue;
        $maintenanceCost = MaintenanceLog::where('status', 'completed')->sum('cost_of_fixing');

        // ── YTD Revenue ──
        $ytdBanquet = BanquetPayment::whereYear('payment_date', now()->year)->sum('amount');
        $ytdFrontdesk = Registration::whereIn('stay_status', ['checked_out', 'checked_in'])->whereYear('created_at', now()->year)->sum('total_amount');
        $ytdGym = GymPayment::whereYear('payment_date', now()->year)->sum('payment_amount');
        $ytdWebsite = Booking::where('payment_status', 'paid')->whereYear('created_at', now()->year)->sum('total_amount');
        $ytdRevenue = $ytdBanquet + $ytdFrontdesk + $ytdGym + $ytdWebsite;

        // ── Month-over-Month Revenue Change ──
        $startThisMonth = now()->startOfMonth();
        $startLastMonth = now()->subMonth()->startOfMonth();
        $endLastMonth = now()->subMonth()->endOfMonth();

        $revenueThisMonth = BanquetPayment::where('payment_date', '>=', $startThisMonth)->sum('amount')
            + Registration::whereIn('stay_status', ['checked_out', 'checked_in'])->where('created_at', '>=', $startThisMonth)->sum('total_amount')
            + GymPayment::where('payment_date', '>=', $startThisMonth)->sum('payment_amount')
            + Booking::where('payment_status', 'paid')->where('created_at', '>=', $startThisMonth)->sum('total_amount');

        $revenueLastMonth = BanquetPayment::whereBetween('payment_date', [$startLastMonth, $endLastMonth])->sum('amount')
            + Registration::whereIn('stay_status', ['checked_out', 'checked_in'])->whereBetween('created_at', [$startLastMonth, $endLastMonth])->sum('total_amount')
            + GymPayment::whereBetween('payment_date', [$startLastMonth, $endLastMonth])->sum('payment_amount')
            + Booking::where('payment_status', 'paid')->whereBetween('created_at', [$startLastMonth, $endLastMonth])->sum('total_amount');

        $revenueChange = $revenueLastMonth > 0 ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1) : 0;
        $revenueDirection = $revenueChange >= 0 ? 'up' : 'down';

        // ── Monthly Revenue Trend (last 6 months) ──
        $revenueMonths = [];
        $monthlyBanquet = [];
        $monthlyFrontdesk = [];
        $monthlyGym = [];
        $monthlyWebsite = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $label = $month->format('M');
            $revenueMonths[] = $label;
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $monthlyBanquet[] = (float) BanquetPayment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount');
            $monthlyFrontdesk[] = (float) Registration::whereIn('stay_status', ['checked_out', 'checked_in'])->whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount');
            $monthlyGym[] = (float) GymPayment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('payment_amount');
            $monthlyWebsite[] = (float) Booking::where('payment_status', 'paid')->whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount');
        }

        // ── Occupancy ──
        $totalRoomUnits = RoomUnit::count();
        $checkedIn = Registration::where('stay_status', 'checked_in')->count();
        $occupancyRate = $totalRoomUnits > 0 ? round(($checkedIn / $totalRoomUnits) * 100, 1) : 0;
        $occupancyRateLastMonth = $totalRoomUnits > 0
            ? round((Registration::where('stay_status', 'checked_in')->where('created_at', '<', $startThisMonth)->count() / $totalRoomUnits) * 100, 1)
            : 0;
        $occupancyChange = $occupancyRateLastMonth > 0 ? round($occupancyRate - $occupancyRateLastMonth, 1) : 0;

        // ── Staff & HR ──
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::whereNull('end_date')->count();
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $departments = collect(DepartmentHelper::consolidate(
            Employee::whereNull('end_date')->whereNotNull('department')->select('department', DB::raw('count(*) as total'))->groupBy('department')->get(),
            'total'
        ));

        // ── Banquet ──
        $banquetOrdersTotal = BanquetOrder::count();
        $banquetOrdersPending = BanquetOrder::where('status', 'Pending')->count();
        $banquetOrdersConfirmed = BanquetOrder::where('status', 'Confirmed')->count();
        $banquetOrdersCompleted = BanquetOrder::where('status', 'Completed')->count();
        $pendingEnquiries = BanquetEnquiry::whereIn('status', ['Pending', 'Contacted'])->count();
        $upcomingEvents = BanquetOrder::upcoming()->take(5)->get();
        $enquirySources = BanquetEnquiry::whereNotNull('hear_about_us')->select('hear_about_us', DB::raw('count(*) as total'))->groupBy('hear_about_us')->orderByDesc('total')->get();
        $banquetPaymentMethods = BanquetPayment::select('payment_method', DB::raw('SUM(amount) as total'))->groupBy('payment_method')->get();

        // ── Frontdesk ──
        $frontdeskRevenueMonth = Registration::whereIn('stay_status', ['checked_out', 'checked_in'])->whereMonth('created_at', now()->month)->sum('total_amount');
        $reservations = Registration::where('stay_status', 'reserved')->count();
        $todayCheckins = Registration::whereDate('check_in', today())->count();
        $todayCheckouts = Registration::whereDate('check_out', today())->count();
        $registrationSources = BookingSource::withCount('registrations')->orderByDesc('registrations_count')->take(5)->get();

        // ── Maintenance ──
        $maintenanceOpen = MaintenanceLog::whereIn('status', ['new', 'in_progress'])->count();
        $maintenanceCritical = MaintenanceLog::where('priority', 'critical')->whereIn('status', ['new', 'in_progress'])->count();
        $maintenanceCompletedMonth = MaintenanceLog::where('status', 'completed')->whereMonth('complaint_datetime', now()->month)->count();
        $maintenanceByDept = MaintenanceLog::select('department', DB::raw('count(*) as total'))->groupBy('department')->orderByDesc('total')->take(5)->get();
        $latestGenReadings = MaintenanceReading::byType('generator')->onDate(today())->get();
        $latestWaterReading = MaintenanceReading::byType('water_tank')->onDate(today())->first();
        $latestDieselReading = MaintenanceReading::byType('diesel_reservoir')->onDate(today())->first();

        // ── Tasks ──
        $tasksPending = Task::where('status', 'pending')->count();
        $tasksInProgress = Task::where('status', 'in_progress')->count();
        $tasksOverdue = Task::whereIn('status', ['pending', 'in_progress'])->where('deadline', '<', today())->count();
        $tasksCompletedMonth = Task::where('status', 'completed')->whereMonth('completion_date', now()->month)->count();
        $highPriorityTasks = Task::where('priority', 'high')->whereIn('status', ['pending', 'in_progress'])->count();

        // ── Restaurant ──
        if (Module::has('Restaurant') && Module::find('Restaurant')->isEnabled()) {
            $restaurantOrdersToday = RestaurantOrder::whereDate('created_at', today())->count();
            $restaurantOrdersPending = RestaurantOrder::where('status', 'pending')->count();
            $restaurantOrdersMonth = RestaurantOrder::whereMonth('created_at', now()->month)->count();
        } else {
            $restaurantOrdersToday = 0;
            $restaurantOrdersPending = 0;
            $restaurantOrdersMonth = 0;
        }

        // ── Gym ──
        $activeMemberships = Membership::count();
        $gymPaymentsMonth = GymPayment::whereMonth('payment_date', now()->month)->sum('payment_amount');
        $membershipDueSoon = Membership::where('next_billing_date', '<=', now()->addDays(7))->count();

        // ── Inventory ──
        $lowStockItems = Item::lowStock()->count();
        $pendingPOs = PurchaseOrder::whereIn('status', ['draft', 'pending_approval', 'approved'])->count();
        $pendingApprovalPOs = PurchaseOrder::where('status', 'pending_approval')->count();

        // ── Website ──
        $websiteBookingsMonth = Booking::whereMonth('created_at', now()->month)->count();
        $websiteRevenueMonth = Booking::where('payment_status', 'paid')->whereMonth('created_at', now()->month)->sum('total_amount');
        $unreadMessages = ContactMessage::where('status', 'unread')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();

        // ── Users & Activity ──
        $recentUsers = User::latest()->take(5)->get();
        $recentLogins = UserLoginLog::successful()->whereDate('logged_in_at', today())->count();
        $activeUsersToday = UserLoginLog::successful()->whereDate('logged_in_at', today())->distinct('user_id')->count('user_id');
        $recentActivity = UserActivityLog::with('user')->recent()->take(10)->get();
        $failedJobs = DB::table('failed_jobs')->count();

        // ── Critical Alerts (consolidated) ──
        $criticalAlerts = $maintenanceCritical + $tasksOverdue + $lowStockItems + $unreadMessages + $pendingApprovalPOs + $failedJobs;

        return view('admin::dashboard', compact(
            'totalUsers', 'totalRoles', 'totalPermissions', 'activeModules',
            'totalRevenue', 'ytdRevenue', 'revenueThisMonth', 'revenueLastMonth', 'revenueChange', 'revenueDirection',
            'revenueMonths', 'monthlyBanquet', 'monthlyFrontdesk', 'monthlyGym', 'monthlyWebsite',
            'banquetRevenue', 'frontdeskRevenue', 'gymRevenue', 'websiteRevenue', 'maintenanceCost', 'banquetOutstanding',
            'totalRoomUnits', 'occupancyRate', 'occupancyChange',
            'totalEmployees', 'activeEmployees', 'pendingLeaves', 'departments',
            'banquetOrdersTotal', 'banquetOrdersPending', 'banquetOrdersConfirmed', 'banquetOrdersCompleted',
            'pendingEnquiries', 'upcomingEvents', 'enquirySources', 'banquetPaymentMethods',
            'checkedIn', 'reservations', 'todayCheckins', 'todayCheckouts',
            'frontdeskRevenueMonth', 'registrationSources',
            'maintenanceOpen', 'maintenanceCritical', 'maintenanceCompletedMonth', 'maintenanceByDept',
            'latestGenReadings', 'latestWaterReading', 'latestDieselReading',
            'tasksPending', 'tasksInProgress', 'tasksOverdue', 'tasksCompletedMonth', 'highPriorityTasks',
            'restaurantOrdersToday', 'restaurantOrdersPending', 'restaurantOrdersMonth',
            'activeMemberships', 'gymPaymentsMonth', 'membershipDueSoon',
            'lowStockItems', 'pendingPOs', 'pendingApprovalPOs',
            'websiteBookingsMonth', 'websiteRevenueMonth', 'unreadMessages', 'confirmedBookings',
            'criticalAlerts',
            'recentUsers', 'recentLogins', 'activeUsersToday', 'recentActivity', 'failedJobs',
        ));
    }

    public function roles()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $permissionGroups = $this->groupPermissionsByModule($permissions);

        return view('admin::roles.index', compact('roles', 'permissions', 'permissionGroups'));
    }

    private function groupPermissionsByModule($permissions)
    {
        $moduleMap = [
            'Dashboard Access' => ['access_admin_dashboard', 'access_frontdesk_dashboard', 'access_staff_dashboard', 'access_restaurant_dashboard', 'access_gym_dashboard', 'access_inventory_dashboard', 'access_maintenance_dashboard', 'access_tasks_dashboard', 'access_banquet_dashboard', 'access_website_dashboard'],
        ];

        $groups = [];
        $uncategorized = [];

        foreach ($permissions as $perm) {
            $matched = false;

            // Check legacy underscore permissions first
            if (! str_contains($perm->name, '.')) {
                if (str_starts_with($perm->name, 'access_')) {
                    $groups['Dashboard Access'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'manage_users') || str_starts_with($perm->name, 'manage_roles') || str_starts_with($perm->name, 'manage_permissions') || str_starts_with($perm->name, 'manage_settings')) {
                    $groups['Admin'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'check_in') || str_starts_with($perm->name, 'check_out')) {
                    $groups['Front Desk'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'employees.') || str_starts_with($perm->name, 'view_employees') || str_starts_with($perm->name, 'manage_employees')) {
                    $groups['HR & Staff'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'leaves.approve') || str_starts_with($perm->name, 'approve_leaves')) {
                    $groups['HR & Staff'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'view_inventory') || str_starts_with($perm->name, 'adjust_stock') || str_starts_with($perm->name, 'manage_suppliers')) {
                    $groups['Inventory'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'take_orders') || str_starts_with($perm->name, 'manage_menu')) {
                    $groups['Restaurant'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'view_tasks') || str_starts_with($perm->name, 'assign_tasks')) {
                    $groups['Tasks'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'manage_banquet')) {
                    $groups['Banquet'][] = $perm;

                    continue;
                }
                if (str_starts_with($perm->name, 'log_maintenance')) {
                    $groups['Maintenance'][] = $perm;

                    continue;
                }
            }

            // Dot-notation CRUD permissions
            if (str_contains($perm->name, '.')) {
                $prefix = explode('.', $perm->name)[0];
                switch ($prefix) {
                    case 'users': case 'roles': case 'permissions': case 'settings':
                        $group = 'Admin';
                        break;
                    case 'guests':
                        $group = 'Front Desk';
                        break;
                    case 'employees': case 'leaves':
                        $group = 'HR & Staff';
                        break;
                    case 'tasks':
                        $group = 'Tasks';
                        break;
                    case 'inventory': case 'suppliers':
                        $group = 'Inventory';
                        break;
                    case 'orders': case 'menu':
                        $group = 'Restaurant';
                        break;
                    case 'banquet':
                        $group = 'Banquet';
                        break;
                    case 'gym':
                        $group = 'Gym';
                        break;
                    case 'maintenance':
                        $group = 'Maintenance';
                        break;
                    case 'website':
                        $group = 'Website';
                        break;
                    default:
                        $group = 'Other';
                        break;
                }
                $groups[$group][] = $perm;

                continue;
            }

            $uncategorized[] = $perm;
        }

        if (! empty($uncategorized)) {
            $groups['Other'] = $uncategorized;
        }

        return $groups;
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully with '.($request->permissions ? count($request->permissions) : 0).' permissions.');
    }

    // public function editRole($id)
    // {
    //     $role = Role::findOrFail($id);
    //     return view('admin::roles.edit', compact('role'));
    // }

    // public function updateRole(Request $request, $id)
    // {
    //     $role = Role::findOrFail($id);
    //     $request->validate([
    //         'name' => 'required|unique:roles,name,' . $role->id,
    //     ]);
    //     $role->update(['name' => $request->name]);
    //     return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    // }
    public function editRole($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $permissionGroups = $this->groupPermissionsByModule($permissions);

        return view('admin::roles.edit', compact('role', 'permissions', 'permissionGroups'));
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id,
            'permissions' => 'array', // Validate permissions array
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Update Name
        $role->update(['name' => $request->name]);

        // Dynamic: Sync the checked permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            // If no permissions checked, remove all
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role and permissions updated successfully.');
    }

    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    public function massDestroyRole(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id',
        ]);

        // Optional: Prevent deleting the Super Admin role if applicable
        // Role::whereIn('id', $request->ids)->where('name', '!=', 'admin')->delete();

        Role::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Selected roles deleted successfully.');
    }

    public function permissions()
    {
        $permissions = Permission::with('roles')->get();
        $roles = Role::all();

        return view('admin::permissions.index', compact('permissions', 'roles'));
    }

    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'guard_name' => 'nullable|string',
        ]);
        Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$request->name}' created successfully.");
    }

    public function editPermission($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);
        $roles = Role::all();

        return view('admin::permissions.edit', compact('permission', 'roles'));
    }

    public function updatePermission(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $request->validate([
            'name' => 'required|unique:permissions,name,'.$permission->id,
        ]);
        $permission->update(['name' => $request->name]);

        return redirect()->route('admin.permissions.index')->with('success', "Permission updated to '{$request->name}' successfully.");
    }

    public function updatePermissionRoles(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.permissions.edit', $permission->id)->with('success', 'Role assignments updated successfully.');
    }

    public function destroyPermission($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$permission->name}' deleted successfully.");
    }

    public function assignPermissionToRole(Request $request)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);

        if ($role->hasPermissionTo($permission)) {
            return redirect()->route('admin.permissions.index')->with('info', "Role '{$role->name}' already has this permission.");
        }

        $role->givePermissionTo($permission);

        return redirect()->route('admin.permissions.index')->with('success', "Permission '{$permission->name}' assigned to role '{$role->name}'.");
    }

    public function users(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $users = $query->get();
        $roles = Role::all();
        $totalStaff = User::staff()->count();
        $totalGuests = User::guest()->count();
        $staffRoles = Role::where('name', '!=', 'guest')->pluck('name')->toArray();
        $guestRoles = ['guest'];

        return view('admin::users.index', compact('users', 'roles', 'totalStaff', 'totalGuests', 'staffRoles', 'guestRoles'));
    }

    public function editPassword($id)
    {
        $user = User::findOrFail($id);

        return view('admin::users.password', compact('user'));
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->update(['password' => $request->password]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password updated successfully for {$user->name}.");
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'required|array|max:2',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $allowedRoles = $user->isGuest() ? ['guest'] : Role::where('name', '!=', 'guest')->pluck('name')->toArray();

        foreach ($request->roles as $role) {
            if (! in_array($role, $allowedRoles)) {
                return back()->with('error', "Role '{$role}' is not allowed for {$user->type} accounts.");
            }
        }

        $oldRoles = $user->roles->pluck('name')->all();

        $user->syncRoles($request->roles);

        if ($user->isGuest() && in_array('guest', $request->roles)) {
            $user->update(['type' => 'guest']);
        } elseif ($user->isStaff() || ! $user->type) {
            $user->update(['type' => 'staff']);
        }

        $this->recordRoleAudit($user, $oldRoles, $user->roles->pluck('name')->all());

        return redirect()->route('admin.users.index')->with('success', 'Roles assigned successfully.');
    }

    public function createGuest()
    {
        return view('admin::users.create-guest');
    }

    public function storeGuest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'guest',
        ]);

        $user->assignRole('guest');

        return redirect()->route('admin.users.index', ['type' => 'guest'])
            ->with('success', "Guest account created for {$user->name}.");
    }

    public function updateType(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:staff,guest',
        ]);

        $user = User::findOrFail($id);
        $newType = $request->type;

        if ($user->type === $newType) {
            return redirect()->route('admin.users.index')->with('info', "User is already a {$newType} account.");
        }

        $oldRoles = $user->roles->pluck('name')->all();

        if ($newType === 'guest') {
            $user->syncRoles(['guest']);
            $user->update(['type' => 'guest']);
            $msg = "{$user->name} changed to guest account with guest role.";
            $event = 'role-assigned';
        } else {
            $user->removeRole('guest');
            $user->update(['type' => 'staff']);
            $msg = "{$user->name} changed to staff account. Assign roles via the Roles button.";
            $event = 'role-detached';
        }

        $this->recordRoleAudit($user, $oldRoles, $user->roles->pluck('name')->all(), $event);

        return redirect()->route('admin.users.index')->with('success', $msg);
    }

    public function createUserFromEmployee()
    {
        $employees = Employee::whereNull('end_date')
            ->where(fn ($q) => $q->whereNull('user_id')->orWhereDoesntHave('user'))
            ->orderBy('name')
            ->get();

        $roles = Role::all();

        return view('admin::employees.create-user', compact('employees', 'roles'));
    }

    public function storeUserFromEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'email' => ['required', 'email', 'unique:users,email', new ValidEmail],
            'password' => 'required|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($request->employee_id);

            $user = User::create([
                'name' => $employee->name ?? $employee->first_name.' '.($employee->last_name ?? ''),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => 'staff',
            ]);

            $employee->update(['user_id' => $user->id]);

            $user->assignRole($request->role);

            DB::commit();

            // Send login credentials via email (synchronous to catch failures)
            try {
                Mail::to($user->email)->send(new AccountCreated($user, $request->password));

                return redirect()->route('admin.users.index')
                    ->with('success', 'User account created successfully. Login credentials have been sent to '.$user->email.'.');
            } catch (\Exception $e) {
                \Log::error('Failed to send account creation email for user '.$user->id.': '.$e->getMessage());

                return redirect()->route('admin.users.index')
                    ->with('warning', 'User account created successfully, but the credentials email could not be delivered to '.$user->email.'. The user may need to use the "Forgot Password" option or you can resend credentials later.')
                    ->with('email_failed', true);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Error creating user account: '.$e->getMessage())
                ->withInput();
        }
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,deactivated',
            'suspension_reason' => 'required_if:status,suspended,deactivated|nullable|string|max:500',
        ]);

        $data = ['status' => $request->status];

        if ($request->status === 'active') {
            $data['suspended_at'] = null;
            $data['suspension_reason'] = null;
        } else {
            $data['suspended_at'] = now();
            $data['suspension_reason'] = $request->suspension_reason;
        }

        $user->update($data);

        $labels = ['active' => 'activated', 'suspended' => 'suspended', 'deactivated' => 'deactivated'];
        $msg = "{$user->name} has been {$labels[$request->status]}.";

        return redirect()->route('admin.users.index')->with('success', $msg);
    }

    public function resendCredentials(Request $request, User $user)
    {
        if ($user->isGuest()) {
            return back()->with('error', 'Cannot resend credentials for guest accounts.');
        }

        $tempPassword = \Str::random(16);
        $user->update(['password' => bcrypt($tempPassword)]);

        try {
            Mail::to($user->email)->send(new AccountCreated($user, $tempPassword));

            return redirect()->route('admin.users.index')
                ->with('success', "New login credentials have been sent to {$user->email}.");
        } catch (\Exception $e) {
            \Log::error('Failed to resend credentials for user '.$user->id.': '.$e->getMessage());

            return back()
                ->with('error', 'Failed to send credentials email. The mail server may be misconfigured or '.$user->email.' may be invalid. Please verify the email address and try again.');
        }
    }

    public function modules()
    {
        $allModules = Module::all();

        return view('admin::modules.index', compact('allModules'));
    }

    public function toggleModule($name)
    {
        $module = Module::find($name);
        if (! $module) {
            return back()->with('error', "Module '{$name}' not found.");
        }

        if ($module->isEnabled()) {
            $module->disable();

            return back()->with('success', "Module '{$name}' disabled.");
        } else {
            $module->enable();

            return back()->with('success', "Module '{$name}' enabled.");
        }
    }

    public function activityLogs(Request $request)
    {
        $query = UserActivityLog::with('user')->recent();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->paginate(50);
        $actions = UserActivityLog::select('action')->distinct()->pluck('action');
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin::activity-logs.index', compact('logs', 'actions', 'users'));
    }

    public function auditTrails(Request $request)
    {
        $query = Audit::with('user')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($event = $request->input('event')) {
            $query->where('event', $event);
        }

        if ($auditableType = $request->input('auditable_type')) {
            $query->where('auditable_type', $auditableType);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId)
                ->where('user_type', 'App\\Models\\User');
        }

        $audits = $query->paginate(50);
        $events = Audit::select('event')->distinct()->pluck('event');
        $auditableTypes = Audit::select('auditable_type')->distinct()->pluck('auditable_type');

        return view('admin::audit-trails.index', compact('audits', 'events', 'auditableTypes'));
    }

    /**
     * Revert a model to the state captured in an "updated" or "deleted" audit.
     */
    public function restoreAudit(Request $request, $id)
    {
        $audit = Audit::findOrFail($id);

        if (! in_array($audit->event, ['updated', 'deleted'], true)) {
            return back()->with('error', 'Only "updated" or "deleted" records can be restored.');
        }

        $modelClass = $audit->auditable_type;

        if (! class_exists($modelClass)) {
            return back()->with('error', 'The audited model class no longer exists.');
        }

        try {
            $instance = new $modelClass;
            $table = $instance->getTable();
            $keyName = $instance->getKeyName();

            $columns = Schema::getColumnListing($table);
            $old = collect($audit->old_values)
                ->only($columns)
                ->toArray();

            $existing = $this->findAuditable($modelClass, $audit->auditable_id);
            $label = class_basename($modelClass).' #'.$audit->auditable_id;

            if ($existing) {
                $existing->forceFill($old)->save();

                if (method_exists($existing, 'trashed') && $existing->trashed()) {
                    $existing->restore();
                }
            } else {
                $model = new $modelClass;
                $model->forceFill($old);
                $model->{$keyName} = $audit->auditable_id;
                $model->save();
            }

            return back()->with('success', "Restored {$label} to its previous state.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to restore record: '.$e->getMessage());
        }
    }

    /**
     * Resolve an auditable model instance, including soft-deleted rows.
     */
    protected function findAuditable(string $modelClass, $id)
    {
        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            return $modelClass::withTrashed()->find($id);
        }

        return $modelClass::find($id);
    }

    /**
     * Record a role assignment/removal as a custom audit entry on the user.
     *
     * Role changes are written to Spatie's model_has_roles pivot and do not
     * fire the Eloquent events laravel-auditing hooks into, so we dispatch a
     * custom AuditCustom event manually.
     */
    protected function recordRoleAudit(User $user, array $oldRoles, array $newRoles, string $event = 'role-assigned'): void
    {
        if ($oldRoles === $newRoles) {
            return;
        }

        $user->auditEvent = $event;
        $user->auditCustomOld = ['roles' => $oldRoles];
        $user->auditCustomNew = ['roles' => $newRoles];
        $user->isCustomEvent = true;

        event(new AuditCustom($user));

        $user->isCustomEvent = false;
        $user->auditCustomOld = $user->auditCustomNew = null;
    }

    public function appearance()
    {
        $theme = Settings::where('key', 'theme')->value('value') ?? 'gold-legacy';
        $logoSetting = Settings::where('key', 'logo')->value('value');

        return view('admin::appearance', compact('theme', 'logoSetting'));
    }

    public function updateAppearance(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:gold-legacy,platinum-noir,sapphire-regal',
        ]);

        Settings::updateOrCreate(
            ['key' => 'theme'],
            ['value' => $validated['theme'], 'type' => 'string']
        );

        cache()->forget('app.theme');

        return redirect()->route('admin.appearance')
            ->with('success', 'Theme updated successfully.');
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $path = $request->file('logo')->store('settings', 'public');

        Settings::updateOrCreate(
            ['key' => 'logo'],
            ['value' => $path, 'type' => 'image']
        );

        cache()->forget('app.logo');

        return redirect()->route('admin.appearance')
            ->with('success', 'Logo uploaded successfully.');
    }

    public function removeLogo()
    {
        $logo = Settings::where('key', 'logo')->first();
        if ($logo) {
            Storage::disk('public')->delete($logo->value);
            $logo->delete();
            cache()->forget('app.logo');
        }

        return redirect()->route('admin.appearance')
            ->with('success', 'Logo removed successfully.');
    }
}
