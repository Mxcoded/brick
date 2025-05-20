<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\TasksController;
use Illuminate\Support\Facades\Auth;



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

Route::prefix('tasks')->middleware(['auth'])->group(function () {
    Route::resource('/', TasksController::class)->parameters(['' => 'task'])->names('tasks');
    Route::put('/{task}/evaluate', [TasksController::class, 'evaluate'])->name('tasks.evaluate');
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
