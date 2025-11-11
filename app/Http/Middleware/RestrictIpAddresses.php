<?php

namespace App\Http\Middleware;

use Closure;
use Jenssegers\Agent\Agent;
use Cookie;
class RestrictIpAddresses
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
        // Get Filament admin base path
        $adminPath = trim(config('filament.admin.path', 'admin'), '/');

        // ✅ Allow only admin login / base route
        if (
            $request->is($adminPath) ||          // example: /admin
            $request->is($adminPath . '/login') || $request->is($adminPath . '/logout') || $request->is($adminPath . '/password-reset/request')  // example: /admin/login
        ) {
            return $next($request);
        }


        $agent = new Agent();
        if(in_array($request->ip(), [
            '122.180.25.201',//Olympus 1
            '122.180.25.202',//Olympus 2
            '115.114.31.169',//Olympus 3
            '115.114.31.171',//Olympus 4
            
            '122.162.118.167', //NM
            '182.68.29.102',
            '203.186.157.142',
            '122.162.98.203',
            '122.161.204.229',
            '42.111.25.227',

            '182.73.38.42',
            '59.145.156.74',
            '182.76.4.12',
            '182.72.128.19',
            '182.73.98.163',
            '59.144.157.156',
            '182.74.162.19',
            '182.73.56.116',
            '182.72.88.59',

            '14.143.192.2', 
            '14.143.192.3', 
            '203.200.55.122', 
            '203.200.55.123',

            '202.54.143.114',
            '14.142.157.138',
            '203.200.55.186', 
            '115.114.100.194',
            '115.114.105.210', 
            '115.114.105.130',
            '14.141.132.250', 
            '121.244.140.62',

            '59.160.158.74', 
            '59.160.158.50',

            '14.143.111.166', 
            '14.143.111.22',

            '14.142.219.78', 
            '115.114.101.210',

            '121.244.134.70',

            '14.142.69.34',

        ])&&($agent->isDesktop())){
            return $next($request);
        }
        elseif($request->hasCookie('developer')) {
            return $next($request);
        }

        else{
            return response('Not Authorized!<br>'.$request->ip(), 403)
                  ->header('Content-Type', 'text/html');
        
        }
    }
}
