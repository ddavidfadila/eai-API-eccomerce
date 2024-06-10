<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Authorization Header: ' . $request->header('Authorization'));
        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            $userId = $payload->get('sub');
            $userEmail = $payload->get('email');

            $request->attributes->set('userId', $userId);
            $request->attributes->set('userEmail', $userEmail);

        } catch (JWTException $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                Log::error('Invalid token: ' . $e->getMessage());
                return response()->json(['status' => 'Token is Invalid'], 401);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                Log::error('Expired token: ' . $e->getMessage());
                return response()->json(['status' => 'Token is Expired'], 401);
            } else {
                Log::error('Authorization token not found: ' . $e->getMessage());
                return response()->json(['status' => 'Authorization Token not found'], 401);
            }
        }
        
        return $next($request);
    }
}
