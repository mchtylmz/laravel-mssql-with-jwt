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

    $name = "Get_UserLogin";
    $params = [
        'Useremail' => 'ismail@adlookups.com',
        'Password' => 'Pswd1234'
    ];

    $sql = (new \App\Helpers\Mssql())->query($name, $params);
    dd(
        $sql,
        (new \App\Helpers\Mssql())->run($sql)
    );

    return view('welcome');
});
