<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontdeskcrm\Http\Controllers\ChannelWebhookController;
use Modules\Frontdeskcrm\Http\Controllers\FrontdeskcrmController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->group(function () {
    Route::apiResource('frontdeskcrm', FrontdeskcrmController::class)->names('frontdeskcrm');
});

// Channel webhook endpoints (no auth — OTAs call these with their own signature)
Route::post('/webhooks/channel/{channel}', ChannelWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('frontdesk.webhooks.channel');
