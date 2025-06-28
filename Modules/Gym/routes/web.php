<?php

use Illuminate\Support\Facades\Route;
use Modules\Gym\Http\Controllers\GymController;

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

// Route::prefix('gym')->middleware(['web', 'auth'])->group(
//     function () {
//         Route::group([], function () {
//             Route::resource('gym', GymController::class)->names('gym');
//         });
//     }
// );
Route::prefix('gym')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [GymController::class, 'index'])->name('gym.index');
    Route::get('memberships/create', [GymController::class, 'create'])->name('gym.memberships.create');
    Route::get('membership/show/{id}', [GymController::class, 'show'])->name('gym.memberships.show');
    Route::get('membership/edit/{id}', [GymController::class, 'edit'])->name('gym.memberships.edit');
    Route::post('memberships', [GymController::class, 'store'])->name('gym.memberships.store');
    Route::put('memberships/{id}', [GymController::class, 'update'])->name('gym.memberships.update');
    Route::delete('memberships/{id}', [GymController::class, 'destroy'])->name('gym.memberships.delete');
    //Payment tracking routes
    Route::get('memberships/{membership}/payments/create', [GymController::class, 'createMemberPayment'])->name('gym.memberships.payments.create');
    Route::post('memberships/{membership}/payments', [GymController::class, 'storeMemberPayment'])->name('gym.memberships.payments.store');
    Route::get('memberships/{membership}/trainer-payments/create', [GymController::class, 'createTrainerPayment'])->name('gym.memberships.trainer-payments.create');
    Route::post('memberships/{membership}/trainer-payments', [GymController::class, 'storeTrainerPayment'])->name('gym.memberships.trainer-payments.store');
    //Trainers Route
    Route::get('trainers/', [GymController::class, 'indexTrainer'])->name('gym.trainers.index');
    Route::get('trainers/create', [GymController::class, 'createTrainer'])->name('gym.trainers.create');
    Route::post('trainers', [GymController::class, 'storeTrainer'])->name('gym.trainers.store');
    Route::get('trainers/edit/{id}', [GymController::class, 'editTrainer'])->name('gym.trainers.edit');
    Route::get('trainers/show/{id}', [GymController::class, 'showTrainer'])->name('gym.trainers.show');
    Route::put('trainers/{id}', [GymController::class, 'updateTrainer'])->name('gym.trainers.update');
    Route::delete('trainers/{id}', [GymController::class, 'showTrainer'])->name('gym.trainers.delete');

    //Subscription Settings
    Route::get('subscription-config/edit', [GymController::class, 'editSubscriptionConfig'])->name('gym.subscription-config.edit');
    Route::put('subscription-config', [GymController::class, 'updateSubscriptionConfig'])->name('gym.subscription-config.update');
});
