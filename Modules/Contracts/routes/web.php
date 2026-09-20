<?php

use Illuminate\Support\Facades\Route;
use Modules\Contracts\Http\Controllers\ContractsController;
use Modules\Contracts\Http\Controllers\TemplateController;

/*
|--------------------------------------------------------------------------
| Contracts Module Web Routes
|--------------------------------------------------------------------------
| Pattern: /contracts
| Name Prefix: contracts.
*/

Route::prefix('contracts')
    ->middleware(['web', 'auth', 'can:access_contracts_dashboard'])
    ->name('contracts.')
    ->group(function () {

        // Dashboard
        Route::get('/', [ContractsController::class, 'index'])->name('dashboard');

        // ==========================================================
        // AGREEMENTS
        // ==========================================================
        Route::prefix('agreements')->name('agreements.')->group(function () {
            Route::get('/', [ContractsController::class, 'agreementsIndex'])->name('index');
            Route::get('/datatable', [ContractsController::class, 'datatable'])->name('datatable');

            Route::get('/create', [ContractsController::class, 'create'])->name('create')->middleware('can:contracts.create');
            Route::post('/', [ContractsController::class, 'store'])->name('store')->middleware('can:contracts.create');

            Route::get('/{agreement}', [ContractsController::class, 'show'])->name('show');
            Route::get('/{agreement}/edit', [ContractsController::class, 'edit'])->name('edit')->middleware('can:contracts.update');
            Route::put('/{agreement}', [ContractsController::class, 'update'])->name('update')->middleware('can:contracts.update');
            Route::delete('/{agreement}', [ContractsController::class, 'destroy'])->name('destroy')->middleware('can:contracts.delete');

            Route::post('/{agreement}/status', [ContractsController::class, 'transition'])->name('status')->middleware('can:contracts.update');
            Route::post('/{agreement}/signatures', [ContractsController::class, 'storeSignature'])->name('signatures.store')->middleware('can:contracts.sign');
            Route::post('/{agreement}/obligations', [ContractsController::class, 'storeObligation'])->name('obligations.store')->middleware('can:contracts.update');
            Route::patch('/{agreement}/obligations/{obligation}', [ContractsController::class, 'updateObligation'])->name('obligations.update')->middleware('can:contracts.update');
            Route::post('/{agreement}/amendments', [ContractsController::class, 'storeAmendment'])->name('amendments.store')->middleware('can:contracts.update');
            Route::post('/{agreement}/documents', [ContractsController::class, 'storeDocument'])->name('documents.store')->middleware('can:contracts.update');
            Route::get('/{agreement}/documents/{document}/download', [ContractsController::class, 'downloadDocument'])->name('documents.download');
        });

        // ==========================================================
        // AGREEMENT TEMPLATES
        // ==========================================================
        Route::prefix('templates')->name('templates.')->middleware('can:contracts.manage_templates')->group(function () {
            Route::get('/', [TemplateController::class, 'index'])->name('index');
            Route::get('/datatable', [TemplateController::class, 'datatable'])->name('datatable');
            Route::get('/create', [TemplateController::class, 'create'])->name('create');
            Route::post('/', [TemplateController::class, 'store'])->name('store');
            Route::get('/{template}', [TemplateController::class, 'show'])->name('show');
            Route::get('/{template}/edit', [TemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [TemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
        });
    });
