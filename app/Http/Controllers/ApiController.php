<?php

namespace App\Http\Controllers;

use App\Helpers\Encode;
use App\Helpers\Mssql;
use App\Http\Requests\LoginRequest;

class ApiController extends Controller
{
    public function login(LoginRequest $request)
    {
        $token = Encode::jwt([
            'user' => 1
        ]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Giriş başarılı',
            'verify' => 1,
            'token' => $token
        ]);
    }

    public function get(string $name)
    {
        return response()->json([
            'name' => $name,
            'params' => json_decode(request()->getContent(), true),
            'query' => (new Mssql())->query($name, json_decode(request()->getContent(), true))
        ]);
    }
}
