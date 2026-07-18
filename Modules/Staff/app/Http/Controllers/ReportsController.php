<?php

namespace Modules\Staff\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Staff\Services\StaffReportService;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $reportService = app(StaffReportService::class);

        $headcountTrend = $reportService->headcountTrend($year);
        $turnoverData = $reportService->turnoverData($year);
        $absenteeismByDept = $reportService->absenteeismByDept($year);
        $leaveUtilization = $reportService->leaveUtilization($year);
        $years = range(now()->year - 5, now()->year);

        return view('staff::reports.index', compact(
            'year', 'years', 'headcountTrend', 'turnoverData', 'absenteeismByDept', 'leaveUtilization'
        ));
    }
}
