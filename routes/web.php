<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    // "SELECT * FROM [AdpeaksMedia].[dbo].[vw_GetAPIUserCredentials]"

    $name = "vw_GetAPIUserCredentials";
    $params = [
        'data' => 1,
        'data1' => 2,
        'data2' => 3
    ];

    dd(
        (new \App\Helpers\Mssql())->query($name, $params)
    );

    return view('welcome');
});
