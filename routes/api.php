<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::post('login', [ApiController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::post('verify', [ApiController::class, 'verify']);
    Route::post('resend-verify', [ApiController::class, 'resendVerify']);
    Route::post('execute/{name}', [ApiController::class, 'execute']);
    Route::post('upload', [ApiController::class, 'upload']);
    Route::post('get-file', [ApiController::class, 'getFile']);
});
