<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {

    verifyMail('ismail@adlookups.com', [
        'email' => 'ismail@adlookups.com',
        'subject' => 'PlanVisio Access Code',
        'code' => rand(100000, 999999)
    ]);

    return view('welcome');
});



Route::prefix('cron')->group(function () {

});

Route::post('clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
});
