<?php

use Illuminate\Support\Facades\Route;
use Modules\Maintenance\Http\Controllers\MaintenanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Show all maintenance logs
Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');

// Show form to create a new maintenance log
Route::get('maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');

// Store a new maintenance log
Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');

// Show a specific maintenance log
Route::get('maintenance/{maintenanceLog}', [MaintenanceController::class, 'show'])->name('maintenance.show');

// Show form to edit a specific maintenance log
Route::get('maintenance/{maintenanceLog}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');

// Update a specific maintenance log
Route::put('maintenance/{maintenanceLog}', [MaintenanceController::class, 'update'])->name('maintenance.update');

// Delete a specific maintenance log
Route::delete('maintenance/{maintenanceLog}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');