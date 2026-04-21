<?php
namespace App\Http\Controllers\API\V3;
use App\CustomerTemp;
use App\Models\Departments;
use App\Helpers\Helper;
use App\HospitalTemp;
use App\Http\Controllers\Controller;
use App\Mail\SendForgotOtp;
use App\Mail\SendOtp;
use App\Mail\SendResendOtp;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\User;
use App\NotifyCustomer;
use App\PasswordHistory;
use App\UserLoginAttemptCount;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JWTAuth;
use Log;
use Mail;
use Response;
use Tymon\JWTAuth\Middleware\GetUserFromToken;
use Validator;

class CustomerCommonController extends Controller
{
   	public function getOtp(Request $request)
	{ 
	    if($request->type === 'customer'){
		    $customer = Customers::where('email', strtolower($request->email))
		        ->where('mobile_number', $request->mobile_number)
		        ->whereNull('deleted_at')
		        ->latest()
		        ->first();
		}else{
			$customer = CustomerTemp::where('email', strtolower($request->email))
		        ->where('mobile_number', $request->mobile_number) 
		        ->latest()
		        ->first();
		}

	    return response()
	        ->json([
	            'status_code' => 200,
	            'message' => 'Customer data',
	            'data' => $customer
	        ]);
	}
 
    public function customerDeleteData(Request $request)
    { 
        if ($request->type == "delete") {
         	$customer = Customers::where('email', strtolower($request->email))->where('mobile_number', $request->mobile_number)->latest()->first();

            $customer->is_deleted = 1;  
            $customer->deleted_at = Carbon::now(); 
            $customer->save();   
            return Response::json(['status_code'=>200, 'message'=>'Customer delete successfully!']);
        }elseif ($request->type == "un_delete") {
        	$customer = Customers::onlyTrashed()
		    ->where('email', strtolower($request->email))
		    ->where('mobile_number', $request->mobile_number)
		    ->latest()
		    ->first(); 
            $customer->is_deleted = 0;  
            $customer->deleted_at = null; 
            $customer->save();   
            return Response::json(['status_code'=>200, 'message'=>'Customer un delete successfully!']);
        } else {
            return Response::json(['status_code'=>403, 'message'=>'Incorrect Mobile Number !']);
        } 
    }

    public function customerPasswordExpired(Request $request)
    { 
        if ($request->is_expired == 1) {
         	$customer = Customers::where('email', strtolower($request->email))->where('mobile_number', $request->mobile_number)->latest()->first();

            $customer->is_expired = 1;   
            $customer->save();   
            return Response::json(['status_code'=>200, 'message'=>'Customer password expired!']);
        }elseif ($request->is_expired == 0) {
         	$customer = Customers::where('email', strtolower($request->email))->where('mobile_number', $request->mobile_number)->latest()->first();

            $customer->is_expired = 0;   
            $customer->save();   
            return Response::json(['status_code'=>200, 'message'=>'Customer password un expired!']);
        } else {
            return Response::json(['status_code'=>403, 'message'=>'Incorrect Mobile Number !']);
        } 
    }
}
