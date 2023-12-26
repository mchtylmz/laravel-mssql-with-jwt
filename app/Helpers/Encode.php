<?php

namespace App\Helpers;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;

class Encode
{
    public static function jwt(array $data = []): string
    {
        $secretKey = env('JWT_SECRET');
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => array_merge(
                $data,
                ['ip' => request()->getClientIp()]
            )
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }
}
