<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [\App\Http\Controllers\ApiController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::post('verify', [\App\Http\Controllers\ApiController::class, 'verify']);
    Route::post('resend-verify', [\App\Http\Controllers\ApiController::class, 'resendVerify']);
    Route::post('get/{name}', [\App\Http\Controllers\ApiController::class, 'get']);
});


