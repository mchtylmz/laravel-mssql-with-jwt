<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Decode
{
    public static function jwt(string $code): \stdClass
    {
        $secretKey = env('JWT_SECRET');

        return JWT::decode($code, new Key($secretKey, 'HS256'));
    }
}
