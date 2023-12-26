<?php

namespace App\Http\Controllers;

use App\Helpers\Decode;
use App\Helpers\Encode;
use App\Helpers\Mssql;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ApiController extends Controller
{
    public function login(LoginRequest $request)
    {
        $response = procedure("Get_UserLogin", [
            'Useremail' => $request->Useremail,
            'Password' => $request->Password
        ], true);
        if (isFailed($response)) {
            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => 'Failed login'
            ], 401);
        }

        $token = Encode::jwt(['response' => $response]);
        mssql()->generateVerifyCode($response->UserID);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'verify otp',
            'verify' => 1,
            'token' => $token
        ]);
    }

    public function verify(Request $request)
    {
        $UserID = $request->attributes->get('user')->UserID ?? 0;
        /*
        $response = procedure("__OTP_VERIFY__", [
            'UserID' => $UserID,
            'code' => $request->code
        ], true);

        if (isFailed($response)) {
            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => 'Failed otp'
            ], 401);
        }*/

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'successfully otp',
            'user' => $request->attributes->get('user'),
            'sites' => procedure("Get_UserSites", [
                'pUserID' => $UserID
            ])
        ]);
    }

    public function resendVerify(Request $request)
    {
        $UserID = $request->attributes->get('user')->UserID ?? 0;
        mssql()->generateVerifyCode($UserID);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'resend otp',
            'verify' => 1
        ]);
    }

    // login
    // verify -> resend
    // verify token change

    public function get(string $name)
    {
        $params = json_decode(request()->getContent(), true) ?? [];
        $results = procedure($name, $params);

        return response()->json([
            'total' => count($results),
            'results' => $results,
            'params' => $params
        ]);
    }
}
