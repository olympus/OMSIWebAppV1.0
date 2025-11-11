<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Session; 

class is_expired
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
        if(Auth::check() && Auth::user()->is_expired == 1){
            Auth::guard('web')->logout();
            Session::flush();
            return redirect('/login')->with('error','Your password has been expired.Please reset your password now.');
        }else{
            return $next($request);
        }
    }
}
