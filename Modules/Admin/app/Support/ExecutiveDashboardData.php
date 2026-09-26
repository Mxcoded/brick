<?php

namespace Modules\Admin\Support;

use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserLoginLog;
use Illuminate\Support\Facades\DB;
use Modules\Banquet\Models\BanquetEnquiry;
use Modules\Banquet\Models\BanquetOrder;
use Modules\Banquet\Models\BanquetPayment;
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

class ExecutiveDashboardData
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(): array
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
            $revenueMonths[] = $month->format('M');
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

        // ── Frontdesk ──
        $frontdeskRevenueMonth = Registration::whereIn('stay_status', ['checked_out', 'checked_in'])->whereMonth('created_at', now()->month)->sum('total_amount');
        $reservations = Registration::where('stay_status', 'reserved')->count();
        $todayCheckins = Registration::whereDate('check_in', today())->count();
        $todayCheckouts = Registration::whereDate('check_out', today())->count();

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

        return [
            'totalUsers' => $totalUsers,
            'totalRoles' => $totalRoles,
            'totalPermissions' => $totalPermissions,
            'activeModules' => $activeModules,
            'totalRevenue' => $totalRevenue,
            'ytdRevenue' => $ytdRevenue,
            'revenueThisMonth' => $revenueThisMonth,
            'revenueLastMonth' => $revenueLastMonth,
            'revenueChange' => $revenueChange,
            'revenueDirection' => $revenueDirection,
            'revenueMonths' => $revenueMonths,
            'monthlyBanquet' => $monthlyBanquet,
            'monthlyFrontdesk' => $monthlyFrontdesk,
            'monthlyGym' => $monthlyGym,
            'monthlyWebsite' => $monthlyWebsite,
            'banquetRevenue' => $banquetRevenue,
            'frontdeskRevenue' => $frontdeskRevenue,
            'gymRevenue' => $gymRevenue,
            'websiteRevenue' => $websiteRevenue,
            'maintenanceCost' => $maintenanceCost,
            'banquetOutstanding' => $banquetOutstanding,
            'totalRoomUnits' => $totalRoomUnits,
            'occupancyRate' => $occupancyRate,
            'occupancyChange' => $occupancyChange,
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'pendingLeaves' => $pendingLeaves,
            'departments' => $departments,
            'banquetOrdersTotal' => $banquetOrdersTotal,
            'banquetOrdersPending' => $banquetOrdersPending,
            'banquetOrdersConfirmed' => $banquetOrdersConfirmed,
            'banquetOrdersCompleted' => $banquetOrdersCompleted,
            'pendingEnquiries' => $pendingEnquiries,
            'upcomingEvents' => $upcomingEvents,
            'checkedIn' => $checkedIn,
            'reservations' => $reservations,
            'todayCheckins' => $todayCheckins,
            'todayCheckouts' => $todayCheckouts,
            'frontdeskRevenueMonth' => $frontdeskRevenueMonth,
            'maintenanceOpen' => $maintenanceOpen,
            'maintenanceCritical' => $maintenanceCritical,
            'maintenanceCompletedMonth' => $maintenanceCompletedMonth,
            'maintenanceByDept' => $maintenanceByDept,
            'latestGenReadings' => $latestGenReadings,
            'latestWaterReading' => $latestWaterReading,
            'latestDieselReading' => $latestDieselReading,
            'tasksPending' => $tasksPending,
            'tasksInProgress' => $tasksInProgress,
            'tasksOverdue' => $tasksOverdue,
            'tasksCompletedMonth' => $tasksCompletedMonth,
            'highPriorityTasks' => $highPriorityTasks,
            'restaurantOrdersToday' => $restaurantOrdersToday,
            'restaurantOrdersPending' => $restaurantOrdersPending,
            'restaurantOrdersMonth' => $restaurantOrdersMonth,
            'activeMemberships' => $activeMemberships,
            'gymPaymentsMonth' => $gymPaymentsMonth,
            'membershipDueSoon' => $membershipDueSoon,
            'lowStockItems' => $lowStockItems,
            'pendingPOs' => $pendingPOs,
            'pendingApprovalPOs' => $pendingApprovalPOs,
            'websiteBookingsMonth' => $websiteBookingsMonth,
            'websiteRevenueMonth' => $websiteRevenueMonth,
            'unreadMessages' => $unreadMessages,
            'confirmedBookings' => $confirmedBookings,
            'criticalAlerts' => $criticalAlerts,
            'recentUsers' => $recentUsers,
            'recentLogins' => $recentLogins,
            'activeUsersToday' => $activeUsersToday,
            'recentActivity' => $recentActivity,
            'failedJobs' => $failedJobs,
        ];
    }
}
