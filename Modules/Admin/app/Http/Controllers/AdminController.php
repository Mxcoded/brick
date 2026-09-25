<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Banquet\Models\BanquetEnquiry;
use Modules\Banquet\Models\BanquetOrder;
use Modules\Banquet\Models\BanquetPayment;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\Registration;
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
use Nwidart\Modules\Facades\Module;
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
                    case 'finance':
                        $group = 'Finance';
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
}
