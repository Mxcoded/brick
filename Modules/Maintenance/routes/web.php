<?php

use Illuminate\Support\Facades\Route;
use Modules\Maintenance\Http\Controllers\MaintenanceController;

// Public maintenance issue reporting (no auth required)
Route::get('maintenance/public/create', [MaintenanceController::class, 'publicCreate'])->name('maintenance.public.create');
Route::post('maintenance/public', [MaintenanceController::class, 'publicStore'])->name('maintenance.public.store');

// Auth routes — explicit routes before parameterized {maintenanceLog}
Route::middleware(['auth'])->group(function () {
    // Staff viewable (no extra permission)
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');

    // Management routes (require permission) — defined BEFORE {maintenanceLog}
    Route::get('maintenance/dashboard', [MaintenanceController::class, 'dashboard'])->name('maintenance.dashboard')->middleware('can:access_maintenance_dashboard');
    Route::get('maintenance/report', [MaintenanceController::class, 'report'])->name('maintenance.report')->middleware('can:access_maintenance_dashboard');
    Route::post('maintenance/report/export', [MaintenanceController::class, 'exportReport'])->name('maintenance.report.export')->middleware('can:access_maintenance_dashboard');
    Route::get('maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create')->middleware('can:access_maintenance_dashboard');
    Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store')->middleware('can:access_maintenance_dashboard');
    Route::get('maintenance/{maintenanceLog}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit')->middleware('can:access_maintenance_dashboard');
    Route::put('maintenance/{maintenanceLog}', [MaintenanceController::class, 'update'])->name('maintenance.update')->middleware('can:access_maintenance_dashboard');
    Route::delete('maintenance/{maintenanceLog}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy')->middleware('can:access_maintenance_dashboard');
    Route::patch('maintenance/{maintenanceLog}/status', [MaintenanceController::class, 'toggleStatus'])->name('maintenance.toggle-status')->middleware('can:access_maintenance_dashboard');

    // Parameterized route LAST — only catches unmatched paths
    Route::get('maintenance/{maintenanceLog}', [MaintenanceController::class, 'show'])->name('maintenance.show');
});
