<?php

namespace App\Http\Controllers;

use App\Helpers\Decode;
use App\Helpers\Encode;
use App\Helpers\Mssql;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
                'code' => 422,
                'status' => 'error',
                'message' => 'Failed login'
            ], 422);
        }

        $token = Encode::jwt(['response' => $response, 'time' => time()]);
        verify($response->UserID, $response->Email);

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

        $response = procedure("User_VerifyCode", [
            'UserID' => $UserID,
            'VerifyCode' => $request->Code
        ], true);

        if (isFailed($response)) {
            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => 'Code is invalid or expired'
            ], 401);
        }

        $token = Encode::jwt(['response' => $request->attributes->get('user'), 'time' => time()]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'successfully login',
            'user' => $request->attributes->get('user'),
            'sites' => procedure("Get_UserSites", [
                'pUserID' => $UserID
            ]),
            'token' => $token
        ]);
    }

    public function resendVerify(Request $request)
    {
        $UserID = $request->attributes->get('user')->UserID ?? 0;
        $UserEmail = $request->attributes->get('user')->Email ?? '';
        verify($UserID, $UserEmail);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'resend otp',
            'verify' => 1
        ]);
    }

    public function execute(string $name)
    {
        $params = json_decode(request()->getContent(), true) ?? [];
        $results = procedure($name, $params);

        return response()->json([
            'total' => count($results),
            'results' => $results,
            'params' => $params
        ]);
    }

    public function upload()
    {
        if (!request()->hasFile('file')) {
            return response()->json([
                'message' => 'file not found',
                'status' => 'error'
            ], 400);
        }

        $file = request()->file('file');
        $path = $file->storeAs(
            'Plan',
            $file->getClientOriginalName(),
            's3'
        );

        $url = Storage::disk('s3')->temporaryUrl(
            $path, now()->addDay()
        );

        return response()->json([
            'path' => $path,
            'url' => $url,
            'message' => 'file uploaded',
            'status' => 'success'
        ]);
    }

    public function getFile()
    {
        if (!request()->has('path')) {
            return response()->json([
                'message' => 'path not found',
                'status' => 'error'
            ], 400);
        }

        $url = Storage::disk('s3')->temporaryUrl(
            request()->has('path'), now()->addDay()
        );

        if (!$url) {
            return response()->json([
                'message' => 'url not found',
                'status' => 'error'
            ], 400);
        }

        return response()->json([
            'path' => request()->has('path'),
            'url' => $url,
            'message' => 'file uploaded',
            'status' => 'success'
        ]);
    }
}
