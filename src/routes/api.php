<?php

use Illuminate\Support\Facades\Route;
use Mpesa\Http\Controllers\C2BController;
use Mpesa\Http\Controllers\STKPushController;

Route::prefix('api/mpesa/c2b')->group(function () {
    Route::get('/token', [C2BController::class, 'getToken']);
    Route::post('/register', [C2BController::class, 'registerUrls']);
    Route::post('/validation', [C2BController::class, 'validation']);
    Route::post('/confirmation', [C2BController::class, 'confirmation']);
});

Route::prefix('api/mpesa/stk')->group(function () {
    Route::post('/push', [STKPushController::class, 'initiate']);
    Route::post('/callback', [STKPushController::class, 'callback']);
});
