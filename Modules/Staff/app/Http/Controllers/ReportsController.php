<?php

namespace Modules\Staff\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Staff\Models\AttendanceLog;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveRequest;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);

        $headcountTrend = $this->headcountTrend($year);
        $turnoverData = $this->turnoverData($year);
        $absenteeismByDept = $this->absenteeismByDept($year);
        $leaveUtilization = $this->leaveUtilization($year);
        $years = range(now()->year - 5, now()->year);

        return view('staff::reports.index', compact(
            'year', 'years', 'headcountTrend', 'turnoverData', 'absenteeismByDept', 'leaveUtilization'
        ));
    }

    private function headcountTrend($year)
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = Carbon::create($year, $m)->startOfMonth();
            $monthEnd = Carbon::create($year, $m)->endOfMonth();

            $count = Employee::where('status', 'approved')
                ->where('start_date', '<=', $monthEnd)
                ->where(function ($q) use ($monthStart) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $monthStart);
                })
                ->count();

            $months[] = (object) [
                'month' => $monthStart->format('M'),
                'count' => $count,
            ];
        }

        return $months;
    }

    private function turnoverData($year)
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = Carbon::create($year, $m)->startOfMonth();
            $monthEnd = Carbon::create($year, $m)->endOfMonth();

            $hires = Employee::where('status', 'approved')
                ->whereBetween('start_date', [$monthStart, $monthEnd])
                ->count();

            $departures = Employee::whereNotNull('end_date')
                ->whereBetween('end_date', [$monthStart, $monthEnd])
                ->count();

            $startCount = Employee::where('status', 'approved')
                ->where('start_date', '<=', $monthStart)
                ->where(function ($q) use ($monthStart) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $monthStart);
                })
                ->count();

            $endCount = Employee::where('status', 'approved')
                ->where('start_date', '<=', $monthEnd)
                ->where(function ($q) use ($monthEnd) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $monthEnd);
                })
                ->count();

            $avgHeadcount = $startCount > 0 && $endCount > 0 ? ($startCount + $endCount) / 2 : max($startCount, $endCount);
            $turnoverRate = $avgHeadcount > 0 ? round(($departures / $avgHeadcount) * 100, 1) : 0;

            $months[] = (object) [
                'month' => $monthStart->format('M'),
                'hires' => $hires,
                'departures' => $departures,
                'avg_headcount' => round($avgHeadcount, 1),
                'turnover_rate' => $turnoverRate,
            ];
        }

        return $months;
    }

    private function absenteeismByDept($year)
    {
        $departments = Employee::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department');

        $result = [];
        foreach ($departments as $dept) {
            $employeeIds = Employee::where('department', $dept)
                ->where('status', 'approved')
                ->pluck('id');

            $employeeCount = $employeeIds->count();
            if ($employeeCount === 0) {
                continue;
            }

            $totalLogs = AttendanceLog::whereIn('employee_id', $employeeIds)
                ->whereYear('date', $year)
                ->count();

            $absentLogs = AttendanceLog::whereIn('employee_id', $employeeIds)
                ->whereYear('date', $year)
                ->where('status', 'absent')
                ->count();

            $lateLogs = AttendanceLog::whereIn('employee_id', $employeeIds)
                ->whereYear('date', $year)
                ->where('status', 'late')
                ->count();

            $result[] = (object) [
                'department' => $dept,
                'employee_count' => $employeeCount,
                'total_logs' => $totalLogs,
                'absent' => $absentLogs,
                'late' => $lateLogs,
                'absenteeism_rate' => $totalLogs > 0
                    ? round((($absentLogs + $lateLogs) / $totalLogs) * 100, 1)
                    : 0,
            ];
        }

        return $result;
    }

    private function leaveUtilization($year)
    {
        $byType = LeaveRequest::where('status', 'approved')
            ->whereYear('start_date', $year)
            ->selectRaw('leave_type, count(*) as request_count, sum(days_count) as total_days')
            ->groupBy('leave_type')
            ->get();

        $byDepartment = LeaveRequest::where('leave_requests.status', 'approved')
            ->whereYear('leave_requests.start_date', $year)
            ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->selectRaw('employees.department, count(*) as request_count, sum(leave_requests.days_count) as total_days')
            ->whereNotNull('employees.department')
            ->groupBy('employees.department')
            ->get();

        $totals = (object) [
            'total_requests' => $byType->sum('request_count'),
            'total_days' => $byType->sum('total_days'),
        ];

        return (object) compact('byType', 'byDepartment', 'totals');
    }
}
