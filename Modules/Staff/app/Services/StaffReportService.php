<?php

namespace Modules\Staff\Services;

use Carbon\Carbon;
use Modules\Staff\Models\AttendanceLog;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveRequest;

class StaffReportService
{
    public function headcountTrend(int $year): array
    {
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd = Carbon::create($year, 12, 31);

        $counts = Employee::where('status', 'approved')
            ->where('start_date', '<=', $yearEnd)
            ->where(function ($q) use ($yearStart) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $yearStart);
            })
            ->get()
            ->flatMap(function ($employee) use ($year) {
                $startMonth = max(1, $employee->start_date->year === $year ? $employee->start_date->month : 1);
                $endMonth = $employee->end_date && $employee->end_date->year === $year
                    ? $employee->end_date->month
                    : 12;

                return range($startMonth, $endMonth);
            })
            ->countBy()
            ->toArray();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = (object) [
                'month' => Carbon::create($year, $m)->format('M'),
                'count' => $counts[$m] ?? 0,
            ];
        }

        return $months;
    }

    public function turnoverData(int $year): array
    {
        $hiresByMonth = Employee::where('status', 'approved')
            ->whereYear('start_date', $year)
            ->selectRaw('MONTH(start_date) as m, COUNT(*) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $departuresByMonth = Employee::whereNotNull('end_date')
            ->whereYear('end_date', $year)
            ->selectRaw('MONTH(end_date) as m, COUNT(*) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $yearStart = Carbon::create($year, 1, 1);

        $allActive = Employee::where('status', 'approved')
            ->where('start_date', '<=', Carbon::create($year, 12, 31))
            ->where(function ($q) use ($yearStart) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $yearStart);
            })
            ->get();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = Carbon::create($year, $m)->startOfMonth();
            $monthEnd = Carbon::create($year, $m)->endOfMonth();

            $hires = $hiresByMonth->get($m, 0);
            $departures = $departuresByMonth->get($m, 0);

            $startCount = $allActive->filter(fn ($e) => $e->start_date <= $monthStart && (! $e->end_date || $e->end_date >= $monthStart))->count();
            $endCount = $allActive->filter(fn ($e) => $e->start_date <= $monthEnd && (! $e->end_date || $e->end_date >= $monthEnd))->count();

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

    public function absenteeismByDept(int $year): array
    {
        $result = AttendanceLog::query()
            ->join('employees', 'attendance_logs.employee_id', '=', 'employees.id')
            ->whereYear('attendance_logs.date', $year)
            ->where('employees.status', 'approved')
            ->whereNotNull('employees.department')
            ->selectRaw('
                employees.department,
                COUNT(DISTINCT employees.id) as employee_count,
                COUNT(*) as total_logs,
                SUM(CASE WHEN attendance_logs.status = ? THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN attendance_logs.status = ? THEN 1 ELSE 0 END) as late
            ', ['absent', 'late'])
            ->groupBy('employees.department')
            ->get()
            ->map(function ($row) {
                $row->absenteeism_rate = $row->total_logs > 0
                    ? round((($row->absent + $row->late) / $row->total_logs) * 100, 1)
                    : 0;

                return $row;
            });

        return $result->toArray();
    }

    public function leaveUtilization(int $year): object
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
