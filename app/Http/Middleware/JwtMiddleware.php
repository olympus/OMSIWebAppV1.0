<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        try {
            $user = JWTAuth::parseToken()->authenticate(); 
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'status_code' => 402,
                    'message' => 'Session is expired. Please login again',
                ]);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'status_code' => 402,
                    'message' => 'Please login again',
                ]); 
            }else{
                return response()->json([
                    'status_code' => 402,
                    'message' => 'Authorization Token not found',
                ]); 
            }
        }
        return $next($request);
    }
}