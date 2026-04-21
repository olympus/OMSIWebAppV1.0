<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class APIV3Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {    
        // token payload, query, header sab jagah check karega
        if(isset($request->auth_token)){
            if( $request->auth_token=='vN7$kP2@xQ9!Lm4#Tz8&YwR5^cD1*Hs6Uj3Fb0AeG%MZrCqWnXyKpVtB+JdEoS9uI2lO4hN8P1fR7mT5aQ0ZxC3Yw6kL@'){
                return $next($request);
            }
            /*elseif( $request->auth_token=='5c2b9071-a675-49b0-8fb2-9cd894da1c87'){
                return $next($request);
            }
            elseif($request->auth_token=='5c2b9071-a675-49b0-8fb2-9cd894da1c81'){
                return $next($request);
            }
            elseif($request->auth_token=='SFDC9072-a675-49b0-8fb2-9cd894da1c87'){
                return $next($request);
            }*/
            else{
                return response('Invalid Request', 400)
                ->header('Content-Type', 'text/plain');
            }
        }else{
            return response('Invalid Request', 400)
            ->header('Content-Type', 'text/plain');
        }
    }
} 