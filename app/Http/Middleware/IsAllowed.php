<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\URL;
use Auth;
use Session; 
class IsAllowed
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
        $methods_disabled = [
            'create',
            'store',
            'edit',
            'update',
            'delete',
            'destroy'
        ];

        $paths_disabled = [
            'admin/activity-log',
            'mail-update/'.$request->route('id'),
            'mail/'.$request->route('id'),
            '/dashboard/settings/'.$request->route('region').'/update',
            '/dashboard/settings/'.$request->route('region'),
            'admin/emailsmaster-academic',
			'admin/emailsmaster-enquiry',
			'admin/emailsmaster-service',
			'admin/emailsmaster-settings/'.$request->route('team'),
			'admin/emailsmaster-settings_post',
			'dashboard/settings/'.$request->route('region'),
			'mis',
			'mis/'.$request->route('region'),
            'admin/admins'


        ];

        $special_paths_allowed = [
            'admin/requests/'.$request->route('request').'/edit',
            'admin/requests/'.$request->route('request')
        ];

        $special_methods_allowed = [
            'edit',
            'update'
        ];


        $path_current_name = \Route::getCurrentRoute()->getActionName();
        $method_name_is = substr($path_current_name, strpos($path_current_name, "@") + 1);
        // echo $method_name_is.'<br>';
        $path_name_is = \Request::path();
        
        if( \Auth::check()){ 
            if(Auth::user()->is_expired == 0){
                if(\Auth::user()->hasRole(['superadministrator|administrator'])) {
                    return $next($request);
                }
                elseif(\Auth::user()->hasRole(['administratorservice']) || \Auth::user()->hasRole(['administratoracademic']) || \Auth::user()->hasRole(['administratorenquiry'])) {
                    if(in_array($path_name_is, $special_paths_allowed) || in_array($method_name_is, $special_methods_allowed) || $method_name_is == 'destroy'){
                        return $next($request);
                    }
                }
                elseif(\Auth::user()->hasRole(['administratorservicec'])) {
                    if(in_array($path_name_is, $special_paths_allowed) || in_array($method_name_is, $special_methods_allowed)){
                        return $next($request);
                    }
                    elseif(in_array($method_name_is, $methods_disabled) || in_array($path_name_is, $paths_disabled)){
                        echo "Not Allowed";
                        die;
                    }else{
                        return $next($request);
                    }
                
                }
                elseif(\Auth::user()->hasRole(['reader'])) {
                    //dd("ritk");
                    if(in_array($method_name_is, $methods_disabled) || in_array($path_name_is, $paths_disabled)){ 
                        echo "Not Allowed";
                        die;
                    }else{ 
                        return $next($request);
                    }
                
                }
            }elseif(Auth::user()->is_expired == 1){
                Auth::guard('web')->logout();
                Session::flush();
                return redirect('/login')->with('error','Your password has been expired.Please reset your password now.');
            }
        }else{
            return redirect('/login')
            ->with(['error' => "You do not have the permission to enter this site. Please login with correct user."]);
        }
        //dd(\Auth::user()->hasRole(['administratorservicec']));
        return $next($request);
    }
}
