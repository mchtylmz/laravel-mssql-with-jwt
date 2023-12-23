<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtVerify
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $authorizationHeader = explode(' ', $request->header('Authorization'));
            $head = !empty($authorizationHeader[0]) ? $authorizationHeader[0] : false;
            $jwt = !empty($authorizationHeader[1]) ? $authorizationHeader[1] : false;

            if (!$head || !$jwt) {
                throw new \Exception('unauthorized');
            }

            $secretKey = env('JWT_SECRET');
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            $request->attributes->add(['decoded' => $decoded, 'jwt' => $jwt]);

            return $next($request);

        }
        catch (ExpiredException|\Exception $error) {
            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => $error->getMessage()
            ], 401);
        }
    }
}
