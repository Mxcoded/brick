<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\FinanceController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('finance', [FinanceController::class, 'index'])
        ->name('finance.index')->middleware('can:finance.view_reports');

    Route::get('finance/coa', [FinanceController::class, 'coaIndex'])
        ->name('finance.coa.index')->middleware('can:finance.view_coa');
    Route::get('finance/coa/create', [FinanceController::class, 'coaCreate'])
        ->name('finance.coa.create')->middleware('can:finance.manage_coa');
    Route::post('finance/coa', [FinanceController::class, 'coaStore'])
        ->name('finance.coa.store')->middleware('can:finance.manage_coa');
    Route::get('finance/coa/{chartOfAccount}/edit', [FinanceController::class, 'coaEdit'])
        ->name('finance.coa.edit')->middleware('can:finance.manage_coa');
    Route::put('finance/coa/{chartOfAccount}', [FinanceController::class, 'coaUpdate'])
        ->name('finance.coa.update')->middleware('can:finance.manage_coa');
    Route::delete('finance/coa/{chartOfAccount}', [FinanceController::class, 'coaDestroy'])
        ->name('finance.coa.destroy')->middleware('can:finance.manage_coa');

    Route::get('finance/journal', [FinanceController::class, 'journalIndex'])
        ->name('finance.journal.index')->middleware('can:finance.view_ledger');
    Route::get('finance/journal/{journalEntry}', [FinanceController::class, 'journalShow'])
        ->name('finance.journal.show')->middleware('can:finance.view_ledger');

    Route::get('finance/reports', [FinanceController::class, 'reportsIndex'])
        ->name('finance.reports.index')->middleware('can:finance.view_reports');
    Route::get('finance/reports/trial-balance', [FinanceController::class, 'trialBalance'])
        ->name('finance.reports.trial-balance')->middleware('can:finance.view_reports');
    Route::get('finance/reports/profit-loss', [FinanceController::class, 'profitLoss'])
        ->name('finance.reports.profit-loss')->middleware('can:finance.view_reports');
    Route::get('finance/reports/balance-sheet', [FinanceController::class, 'balanceSheet'])
        ->name('finance.reports.balance-sheet')->middleware('can:finance.view_reports');
});
