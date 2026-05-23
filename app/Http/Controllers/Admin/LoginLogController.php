<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LoginLogController extends Controller
{
    /**
     * Display the login logs dashboard.
     */
    public function index(Request $request)
    {
        // Statistics
        $stats = [
            'total_logins_today' => UserLoginLog::today()->successful()->count(),
            'unique_users_today' => UserLoginLog::today()->successful()->distinct('user_id')->count('user_id'),
            'active_sessions' => UserLoginLog::whereNull('logged_out_at')->where('status', 'success')->count(),
            'failed_logins_today' => UserLoginLog::today()->failed()->count(),
            'total_logins_week' => UserLoginLog::whereBetween('logged_in_at', [now()->startOfWeek(), now()])->successful()->count(),
            'total_logins_month' => UserLoginLog::whereBetween('logged_in_at', [now()->startOfMonth(), now()])->successful()->count(),
        ];

        // Get users for filter dropdown
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.login-logs.index', compact('stats', 'users'));
    }

    /**
     * Get login logs for DataTables.
     */
    public function datatable(Request $request)
    {
        $query = UserLoginLog::with('user')
            ->select('user_login_logs.*')
            ->latest('logged_in_at');

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('logged_in_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('logged_in_at', '<=', $request->date_to);
        }

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        return DataTables::of($query)
            ->addColumn('user_name', function ($log) {
                return $log->user->name ?? 'Unknown';
            })
            ->addColumn('user_email', function ($log) {
                return $log->user->email ?? 'N/A';
            })
            ->addColumn('logged_in_at_formatted', function ($log) {
                return $log->logged_in_at->format('M d, Y h:i A');
            })
            ->addColumn('logged_out_at_formatted', function ($log) {
                return $log->logged_out_at ? $log->logged_out_at->format('M d, Y h:i A') : '<span class="badge bg-success">Active</span>';
            })
            ->addColumn('session_duration', function ($log) {
                if (!$log->logged_out_at) {
                    $minutes = $log->logged_in_at->diffInMinutes(now());
                    return '<span class="text-success">' . $this->formatDuration($minutes) . ' (ongoing)</span>';
                }
                return $this->formatDuration($log->session_duration);
            })
            ->addColumn('device_info', function ($log) {
                $icon = match($log->device_type) {
                    'mobile' => '<i class="fas fa-mobile-alt text-primary"></i>',
                    'tablet' => '<i class="fas fa-tablet-alt text-info"></i>',
                    default => '<i class="fas fa-desktop text-secondary"></i>',
                };
                return $icon . ' ' . ($log->browser ?? 'Unknown') . ' / ' . ($log->platform ?? 'Unknown');
            })
            ->addColumn('status_badge', function ($log) {
                $badgeClass = $log->status === 'success' ? 'bg-success' : 'bg-danger';
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($log->status) . '</span>';
            })
            ->rawColumns(['logged_out_at_formatted', 'session_duration', 'device_info', 'status_badge'])
            ->make(true);
    }

    /**
     * Show login history for a specific user.
     */
    public function userHistory($userId)
    {
        $user = User::findOrFail($userId);
        
        $stats = [
            'total_logins' => UserLoginLog::where('user_id', $userId)->successful()->count(),
            'last_login' => UserLoginLog::where('user_id', $userId)->successful()->latest('logged_in_at')->first()?->logged_in_at,
            'failed_attempts' => UserLoginLog::where('user_id', $userId)->failed()->count(),
            'most_used_browser' => UserLoginLog::where('user_id', $userId)
                ->successful()
                ->select('browser')
                ->groupBy('browser')
                ->orderByRaw('COUNT(*) DESC')
                ->first()?->browser ?? 'N/A',
        ];

        return view('admin.login-logs.user-history', compact('user', 'stats'));
    }

    /**
     * Get active sessions.
     */
    public function activeSessions()
    {
        $sessions = UserLoginLog::with('user')
            ->whereNull('logged_out_at')
            ->where('status', 'success')
            ->latest('logged_in_at')
            ->get();

        return view('admin.login-logs.active-sessions', compact('sessions'));
    }

    /**
     * Export login logs to CSV.
     */
    public function export(Request $request)
    {
        $query = UserLoginLog::with('user')->latest('logged_in_at');

        // Apply same filters as datatable
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('logged_in_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('logged_in_at', '<=', $request->date_to);
        }

        $logs = $query->get();
        $filename = 'login-logs-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header row
            fputcsv($file, [
                'User Name',
                'Email',
                'IP Address',
                'Browser',
                'Platform',
                'Device Type',
                'Status',
                'Login Time',
                'Logout Time',
                'Session Duration (mins)',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->user->name ?? 'Unknown',
                    $log->user->email ?? 'N/A',
                    $log->ip_address,
                    $log->browser,
                    $log->platform,
                    $log->device_type,
                    $log->status,
                    $log->logged_in_at->format('Y-m-d H:i:s'),
                    $log->logged_out_at?->format('Y-m-d H:i:s') ?? 'Active',
                    $log->session_duration ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Format duration in minutes to human-readable string.
     */
    private function formatDuration(?int $minutes): string
    {
        if ($minutes === null) {
            return 'N/A';
        }

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours < 24) {
            return $hours . 'h ' . $mins . 'm';
        }

        $days = floor($hours / 24);
        $hours = $hours % 24;

        return $days . 'd ' . $hours . 'h';
    }
}
