<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\TasksController;

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

// Route::prefix('tasks')->middleware(['auth'])->group(function () {
//     Route::get('/', [TasksController::class, 'index'])->name('tasks.index');
//     Route::get('/create', [TasksController::class, 'create'])->name('tasks.create');
//     Route::post('/', [TasksController::class, 'store'])->name('tasks.store');
//     Route::get('/{id}/edit', [TasksController::class, 'edit'])->name('tasks.edit');
//     Route::put('/{id}', [TasksController::class, 'update'])->name('tasks.update');
//     Route::put('/{id}/evaluate', [TasksController::class, 'evaluate'])->name('tasks.evaluate');
// });
Route::prefix('tasks')->middleware(['auth'])->group(function () {
    Route::resource('/', TasksController::class)
        ->parameters(['' => 'task'])
        ->names('tasks'); // This prefixes all resource route names with "tasks."
    Route::put('/{task}/evaluate', [TasksController::class, 'evaluate'])->name('tasks.evaluate');
});
