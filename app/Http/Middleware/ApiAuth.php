<?php

namespace App\Http\Middleware;

use Closure;
use \App\ApiRequests;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function getStringBetween($str,$from,$to){
        $sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
        return substr($sub,0,strpos($sub,$to));
    }

    public function handle($request, Closure $next)
    {   

        $temp_data1=explode("\n", $request);
        $request_type=explode(" ", $temp_data1[0])[0];
        $request_body=trim(end($temp_data1));
        $request_url1=str_replace($request_type,"",$temp_data1[0]);
        $request_url=trim(str_replace("HTTP/1.1","",$request_url1));

        $tempcid = $this->getStringBetween($request,"/api/v1/","?auth_token=");
        $tempcid1 = substr($tempcid, strrpos($tempcid, '/') + 1);
        $identifier = is_numeric($tempcid1) ? $tempcid1 : "" ;
        if(isset($request->customer_id)){ $identifier = $request->customer_id; }

        $api_request = new ApiRequests;
        $api_request->identifier = $identifier;
        $api_request->request_type = $request_type;
        $api_request->request_body = $request_body;
        $api_request->request_url = $request_url;
        $api_request->save();
        
        // \Log::info("\n\n[[[[ \nCustomerID==>".$identifier."\nRequestData==>\n"."identifier==>".$identifier."\n"."request_type==>".$request_type."\n"."request_body==>".$request_body."\n"."request_url==>".$request_url."\n"."\n]]]]\n\n".$request);

        if(isset($request->auth_token)){
            if( $request->auth_token=='5c2b9071-a675-49b0-8fb2-9cd894da1c87'){
                return $next($request);
            }elseif($request->auth_token=='5c2b9071-a675-49b0-8fb2-9cd894da1c81'){
                return $next($request);
            }elseif($request->auth_token=='SFDC9072-a675-49b0-8fb2-9cd894da1c87'){
                return $next($request);
            }else{
                return response('Invalid Request', 400)
                ->header('Content-Type', 'text/plain');
            }
        }else{
            return response('Invalid Request', 400)
            ->header('Content-Type', 'text/plain');
        }
    }
}
