<?php

namespace Modules\Staff\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Staff\Models\AttendanceLog;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\ShiftAssignment;
use Modules\Staff\Services\HikvisionService;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : now()->today();
        $department = $request->department;

        $employees = Employee::where('status', 'approved')
            ->whereNull('end_date')
            ->when($department, fn ($q) => $q->where('department', $department))
            ->with(['attendanceLogs' => fn ($q) => $q->whereDate('date', $date)])
            ->with(['shiftAssignments' => fn ($q) => $q->whereDate('date', $date)->with('shift')])
            ->get();

        $departments = Employee::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department')
            ->sort();

        $todayStats = [
            'present' => AttendanceLog::whereDate('date', $date)->whereIn('status', ['present', 'late'])->count(),
            'late' => AttendanceLog::whereDate('date', $date)->where('status', 'late')->count(),
            'absent' => AttendanceLog::whereDate('date', $date)->where('status', 'absent')->count(),
            'on_leave' => AttendanceLog::whereDate('date', $date)->where('status', 'on_leave')->count(),
            'no_record' => 0,
        ];
        $todayStats['no_record'] = $employees->count()
            - $todayStats['present']
            - $todayStats['absent']
            - $todayStats['on_leave'];

        return view('staff::attendance.index', compact(
            'employees', 'date', 'department', 'departments', 'todayStats'
        ));
    }

    public function clockInForm()
    {
        $employee = Auth::user()?->employee;

        if (! $employee) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'No employee record linked to your account.');
        }

        $today = now()->today();
        $assignment = ShiftAssignment::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->with('shift')
            ->first();

        $attendance = AttendanceLog::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        return view('staff::attendance.clock', compact('employee', 'assignment', 'attendance'));
    }

    public function clockIn(Request $request)
    {
        $employee = Auth::user()?->employee;

        if (! $employee) {
            return back()->with('error', 'No employee record linked to your account.');
        }

        $today = now()->today();

        $existing = AttendanceLog::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->clock_in) {
            return back()->with('warning', 'You have already clocked in today.');
        }

        $now = now();
        $assignment = ShiftAssignment::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->with('shift')
            ->first();

        $status = 'present';
        $lateMinutes = 0;

        if ($assignment && $assignment->shift) {
            $shiftStart = Carbon::parse($today.' '.$assignment->shift->start_time);
            $graceEnd = $shiftStart->copy()->addMinutes($assignment->shift->grace_minutes);

            if ($now->greaterThan($graceEnd)) {
                $lateMinutes = (int) $shiftStart->diffInMinutes($now);
                $status = 'late';
            }
        }

        AttendanceLog::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'shift_assignment_id' => $assignment?->id,
                'clock_in' => $now,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'clock_in_note' => $request->note,
            ]
        );

        return redirect()->route('staff.attendance.clock')
            ->with('success', 'Clocked in successfully at '.$now->format('h:i A').'.');
    }

    public function clockOut(Request $request)
    {
        $employee = Auth::user()?->employee;

        if (! $employee) {
            return back()->with('error', 'No employee record linked to your account.');
        }

        $today = now()->today();

        $attendance = AttendanceLog::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance || ! $attendance->clock_in) {
            return back()->with('error', 'You must clock in first before clocking out.');
        }

        if ($attendance->clock_out) {
            return back()->with('warning', 'You have already clocked out today.');
        }

        $now = now();
        $attendance->update([
            'clock_out' => $now,
            'clock_out_note' => $request->note,
        ]);

        return redirect()->route('staff.attendance.clock')
            ->with('success', 'Clocked out successfully at '.$now->format('h:i A').'.');
    }

    public function report(Request $request)
    {
        $month = (int) ($request->month ?? now()->month);
        $year = (int) ($request->year ?? now()->year);

        // Query params arrive as strings; clamp to valid ranges before
        // handing them to Carbon (setYear/setMonth reject strings).
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $department = $request->department;

        $employees = Employee::where('status', 'approved')
            ->whereNull('end_date')
            ->when($department, fn ($q) => $q->where('department', $department))
            ->with(['attendanceLogs' => function ($q) use ($month, $year) {
                $q->whereMonth('date', $month)->whereYear('date', $year);
            }])
            ->get()
            ->map(function ($employee) use ($month, $year) {
                $logs = $employee->attendanceLogs;
                $present = $logs->whereIn('status', ['present', 'late'])->count();
                $late = $logs->where('status', 'late')->count();
                $absent = $logs->where('status', 'absent')->count();
                $onLeave = $logs->where('status', 'on_leave')->count();
                $totalLateMinutes = $logs->sum('late_minutes');
                $totalOvertimeMinutes = $logs->sum('overtime_minutes');
                $workingDays = now()->setYear($year)->setMonth($month)->daysInMonth;

                return (object) [
                    'employee' => $employee,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'on_leave' => $onLeave,
                    'late_minutes' => $totalLateMinutes,
                    'overtime_minutes' => $totalOvertimeMinutes,
                    'working_days' => $workingDays,
                    'attendance_rate' => $workingDays > 0 ? round(($present / $workingDays) * 100, 1) : 0,
                ];
            });

        $departments = Employee::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct('department')
            ->pluck('department')
            ->sort();

        $summary = (object) [
            'total_employees' => $employees->count(),
            'total_present' => $employees->sum('present'),
            'total_absent' => $employees->sum('absent'),
            'total_late' => $employees->sum('late'),
            'avg_attendance_rate' => $employees->count() > 0
                ? round($employees->avg('attendance_rate'), 1) : 0,
            'total_late_minutes' => $employees->sum('late_minutes'),
            'total_overtime_minutes' => $employees->sum('overtime_minutes'),
        ];

        return view('staff::attendance.report', compact(
            'employees', 'summary', 'month', 'year', 'department', 'departments'
        ));
    }

    public function hikvisionWebhook(Request $request, HikvisionService $hikvision)
    {
        if (! $hikvision->isConfigured()) {
            return response()->json(['error' => 'Hikvision not configured'], 503);
        }

        $payload = $request->all();

        Log::info('Hikvision webhook received', ['payload' => $payload]);

        $records = collect();

        $rawRecords = $payload['AttendanceRecord'] ?? $payload['records'] ?? [$payload];

        foreach (is_array($rawRecords) ? $rawRecords : [$rawRecords] as $item) {
            $uid = $item['uid'] ?? $item['recordId'] ?? uniqid('hik_webhook_', true);
            $pin = $item['employeeId'] ?? $item['pin'] ?? '';
            $timeRaw = $item['time'] ?? $item['punchTime'] ?? null;
            $status = $item['status'] ?? '';

            if (! $timeRaw) {
                continue;
            }

            $records->push([
                'uid' => (string) $uid,
                'pin' => (string) $pin,
                'time' => Carbon::parse($timeRaw),
                'status' => (string) $status,
            ]);
        }

        $result = $hikvision->importFetchedRecords($records);

        return response()->json([
            'success' => true,
            'imported' => $result['imported'],
            'matched' => $result['matched_employees'],
        ]);
    }

    public function hikvisionTest(Request $request)
    {
        $ip = $request->input('ip');
        $username = $request->input('username', 'admin');
        $password = $request->input('password');
        $port = (int) $request->input('port', 80);
        $timeout = (int) $request->input('timeout', 30);

        if (empty($ip) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'IP and password are required.']);
        }

        $url = "http://{$ip}:{$port}/ISAPI/System/deviceInfo";

        try {
            $response = Http::timeout($timeout)
                ->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_0,
                        CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=0',
                    ],
                ])
                ->withDigestAuth($username, $password)
                ->get($url);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Connection successful!']);
            }

            if ($response->status() === 401) {
                return response()->json(['success' => false, 'message' => 'Authentication failed. Check username/password.']);
            }

            return response()->json(['success' => false, 'message' => "HTTP {$response->status()}"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
