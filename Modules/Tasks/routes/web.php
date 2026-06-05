<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\TasksController;
use Illuminate\Support\Facades\Auth;

Route::prefix('tasks')->middleware(['auth', 'can:access_tasks_dashboard'])->group(function () {
    Route::get('/', [TasksController::class, 'index'])->name('tasks.index');
    Route::get('/create', [TasksController::class, 'create'])->name('tasks.create');
    Route::post('/', [TasksController::class, 'store'])->name('tasks.store');
    Route::get('/{task}', [TasksController::class, 'show'])->name('tasks.show');
    Route::get('/{task}/edit', [TasksController::class, 'edit'])->name('tasks.edit');
    Route::put('/{task}', [TasksController::class, 'update'])->name('tasks.update');
    Route::patch('/{task}/toggle-complete', [TasksController::class, 'toggleComplete'])->name('tasks.toggle-complete');
    Route::patch('/{task}/status', [TasksController::class, 'setStatus'])->name('tasks.status');
    Route::delete('/{task}', [TasksController::class, 'destroy'])->name('tasks.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', function () {
        return Auth::user()->unreadNotifications;
    })->name('notifications.index');
    Route::post('/notifications/{id}/read', function ($id) {
        Auth::user()->notifications()->findOrFail($id)->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.read');
});
