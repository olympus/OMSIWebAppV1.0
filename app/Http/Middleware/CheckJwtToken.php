<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Customers;

class CheckJwtToken
{
    // public function handle(Request $request, Closure $next)
    // {   

    //     try {

    //         $token = JWTAuth::getToken(); 
            
    //         if (!$token) {

    //             return response()->json([
    //                 'status_code' => 401,
    //                 'message' => 'Token not provided'
    //             ]);
    //         }

    //         // ✅ FIXED LINE
    //         $user = JWTAuth::setToken($token)->toUser();

    //         if (!$user) {

    //             return response()->json([
    //                 'status_code' => 401,
    //                 'message' => 'User not found'
    //             ]);
    //         }

    //         // ✅ Old token invalid
    //         if ($user->jwt_token !== $token->get()) {

    //             return response()->json([
    //                 'status_code' => 401,
    //                 'message' => 'Session expired. Please login again'
    //             ]);
    //         }

    //     }
    //     catch (Exception $e) {

    //         return response()->json([
    //             'status_code' => 401,
    //             'message' => $e->getMessage()
    //         ]);
    //     }

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next)
    {
        try {

            // user get from token
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {

                return response()->json([
                    'status_code' => 401,
                    'message' => 'User not found'
                ]);
            }

            // get current token from request
            $current_token = JWTAuth::getToken()->get();

            // get token from database
            $db_token = Customers::where('id', $user->id)
                        ->value('jwt_token');

            // compare token
            if ($current_token !== $db_token) {

                return response()->json([
                    'status_code' => 401,
                    'message' => 'Session expired. Please login again.'
                ]);
            }

        }
        catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json([
                'status_code' => 401,
                'message' => 'Token expired'
            ]);

        }
        catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json([
                'status_code' => 401,
                'message' => 'Token invalid'
            ]);

        }
        catch (\Exception $e) {

            return response()->json([
                'status_code' => 401,
                'message' => 'Token missing'
            ]);
        }

        return $next($request);
    }
}
