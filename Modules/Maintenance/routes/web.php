<?php

use Illuminate\Support\Facades\Route;
use Modules\Maintenance\Http\Controllers\MaintenanceController;
use Modules\Maintenance\Http\Controllers\ReadingController;

// Public maintenance issue reporting (no auth required)
Route::get('maintenance/public/create', [MaintenanceController::class, 'publicCreate'])->name('maintenance.public.create');
Route::post('maintenance/public', [MaintenanceController::class, 'publicStore'])->name('maintenance.public.store');
Route::get('maintenance/qr', [MaintenanceController::class, 'qrCode'])->name('maintenance.qr');

// Auth routes — explicit routes before parameterized {maintenanceLog}
Route::middleware(['auth'])->group(function () {
    // Staff viewable (no extra permission)
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('maintenance/quick-report', [MaintenanceController::class, 'quickStore'])->name('maintenance.quick-store');

    // Management routes (require permission) — defined BEFORE {maintenanceLog}
    Route::get('maintenance/dashboard', [MaintenanceController::class, 'dashboard'])->name('maintenance.dashboard')->middleware('permission:access_maintenance_dashboard|maintenance.read|maintenance.create|maintenance.update|maintenance.delete');
    Route::get('maintenance/report', [MaintenanceController::class, 'report'])->name('maintenance.report')->middleware('permission:access_maintenance_dashboard|maintenance.read');
    Route::post('maintenance/report/export', [MaintenanceController::class, 'exportReport'])->name('maintenance.report.export')->middleware('permission:access_maintenance_dashboard|maintenance.read');
    Route::get('maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create')->middleware('permission:access_maintenance_dashboard|maintenance.create');
    Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store')->middleware('permission:access_maintenance_dashboard|maintenance.create');
    Route::get('maintenance/{maintenanceLog}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit')->middleware('permission:access_maintenance_dashboard|maintenance.update');
    Route::put('maintenance/{maintenanceLog}', [MaintenanceController::class, 'update'])->name('maintenance.update')->middleware('permission:access_maintenance_dashboard|maintenance.update');
    Route::delete('maintenance/{maintenanceLog}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy')->middleware('permission:access_maintenance_dashboard|maintenance.delete');
    Route::patch('maintenance/{maintenanceLog}/status', [MaintenanceController::class, 'toggleStatus'])->name('maintenance.toggle-status')->middleware('permission:access_maintenance_dashboard|maintenance.update');

    // Daily Readings (generator, diesel, water, cold room)
    Route::get('maintenance/readings', [ReadingController::class, 'index'])->name('maintenance.readings.index');
    Route::get('maintenance/readings/create', [ReadingController::class, 'create'])->name('maintenance.readings.create');
    Route::post('maintenance/readings', [ReadingController::class, 'store'])->name('maintenance.readings.store');
    Route::post('maintenance/readings/export', [ReadingController::class, 'exportReport'])->name('maintenance.readings.export');
    Route::post('maintenance/readings/export-excel', [ReadingController::class, 'exportExcel'])->name('maintenance.readings.export-excel');
    Route::get('maintenance/readings/{date}', [ReadingController::class, 'show'])->name('maintenance.readings.show');
    Route::get('maintenance/readings/{id}/edit', [ReadingController::class, 'edit'])->name('maintenance.readings.edit');
    Route::put('maintenance/readings/{id}', [ReadingController::class, 'update'])->name('maintenance.readings.update');
    Route::delete('maintenance/readings/{id}', [ReadingController::class, 'destroy'])->name('maintenance.readings.destroy');

    // Parameterized route LAST — only catches unmatched paths
    Route::get('maintenance/{maintenanceLog}', [MaintenanceController::class, 'show'])->name('maintenance.show');
});
