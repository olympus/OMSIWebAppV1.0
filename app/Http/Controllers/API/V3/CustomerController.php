<?php
namespace App\Http\Controllers\API\V3;
use App\CustomerTemp;
use App\Models\Departments;
use App\Helpers\Helper;
use App\HospitalTemp;
use App\Http\Controllers\Controller;
use App\Mail\Revamp\SendForgotOtp;
use App\Mail\Revamp\SendOtp;
use App\Mail\Revamp\SendResendOtp;
use App\Mail\Revamp\SendKYCVerificationMail;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\User;
use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\Promailer;
use App\Models\EmployeeTeam;
use App\Models\CustomerShowPromailer;
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

class CustomerController extends Controller
{   
    public function departmentsList()
    {
        $departments = Departments::orderBy('sort_order', 'ASC')->get();
        return $departments;
    }

    public function login(Request $request)
    {
        Logger("customer login api request");
        Logger($request->all());

        $rules = [
            'email'        => 'required|email|exists:customers,email',
            'password'     => 'required', 
            
            'device_token' => [
                'required',
                'string',
                'min:20',
                'max:255',
                'regex:/^[A-Za-z0-9\-\_\:\.]+$/'
            ],


            'platform'    => 'required|string|regex:/^[a-zA-Z\s]*$/',
            'app_version' => 'required|string|regex:/[0-9]/|regex:/[.]/',

            'is_mpin'     => 'required|in:0,1',
            'is_face_id'  => 'required|in:0,1',
        ];
     
        $messages = [
            'email.exists' => 'The username or password is not valid.',
            'is_mpin.in'    => 'Invalid MPIN flag. Allowed values are 0 or 1.',
            'is_face_id.in' => 'Invalid Face ID flag. Allowed values are 0 or 1.',
        ];
     
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{

        $app_info = [
        'ios' => config('oly.current_version_iOS'),
        'android' => config('oly.current_version_android'),
        'is_app_update' => 0,
        'message' => "Dear customer!
Post-repair delivery acknowledgement is now available!
Update your app to access this feature."
            ];

            $customer = Customers::where('email', strtolower($request->email))->whereNull('deleted_at')->first();

            Logger("customer login api customer data");
            Logger($customer);

            if(!isset($customer)){
                return response()->json([
                    'status_code' => 400,
                    'message' => 'The username or password is not valid.',
                ]);
            }

            if($customer->is_account_block == 1){
                return response()->json([
                    'status'  => 423,
                    'status_code' => 423,
                    'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
                ]);
            }

            if($customer != null){
                $login_attempt_data_check = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                $login_attempt_check = UserLoginAttemptCount::where(['user_id' => $customer->id,'login_attempts' => 10])->first();

                if(!empty($login_attempt_check)){
                    $to = Carbon::createFromFormat('Y-m-d H:i:s', $login_attempt_check->login_attempts_updated_at);
                    $from = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now());
                    $diff_in_hours = $to->diffInMinutes($from);
                }
                if (!is_null($customer)) {
                    if(!empty($login_attempt_check)){
                        if($diff_in_hours >= 15){

                            Logger("customer login api diff in hours");
                            Logger($diff_in_hours);
                            if($customer->is_expired == 0){
                                $respArr = [];

                                // DEVICE TOKEN SECURITY CHECK
                            
                             


                                $isSameEmail = $customer->email === $request->email;
                                $isSameToken = $customer->device_token === $request->device_token; 

                                if (isset($request->device_token)) {
                                    if ($request->device_token!=null) {
                                        $customer->device_token = $request->device_token;
                                        $customer->platform = $request->platform;
                                        $customer->save();
                                    }
                                }

                                /*if ($request->is_mpin == 0) { 
                                    $customer->is_mpin = 0; 
                                    $customer->save(); 
                                }


                                if ($request->is_face_id == 0) { 
                                    $customer->is_face_id = 0; 
                                    $customer->save(); 
                                }*/


                                if ($isSameEmail == true  && $isSameToken == false) { 
                                    $customer->update([
                                        'is_mpin' => 0,
                                        'is_face_id' => 0,
                                    ]);
                                }elseif ($isSameEmail == false  && $isSameToken == true) { 
                                    $customer->update([
                                        'is_mpin' => 0,
                                        'is_face_id' => 0,
                                    ]);
                                }elseif ($isSameEmail == false  && $isSameToken == false) { 
                                    $customer->update([
                                        'is_mpin' => 0,
                                        'is_face_id' => 0,
                                    ]);
                                }
                                
                              Customers::where('device_token', $request->device_token)
                                ->where('id', '!=', $customer->id)
                                ->whereNull('deleted_at')
                                ->update([
                                    'is_mpin' => 0,
                                    'is_face_id' => 0,
                                ]);

                                if (isset($request->app_version)) {
                                    if ($request->app_version!=null) {
                                        $customer->app_version = $request->app_version;
                                        $customer->save();
                                    }
                                }
                                $hospitals = Hospitals::whereIn('id', explode(',', $customer->hospital_id))->get();
                                if (Hash::check($request->password, $customer->password)) {

                                    Logger("customer login api password check");

                                    if(!empty($login_attempt_data_check)){
                                        UserLoginAttemptCount::where('user_id', $customer->id)->update([
                                            'login_attempts' => 0,
                                        ]);
                                    }

                                    foreach ($hospitals as $hospital) {
                                        $departments = Departments::whereIn('id', explode(',', $hospital->dept_id))->get();
                                        $hospital->deptAry = $departments;
                                    }
                                    if ($customer->is_verified) {
                                        $customer->hospitalAry = $hospitals;

                                        // $customer->days = $customer->account_verify_at ? Carbon::parse($customer->account_verify_at)->diffInDays(now()) : 0;

                                        // $customer->is_popup_show = 0;
                                        // $customer->is_mandatory  = 0;

                                        // if ($customer->days > 0 && $customer->days <= 15) {
                                        
                                        //     $customer->is_popup_show = 1;
                                        //     $customer->is_mandatory  = 0;
                                        // }
                                        // elseif($customer->days == 0) {
                                            
                                        //     $customer->is_popup_show = 1;
                                        //     $customer->is_mandatory  = 1;
                                        // }

                                        $kycDays = 90;

                                        if ($customer->account_verify_at) {

                                            $verifyDate = Carbon::parse($customer->account_verify_at)->startOfDay();
                                            $expiryDate = $verifyDate->copy()->addDays($kycDays);

                                            $daysLeft = Carbon::today()->diffInDays($expiryDate, false);

                                            $customer->days = $daysLeft > 0 ? $daysLeft : 0;

                                        } else {
                                            $customer->days = 0;
                                        }
                                        //$customer->days = $days;
                                        $customer->is_popup_show = 0;
                                        $customer->is_mandatory  = 0;
                                        $customer->is_kyc_message = null;
                                        if ($customer->days > 0 && $customer->days <= 15) {
                                            $customer->is_popup_show = 1;
                                            $customer->is_kyc_message = "Please Update Your KYC";
                                        } elseif ($customer->days === 0) {
                                            $customer->is_popup_show = 1;
                                            $customer->is_mandatory  = 1;
                                            $customer->is_kyc_message = "Please Update Your KYC";
                                        }

                                        try {
                                            JWTAuth::invalidate(JWTAuth::setToken($customer->jwt_token)); 
                                        } catch (\Exception $e) {
                                            Logger('Error in JWT Token Invalidation: ' . $e->getMessage());
                                        }

                                        $access_token = JWTAuth::fromUser($customer);

                                        Customers::where('id', $customer->id)->update([
                                            'jwt_token' => $access_token,
                                        ]);
                                        
                                        $customer_data_get = Customers::where('id', $customer->id)->first();
                                        $customer->jwt_token = $customer_data_get->jwt_token;
                                        $respArr['status_code'] = 200;
                                        $respArr['message'] = 'Success';
                                        $respArr['data'] = $customer->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);
                                        $respArr['access_token'] = $access_token;
                                        $respArr['token_type'] = 'bearer';
                                        $respArr['expires_in'] = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');
                                        $respArr['app_info'] = $app_info;
                                         
                                        if($customer->is_testing){
                                            $respArr['data']->testing_url = \Config('oly.testing_url');
                                        }
                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }else {
                                        if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                                            $customer = Customers::where('email', strtolower($request->email))->first();
                                            $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                                            $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                                            $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600); 
                                            $customer->save();
                                        }
                                        $respArr['status_code'] = 401;
                                        $respArr['message'] = 'Your Account is not verified yet.';
                                        $respArr['app_info'] = $app_info;
                                        $respArr['data'] = $customer->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);
                                        $customer_data = Customers::where('email', strtolower($request->email))->first();
                                         
                                        Mail::to($customer_data->email)->send(new SendOtp($customer_data));
                                        sendCustomerMobileSms('send_otp', $customer_data, "", "");  
                                        
                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }
                                }else {
                                    $login_attempt = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                                    Logger("customer login api password attempt check");
                                    Logger($login_attempt);
                                    if(empty($login_attempt)){
                                        $left_attempt = 0;
                                        $left_attempt =  10 - 1;
                                        $user_login_attempt_count = new UserLoginAttemptCount();
                                        $user_login_attempt_count->user_id = $customer->id;
                                        $user_login_attempt_count->login_attempts = 1;
                                        $user_login_attempt_count->login_attempts_updated_at = Carbon::now();
                                        $user_login_attempt_count->save();
                                        $login_attempt_chk = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                                    }else{
                                        $login_attempt_chk = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                                        if($login_attempt_chk->login_attempts >= 0){
                                            $attmpt = $login_attempt_chk->login_attempts + 1;
                                            $left_attempt =  10 - (int)$attmpt;
                                        }

                                        if($login_attempt->login_attempts < 10){
                                            $user_login_attempt_count = UserLoginAttemptCount::find($login_attempt->id);
                                            $user_login_attempt_count->user_id = $customer->id;
                                            $user_login_attempt_count->login_attempts = $login_attempt->login_attempts + 1;
                                            $user_login_attempt_count->login_attempts_updated_at = Carbon::now();
                                            $user_login_attempt_count->save();
                                        }
                                    }

                                    if($login_attempt_chk->login_attempts < 9){
                                        Logger("customer login api password attempt check less than 9"); 
                                        $respArr['status_code'] = 403; 
                                        $respArr['app_info'] = $app_info;
                                        $respArr['message'] = 'Invalid credentials!
Please try again.
You have '.$left_attempt.' login attempts remaining.';

                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }else{
                                        Logger("customer login api password account locked");
                                        $respArr['status_code'] = 403;
                                        $respArr['app_info'] = $app_info;
                                        $respArr['message'] = 'Your account has been locked due to multiple failed login attempts. Please try again after 15 minutes.';
                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }
                                }
                            }else{
                                Logger("customer login api password has been expired");
                                return response()->json([
                                    'status_code' => 407,
                                    'message' => 'Your password has been expired.Please reset your password now.',
                                    'is_expired' => $customer->is_expired
                                ]);
                            }
                        }else{
                            Logger("customer login api password Your Account has been locked due to multiple failed login attempts. Please try again after 15 minutes");
                            $respArr['status_code'] = 403;
                            $respArr['app_info'] = $app_info;
                            $respArr['message'] = 'Your Account has been locked due to multiple failed login attempts. Please try again after 15 minutes.';
                            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                        }
                    }else{ 
                        if($customer->is_expired == 0){
                            Logger("customer login api password when is expired zero");

                            $respArr = [];
                            
                            $isSameEmail = $customer->email === $request->email;
                            $isSameToken = $customer->device_token === $request->device_token;

                            if (isset($request->device_token)) {
                                if ($request->device_token!=null) {
                                    $customer->device_token = $request->device_token;
                                    $customer->platform = $request->platform;
                                    $customer->save();
                                }
                            }  
                            

                            if ($isSameEmail == true  && $isSameToken == false) { 
                                $customer->update([
                                    'is_mpin' => 0,
                                    'is_face_id' => 0,
                                ]);
                            }elseif ($isSameEmail == false  && $isSameToken == true) { 
                                $customer->update([
                                    'is_mpin' => 0,
                                    'is_face_id' => 0,
                                ]);
                            }elseif ($isSameEmail == false  && $isSameToken == false) { 
                                $customer->update([
                                    'is_mpin' => 0,
                                    'is_face_id' => 0,
                                ]);
                            }


                            Customers::where('device_token', $request->device_token)
                                ->where('id', '!=', $customer->id)
                                ->whereNull('deleted_at')
                                ->update([
                                    'is_mpin' => 0,
                                    'is_face_id' => 0,
                                ]);

                            if (isset($request->app_version)) {
                                if ($request->app_version!=null) {
                                    $customer->app_version = $request->app_version;
                                    $customer->save();
                                }
                            }
                            $hospitals = Hospitals::whereIn('id', explode(',', $customer->hospital_id))->get();
                            if (Hash::check($request->password, $customer->password)) {
                                Logger("customer login api password check");

                                if(!empty($login_attempt_data_check)){
                                    UserLoginAttemptCount::where('user_id', $customer->id)->update([
                                        'login_attempts' => 0,
                                    ]);
                                }

                                foreach ($hospitals as $hospital) {
                                    $departments = Departments::whereIn('id', explode(',', $hospital->dept_id))->get();
                                    $hospital->deptAry = $departments;
                                }
                                if ($customer->is_verified) {
                                    $customer->hospitalAry = $hospitals;
                                    // $customer->days = $customer->account_verify_at ? Carbon::parse($customer->account_verify_at)->diffInDays(now()) : 0;

                                    // $customer->is_popup_show = 0;
                                    // $customer->is_mandatory  = 0;

                                    // if ($customer->days > 0 && $customer->days <= 15) {
                                    
                                    //     $customer->is_popup_show = 1;
                                    //     $customer->is_mandatory  = 0;
                                    // }
                                    // elseif($customer->days == 0) {
                                        
                                    //     $customer->is_popup_show = 1;
                                    //     $customer->is_mandatory  = 1;
                                    // }

                                    $kycDays = 90;

                                    if ($customer->account_verify_at) {

                                        $verifyDate = Carbon::parse($customer->account_verify_at)->startOfDay();
                                        $expiryDate = $verifyDate->copy()->addDays($kycDays);

                                        $daysLeft = Carbon::today()->diffInDays($expiryDate, false);

                                        $customer->days = $daysLeft > 0 ? $daysLeft : 0;

                                    } else {
                                        $customer->days = 0;
                                    }
                                    //$customer->days = $days;
                                    $customer->is_popup_show = 0;
                                    $customer->is_mandatory  = 0;
                                    $customer->is_kyc_message = null;
                                    if ($customer->days > 0 && $customer->days <= 15) {
                                        $customer->is_popup_show = 1;
                                        $customer->is_kyc_message = "Please Update Your KYC";
                                    } elseif ($customer->days === 0) {
                                        $customer->is_popup_show = 1;
                                        $customer->is_mandatory  = 1;
                                        $customer->is_kyc_message = "Please Update Your KYC";
                                    }


                                    try {
                                        JWTAuth::invalidate(JWTAuth::setToken($customer->jwt_token)); 
                                    } catch (\Exception $e) {
                                        Logger('Error in JWT Token Invalidation: ' . $e->getMessage());
                                    }
                                    
                                    $access_token = JWTAuth::fromUser($customer);
                                    Customers::where('id', $customer->id)->update([
                                        'jwt_token' => $access_token,
                                    ]);

                                    
                                    $customer_data_get = Customers::where('id', $customer->id)->first();
                                    $customer->jwt_token = $customer_data_get->jwt_token;
                                    
                                    $respArr['status_code'] = 200;
                                    $respArr['message'] = 'Success';
                                    $respArr['app_info'] = $app_info;
                                    $respArr['data'] = $customer->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);
                                    $respArr['access_token'] = $access_token;
                                    $respArr['token_type'] = 'bearer';
                                    $respArr['expires_in'] = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');

                                    if($customer->is_testing){
                                        $respArr['data']->testing_url = \Config('oly.testing_url');
                                    }

                                    Logger("customer login api success response");
                                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                }else {
                                    if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                                        $customer = Customers::where('email', strtolower($request->email))->first();
                                        $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                                        $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                                        $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600); 
                                        $customer->save();
                                    }
                                    $respArr['status_code'] = 401;
                                    $respArr['app_info'] = $app_info;
                                    $respArr['message'] = 'Your Account is not verified yet.';
                                    $respArr['data'] = $customer->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);
                                    $customer_data = Customers::where('email', strtolower($request->email))->first();
                                    
                                    Mail::to($customer_data->email)->send(new SendOtp($customer_data));
                                    sendCustomerMobileSms('send_otp', $customer_data, "", "");

                                    // Mail::to($customer_data->email)
                                    //     ->send(new SendOtp($customer_data));
                                    // send_sms('request_created', $customer_data, "", "");
                                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                }
                            }else {
                                $login_attempt = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                                if(empty($login_attempt)){
                                    $left_attempt = 0;
                                    $left_attempt =  10 - 1;

                                    $user_login_attempt_count = new UserLoginAttemptCount();
                                    $user_login_attempt_count->user_id = $customer->id;
                                    $user_login_attempt_count->login_attempts = 1;
                                    $user_login_attempt_count->login_attempts_updated_at = Carbon::now();
                                    $user_login_attempt_count->save();

                                    $login_attempt_chk = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                                }else{
                                    $login_attempt_chk = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                                    if($login_attempt_chk->login_attempts >= 0){
                                        $attmpt = $login_attempt_chk->login_attempts + 1;
                                        $left_attempt =  10 - (int)$attmpt;
                                    }

                                    if($login_attempt->login_attempts < 10){
                                        $user_login_attempt_count = UserLoginAttemptCount::find($login_attempt->id);
                                        $user_login_attempt_count->user_id = $customer->id;
                                        $user_login_attempt_count->login_attempts = $login_attempt->login_attempts + 1;
                                        $user_login_attempt_count->login_attempts_updated_at = Carbon::now();
                                        $user_login_attempt_count->save();
                                    }
                                } 

                                if($login_attempt_chk->login_attempts < 9){
                                    Logger("customer login api Invalid credentials!");
                                    Logger($respArr);

                                    $respArr['status_code'] = 403;
                                    $respArr['app_info'] = $app_info;
                                    $respArr['message'] = 'Invalid credentials!
Please try again.
You have '.$left_attempt.' login attempts remaining.';
                                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                }else{
                                    $respArr['status_code'] = 403;
                                    $respArr['app_info'] = $app_info;
                                    $respArr['message'] = 'Your account has been locked due to multiple failed login attempts. Please try again after 15 minutes.';
                                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                }
                            }
                        }else{
                            return response()->json([
                                'status_code' => 407,
                                'message' => 'Your password has been expired.Please reset your password now.',
                                'is_expired' => $customer->is_expired
                            ]);
                        }
                    }
                }else {
                    $respArr['status_code'] = 404; 
                    $respArr['app_info'] = $app_info;
                    $respArr['message'] = 'The username or password is not valid.';
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            }else {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'The username or password is not valid.',
                ]);
            }
        }
    }

    public function accountVerification(Request $request)
    {
        Logger("customer forget pwd otp verify api rquest payload");
        Logger($request->all());

        if ($request->type == 'account') {

            $customer = CustomerTemp::where('id', $request->temp_customer_id)->first();
            $rules = [
                'temp_customer_id' => 'required|numeric',
                'mobile_otp'    => 'required|digits:6|integer',
                'email_otp'     => 'required|digits:6|integer',
                'type' => 'required',
            ];

        } elseif ($request->type == 'forgot-password-verify') {
            $customer = Customers::where('mobile_number', $request->mobile_number)->where('email', strtolower($request->email))->first();  

            $rules = [
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'mobile_otp'    => 'required|digits:6|integer',
                'email'         => 'required|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
                'email_otp'     => 'required|digits:6|integer',
                'type'          => 'required'
            ];
        }elseif ($request->type == 'verify-account-kyc') {
            $customer = Customers::where('mobile_number', $request->mobile_number)->where('email', strtolower($request->email))->first();  
            
            $rules = [
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'mobile_otp'    => 'required|digits:6|integer',
                'email'         => 'required|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
                'email_otp'     => 'required|digits:6|integer',
                'type'          => 'required'
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status_code' => 203
            ]);
        }

        if (!$customer) {
            return response(json_encode([
                'status_code' => 202,
                'message' => 'Sorry, no account exists with this mobile number.'
            ]), 202)->header('Content-Type', 'text/plain');
        }

        $login_attempt_data_check = UserLoginAttemptCount::where('user_id', $customer->id)->first();
        $login_attempt_check = UserLoginAttemptCount::where([
            'user_id' => $customer->id,
            'forget_pwd_otp_attempts' => 10
        ])->first();

        if (!empty($login_attempt_check)) {
            $to = Carbon::createFromFormat('Y-m-d H:i:s', $login_attempt_check->otp_attempts_updated_at);
            $from = Carbon::now();
            $diff_in_minutes = $to->diffInMinutes($from);
        }

        /* ============================================================
            ACCOUNT VERIFICATION FLOW  ✅ UPDATED
            ============================================================ */
        if ($request->type == 'account') {

            Logger("customer forget pwd otp verify api account type");

            /* 🔴 MOBILE OTP CHECK (ADDED – SAFE) */
            if (isset($customer->mobile_otp) && isset($request->mobile_otp)) {
                if ($customer->mobile_otp != $request->mobile_otp) {
                    return response(json_encode([
                        'status_code' => 403,
                        'message' => 'Invalid Mobile OTP! Please try again.'
                    ]), 200)->header('Content-Type', 'text/plain');
                }
            }

            /* 🔴 EMAIL OTP CHECK (ADDED – SAFE) */
            if (isset($customer->email_otp) && isset($request->email_otp)) {
                if ($customer->email_otp != $request->email_otp) {
                    return response(json_encode([
                        'status_code' => 403,
                        'message' => 'Invalid Email OTP! Please try again.'
                    ]), 200)->header('Content-Type', 'text/plain');
                }
            } 

            /* 🔴 OTP EXPIRY CHECK (UNCHANGED) */
            if (strtotime($customer->valid_upto) < strtotime(now())) {
                
                $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600);
                $customer->update();
 
                Mail::to($customer->email)->send(new SendOtp($customer));
                sendCustomerMobileSms('send_otp', $customer, "", "");
 
                return response(json_encode([
                    'status_code' => 403,
                    'message' => 'Your OTP has been expired. We have sent a new OTP to your registered mobile number and email.'
                ]), 200)->header('Content-Type', 'text/plain');
            }

            /* 🔴 EXISTING ACCOUNT CHECK (UNCHANGED) */
            if ($existing = Customers::where('email', $customer->email)->first()) {
                return response(json_encode([
                    'status_code' => 200,
                    'message' => 'Your account already verified.',
                    'data' => $existing
                ]), 200)->header('Content-Type', 'text/plain');
            }

            /* 🔴 ACCOUNT CREATION (UNCHANGED – YOUR CODE) */
            $customer_data = new Customers;
            $customer_data->title = ($customer->title == "Mrs." || $customer->title == "Mrs") ? "Ms." : $customer->title;
            $customer_data->customer_type = $customer->customer_type;
            $customer_data->first_name = $customer->first_name;
            $customer_data->middle_name = $customer->middle_name;
            $customer_data->last_name = $customer->last_name;
            $customer_data->mobile_number = $customer->mobile_number;
            $customer_data->email = $customer->email;
            $customer_data->is_verified = true;
            $customer_data->password = $customer->password;
            $customer_data->password_updated_at = Carbon::now();
            $customer_data->is_expired = 0;
            $customer_data->account_verify_at = Carbon::now();
            $customer_data->device_token = $customer->device_token;
            $customer_data->app_version = $customer->app_version;
            $customer_data->platform = $customer->platform;
            $customer_data->save();

            $pass = new PasswordHistory();
            $pass->customer_id = $customer_data->id;
            $pass->password = $customer->password;
            $pass->save();
            $hospital_get_data = HospitalTemp::whereIn('id', explode(',', $customer->hospital_id))->get();
            if($hospital_get_data){
                $hospitalIds = [];
                foreach ($hospital_get_data as $hospital_req) {
                    $hospital = new Hospitals();
                    $hospital->hospital_name = $hospital_req->hospital_name;
                    $hospital->dept_id = $hospital_req->dept_id;
                    $hospital->address = $hospital_req->address;
                    $hospital->city = $hospital_req->city;
                    $hospital->state = $hospital_req->state;
                    $hospital->zip = $hospital_req->zip;
                    $hospital->country = $hospital_req->country;
                    $hospital->other_department_name = $hospital_req->other_department_name;
                    $hospital->customer_id = $customer_data->id;
                    $hospital->responsible_branch = (array_key_exists($hospital->state, \Config('oly.responsible_branches'))) ? \Config('oly.responsible_branches')[$hospital->state] : \Config('oly.default_responsible_branch');
                    $hospital->save();
                    $hospitalIds[]=$hospital->id;
                }

                $customer_data->hospital_id = implode(',', $hospitalIds);
                $customer_data->customer_id = sprintf("%08s", $customer_data->id);

                // if (isset($request->device_token) && !is_null($request->device_token) ) {
                //     $customer_data->device_token = $request->device_token;
                // }
                // if (isset($request->platform) && !is_null($request->platform) ) {
                //     $customer_data->platform = $request->platform;
                // }
                // if (isset($request->app_version) && !is_null($request->app_version)) {
                //     $customer_data->app_version = $request->app_version;
                // }

                $customer_data->update();
            }
            $customer_get = Customers::where('id', $customer_data->id)->first();
            $hospitals = Hospitals::where('customer_id', $customer_get->id)->get();
            foreach ($hospitals as $hospital) {
                $departments = Departments::whereIn('id', explode(',', $hospital->dept_id))->get();
                $hospital->deptAry = $departments;
            }

            if(isset($customer_data)){ 
                if ($customer_data->is_verified) {
                    $customer_data->hospitalAry = $hospitals;

                    // $customer_data->days = $customer_data->account_verify_at ? Carbon::parse($customer_data->account_verify_at)->diffInDays(now()) : 0;

                    // $customer_data->is_popup_show = 0;
                    // $customer_data->is_mandatory  = 0;

                    // if ($customer_data->days > 0 && $customer_data->days < 15) {
                    
                    //     $customer_data->is_popup_show = 1;
                    //     $customer_data->is_mandatory  = 0;
                    // }
                    // elseif($customer_data->days == 0) {
                        
                    //     $customer_data->is_popup_show = 1;
                    //     $customer_data->is_mandatory  = 1;
                    // }
                    $kycDays = 90;

                    if ($customer_data->account_verify_at) {

                        $verifyDate = Carbon::parse($customer_data->account_verify_at)->startOfDay();
                        $expiryDate = $verifyDate->copy()->addDays($kycDays);

                        $daysLeft = Carbon::today()->diffInDays($expiryDate, false);

                        $customer_data->days = $daysLeft > 0 ? $daysLeft : 0;

                    } else {
                        $customer_data->days = 0;
                    }
                    //$customer_data->days = $days;
                    $customer_data->is_popup_show = 0;
                    $customer_data->is_mandatory  = 0;

                    if ($customer_data->days > 0 && $customer_data->days < 15) {
                        $customer_data->is_popup_show = 1;
                        $customer->is_kyc_message = "Please Update Your KYC";
                    } elseif ($customer_data->days === 0) {
                        $customer_data->is_popup_show = 1;
                        $customer_data->is_mandatory  = 1;
                        $customer->is_kyc_message = "Please Update Your KYC";
                    }

                }

                $access_token = JWTAuth::fromUser($customer_data);
                Customers::where('id', $customer_data->id)->update([
                    'jwt_token' => $access_token,
                ]); 

                $customer = Customers::where('id', $customer_data->id)->first();
                $respArr['status_code'] = 200;
                $respArr['message'] = 'Your account has been verified successfully.';
                $respArr['data'] = $customer_data->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);
                $respArr['access_token'] = $access_token;
                $respArr['token_type'] = 'bearer';
                $respArr['expires_in'] = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');
                
                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');

                // return response(json_encode([ 
                //     'data' => $respArr
                // ]), 200)->header('Content-Type', 'text/plain');
            }else{
                $respArr['status_code'] = 202;
                $respArr['message'] = 'Somethingh went wrong.';
                $respArr['data'] = null;
                $respArr['access_token'] = null;
                $respArr['token_type'] = null;
                $respArr['expires_in'] = null; 
                return response(json_encode($respArr), 202)->header('Content-Type', 'text/plain');
                // return response(json_encode([ 
                //     'data' => $respArr
                // ]), 202)->header('Content-Type', 'text/plain'); 
            } 
        }

        /* ===========================
           PASSWORD RESET FLOW
           =========================== */
        Logger("customer forget pwd otp verify api password update");

        if ($request->type == 'forgot-password-verify') {
            /* 🔴 MOBILE OTP CHECK (NEW) */
            if ($customer->mobile_otp != $request->mobile_otp) {

                $login_attempt = UserLoginAttemptCount::where('user_id', $customer->id)->first();

                if (!$login_attempt) {
                    //$left_attempt = 9;
                    $left_attempt = 2;
                    UserLoginAttemptCount::create([
                        'user_id' => $customer->id,
                        'forget_pwd_otp_attempts' => 1,
                        'otp_attempts_updated_at' => Carbon::now(),
                    ]);
                } else {
                    $attempts = $login_attempt->forget_pwd_otp_attempts + 1;
                    $left_attempt = 3 - $attempts;

                    if ($login_attempt->forget_pwd_otp_attempts < 3) {
                        $login_attempt->update([
                            'forget_pwd_otp_attempts' => $attempts,
                            'otp_attempts_updated_at' => Carbon::now(),
                        ]);
                    }
                }

                return response(json_encode([
                    'status_code' => 403,
                    'message' => "Invalid Mobile OTP!\nPlease try again.\nYou have $left_attempt attempts remaining"
                ]), 200)->header('Content-Type', 'text/plain');
            }
 
            /* 🔴 MOBILE OTP CHECK (ADDED – SAFE) */
            if (isset($customer->mobile_otp) && isset($request->mobile_otp)) {
                if ($customer->mobile_otp != $request->mobile_otp) {
                    return response(json_encode([
                        'status_code' => 403,
                        'message' => 'Invalid Mobile OTP! Please try again.'
                    ]), 200)->header('Content-Type', 'text/plain');
                }
            }

            /* 🔴 EMAIL OTP CHECK (ADDED – SAFE) */
            if (isset($customer->email_otp) && isset($request->email_otp)) {
                if ($customer->email_otp != $request->email_otp) {
                    return response(json_encode([
                        'status_code' => 403,
                        'message' => 'Invalid Email OTP! Please try again.'
                    ]), 200)->header('Content-Type', 'text/plain');
                }
            }

            /* 🔴 LOCK CHECK (UNCHANGED) */
            if (!empty($login_attempt_check) && $diff_in_minutes < 15) {
                return response(json_encode([
                    'status_code' => 403,
                    'message' => 'You have exhausted all your attempts to enter the incorrect OTP. Please try again after 15 minutes.'
                ]), 200)->header('Content-Type', 'text/plain');
            }

            /* 🔴 OTP EXPIRY CHECK (UNCHANGED) */
            if (strtotime($customer->valid_upto) < strtotime(now())) {

                $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600);
                $customer->update();

                Mail::to($customer->email)->send(new SendOtp($customer));
                sendCustomerMobileSms('send_otp', $customer, "", "");

                return response(json_encode([
                    'status_code' => 403,
                    'message' => 'Your OTP has been expired. We have sent a new OTP.'
                ]), 200)->header('Content-Type', 'text/plain');
            }

            /* 🔴 SUCCESS */
            UserLoginAttemptCount::where('user_id', $customer->id)->update(['forget_pwd_otp_attempts' => 0]);

            $token = Str::random(80);
            $customer->otp_code = null;
            $customer->mobile_otp = null;
            $customer->email_otp = null;
            $customer->valid_upto = null; 
            $customer->access_token = $token; 
            $customer->save();

            return response(json_encode([
                'status_code' => 200,
                'message' => 'Your account has been verified successfully.',
                'data' => $customer,
                'password_access_token' => $token
            ]), 200)->header('Content-Type', 'text/plain');
        }

        /* ===========================
           Account KYC FLOW
           =========================== */
        if ($request->type == 'verify-account-kyc') {
            /* 🔴 MOBILE OTP CHECK (NEW) */
            
            //Comment for testing purpose
            /*if ($customer->mobile_otp != $request->mobile_otp) {

                $login_attempt = UserLoginAttemptCount::where('user_id', $customer->id)->first();

                if (!$login_attempt) {
                    $left_attempt = 9;
                    UserLoginAttemptCount::create([
                        'user_id' => $customer->id,
                        'forget_pwd_otp_attempts' => 1,
                        'otp_attempts_updated_at' => Carbon::now(),
                    ]);
                } else {
                    $attempts = $login_attempt->forget_pwd_otp_attempts + 1;
                    $left_attempt = 10 - $attempts;

                    if ($login_attempt->forget_pwd_otp_attempts < 10) {
                        $login_attempt->update([
                            'forget_pwd_otp_attempts' => $attempts,
                            'otp_attempts_updated_at' => Carbon::now(),
                        ]);
                    }
                }

                return response(json_encode([
                    'status_code' => 403,
                    'message' => "Invalid Mobile OTP!\nPlease try again.\nYou have $left_attempt attempts remaining"
                ]), 200)->header('Content-Type', 'text/plain');
            }*/


            /* 🔴 MOBILE OTP CHECK (ADDED – SAFE) */
            if (isset($customer->mobile_otp) && isset($request->mobile_otp)) {
                if ($customer->mobile_otp != $request->mobile_otp) {
                    return response(json_encode([
                        'status_code' => 403,
                        'message' => 'Invalid Mobile OTP! Please try again.'
                    ]), 200)->header('Content-Type', 'text/plain');
                }
            }

            /* 🔴 EMAIL OTP CHECK (ADDED – SAFE) */
            if (isset($customer->email_otp) && isset($request->email_otp)) {
                if ($customer->email_otp != $request->email_otp) {
                    return response(json_encode([
                        'status_code' => 403,
                        'message' => 'Invalid Email OTP! Please try again.'
                    ]), 200)->header('Content-Type', 'text/plain');
                }
            }

            //Comment for testing purpose
            
            /* 🔴 LOCK CHECK (UNCHANGED) */
            
            /*if (!empty($login_attempt_check) && $diff_in_minutes < 15) {
               
                return response(json_encode([
                    'status_code' => 403,
                    'message' => 'You have exhausted all your attempts to enter the incorrect OTP. Please try again after 15 minutes.'
                ]), 200)->header('Content-Type', 'text/plain');
            
            }*/

            /* 🔴 OTP EXPIRY CHECK (UNCHANGED) */
            if (strtotime($customer->valid_upto) < strtotime(now())) {

                $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600);
                $customer->update();

                Mail::to($customer->email)->send(new SendOtp($customer));
                sendCustomerMobileSms('send_otp', $customer, "", "");

                return response(json_encode([
                    'status_code' => 403,
                    'message' => 'Your OTP has been expired. We have sent a new OTP.'
                ]), 200)->header('Content-Type', 'text/plain');
            }

            /* 🔴 SUCCESS */ 

            $token = Str::random(80);
            $customer->otp_code = null;
            $customer->mobile_otp = null;
            $customer->email_otp = null;
            $customer->valid_upto = null; 
            $customer->access_token = null;
            $customer->account_verify_at = Carbon::now(); 
            $customer->save();

            if(isset($customer)){ 
                // Send KYC success mail
                if (!empty($customer->email)) {
                    try {
                        Mail::to($customer->email)
                            ->send(new \App\Mail\Revamp\SendKYCVerificationMail($customer));
                    } catch (\Exception $e) {
                        \Log::error('KYC Mail Failed: ' . $e->getMessage());
                    } 
                }

                $access_token = JWTAuth::fromUser($customer);
                
                Customers::where('id', $customer->id)->update([
                    'jwt_token' => $access_token,
                ]); 

                $get_customer_dtl = Customers::where('id', $customer->id)->first();
                $respArr['status_code'] = 200;
                $respArr['message'] = 'Your account has been verified successfully.';
                $respArr['data'] = $get_customer_dtl->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);
                $respArr['access_token'] = $access_token;
                $respArr['token_type'] = 'bearer';
                $respArr['expires_in'] = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');

                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                
            }else{
                
                $respArr['status_code'] = 202;
                $respArr['message'] = 'Somethingh went wrong.';
                $respArr['data'] = null;
                $respArr['access_token'] = null;
                $respArr['token_type'] = null;
                $respArr['expires_in'] = null; 

                return response(json_encode($respArr), 202)->header('Content-Type', 'text/plain'); 
            } 
        }
    }

    public function mpinBiometricUpdate(Request $request)
    {
        Logger("customer mpin biometric update api");
        Logger($request->all());

        $user = auth('customer-api')->user();

        if (count((array)$user) > 0) {

            if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
                return response()->json([
                    'status'  => 423,
                    'status_code' => 423,
                    'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
                ]);
            }

            if ($user->is_expired == 0) {

                $rules = [ 
                    'is_face_id'  => 'nullable|in:0,1|required_without:is_mpin',
                    'is_mpin'     => 'nullable|in:0,1|required_without:is_face_id',
                ];

                $messages = [
                    'is_face_id.required_without' => 'Either Face ID or MPIN is required.',
                    'is_mpin.required_without'    => 'Either Face ID or MPIN is required.',
                ];

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return response()->json([
                        'message' => $validator->errors()->first(),
                        'status_code' => 203
                    ]);
                }

                // /* 🔐 SECURITY CHECK */
                // if ($user->id != $request->customer_id) {
                //     return response()->json([
                //         'status_code' => 403,
                //         'message' => 'Unauthorized request'
                //     ]);
                // }

                /* 🔄 UPDATE ONLY WHAT APP SENDS */
                $updateData = [];
                $messages   = [];

                if ($request->has('is_face_id')) {
                    $updateData['is_face_id'] = $request->is_face_id;
                    $messages[] = $request->is_face_id == 1
                        ? 'Face ID activated successfully.'
                        : 'Face ID deactivated successfully.';
                }

                if ($request->has('is_mpin')) {
                    $updateData['is_mpin'] = $request->is_mpin;
                    $messages[] = $request->is_mpin == 1
                        ? 'MPIN activated successfully.'
                        : 'MPIN deactivated successfully.';
                }

                if (empty($updateData)) {
                    return response()->json([
                        'status_code' => 400,
                        'message' => 'No changes provided.'
                    ]);
                }

                Customers::where('id', $user->id)->update($updateData);

                $updatedUser = Customers::find($user->id)->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);

                return response()->json([
                    'status_code' => 200,
                    'message' => implode(' ', $messages),
                    'data' => $updatedUser
                ]);

            } else {
                return response()->json([
                    'status_code' => 407,
                    'message' => 'password expired',
                    'is_expired' => $user->is_expired
                ]);
            }

        } else {
            return response()->json([
                'status_code' => 400,
                'message' => 'user not found',
            ]);
        }
    }

    public function customerSignUp(Request $request)
    {
        $new_hospitalAry = $request->hospitalAry;
        $hospitalAry = json_decode($request->hospitalAry, true);
        $request->merge(['hospitalAry' => $hospitalAry]);
        if(!is_array($request->hospitalAry)){
            return response()->json(['message' =>  'hospital ary must be an array', 'status_code' => 203 ]);
        }
        try {
            $chk_mobile = Customers::where('mobile_number', $request->mobile_number)->whereNull('deleted_at')->first();
            
            if(!empty($chk_mobile)){
                return response()->json([
                    'status_code' => 203,
                    'message' => 'Mobile number already exists.',
                ]);
            }
            
            $chk_email = Customers::where('email', $request->email)->whereNull('deleted_at')->first();
            if(!empty($chk_email)){
                return response()->json([
                    'status_code' => 203,
                    'message' => 'Email already exists.',
                ]);
            }
            
            $validator = Validator::make($request->all(), [
                'customer_type' => 'nullable',
                'title' => 'required',
                'first_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                'middle_name' => 'regex:/^[a-zA-Z\s]*$/',
                'last_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'email' => 'required|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
                'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/',
                'device_token' => [
                    'required',
                    'string',
                    'min:20',
                    'max:255',
                    'regex:/^[A-Za-z0-9\-\_\:\.]+$/'
                ],


                'platform'    => 'required|string|regex:/^[a-zA-Z\s]*$/',
                'app_version' => 'required|string|regex:/[0-9]/|regex:/[.]/',
                "hospitalAry" => "required",
                "hospitalAry.*.address" => "required|regex:/^[0-9A-Za-z:#&@\/\-.\s,'-()]*$/",
                "hospitalAry.*.city" => "required|regex:/^[a-zA-Z\s]*$/",
                "hospitalAry.*.country" => "required|regex:/^[a-zA-Z\s]*$/",
                "hospitalAry.*.hospital_name" => "required|regex:/^[0-9A-Za-z&@.\s,'-()]*$/",
                //"hospitalAry.*.dept_id" => "required", 
                "hospitalAry.*.dept_id"  => [
                    "required",
                    "string",
                    "regex:/^[0-9]+(,[0-9]+)*$/"
                ],
                "hospitalAry.*.zip" => "required|digits:6|integer",
                "hospitalAry.*.other_department_name" => "nullable|regex:/^[a-zA-Z\s]*$/",
                "hospitalAry.*.state" => "required|regex:/^[a-zA-Z\s]*$/",
            ],[
                'hospitalAry.*.address.regex' => 'The address name(:input) is invalid. Special characters are not allowed in the address name.',
                'hospitalAry.*.city.regex' => 'The city name(:input) is invalid. Special characters are not allowed in the city name.',
                'hospitalAry.*.country.regex' => 'The country name(:input) is invalid. Special characters are not allowed in the country name.',
                'hospitalAry.*.hospital_name.regex' => 'The hospital name(:input) is invalid. Special characters are not allowed in the hospital name.',
                "mobile_number.required" => "Mobile number is required",
                "mobile_number.unique" => "Mobile number already exists",
                "password.required"=>"Password is required",
                "email.required"=>"Email is required",
                "email.unique" => "Email already exists",
                "password.*"=>"Invalid password. Password should be in minimum 8 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
            ]);

            Logger("customer store");
            Logger($request->all());

            if($validator->fails()) {
                $errors = $validator->errors();

                return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
            }else{
                $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
                $string = $password;

                $chk_pass_space = $request->get('password');
                if(str_contains($chk_pass_space, ' ')){
                    Logger("customer store 1");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use space in your password.',
                    ]);
                }

                $blacklistArray = ['abc', 'bcd', 'cde', 'def', 'efg', 'fgh', 'ghi', 'hij', 'Ijk', 'jkl', 'klm', 'lmn', 'mno', 'nop', 'opq', 'pqr', 'qrs', 'rst', 'stu', 'tuv', 'uvw', 'vwx', 'wxy', 'xyz', 'yza', 'zab','abc','ABC', 'BCD', 'CDE', 'DEF', 'EFG', 'FGH', 'GHI', 'HIJ', 'IJK', 'JKL', 'KLM', 'LMN', 'MNO', 'NOP', 'OPQ', 'PQR', 'QRS', 'RST', 'STU', 'TUV', 'UVW', 'VWX', 'WXY', 'XYZ', 'YZA', 'ZAB','ABC'];
                $flag = false;
                foreach ($blacklistArray as $k => $v) {
                    if(str_contains($string, $v)){
                        $flag = true;
                        break;
                    }
                }
                if ($flag == true) {
                    Logger("customer store 2");

                    return response()->json([
                        'status_code' => 203,
                        'message' => 'Also, password should not contain 3 sequence alphabetic characters. For eg: abc, bcd etc.',
                    ]);
                }

                $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
                $string = $password;

                $first_name = strtolower($request->first_name);
                $last_name = strtolower($request->last_name);

                $email = strtolower($request->email);
                $mobile_number = $request->mobile_number;

                $parts = explode('@', $email);
                $namePart = $parts[0];
 
                $mobile_number_parts = last(explode('91', $request->mobile_number));
                $mobileamePart = $mobile_number_parts;

                $strippedNumber = substr($request->mobile_number, 2); // Removing "91" prefix
                 
                $first_name_match = explode(' ', $first_name);
                $last_name_match = explode(' ', $last_name);

                $first_lat_name_flag = false;
                if(str_contains(strtolower($string), strtolower($first_name.$last_name))){
                    $first_lat_name_flag = true;
                }

                if ($first_lat_name_flag == true) {
                    Logger("customer store 3");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use name, email and phone number in password.',
                    ]);
                }

                $first_name_flag = false;
                foreach($first_name_match as $first_name_matchs){
                    if(str_contains(strtolower($string), strtolower($first_name_matchs))  && $first_name_match != ""){
                        $first_name_flag = true;
                        break;
                    }
                }

                if ($first_name_flag == true) {
                    Logger("customer store 4");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use name, email and phone number in password.',
                    ]);
                }

                $last_name_flag = false;
                foreach($last_name_match as $last_name_matchs){
                    if(str_contains(strtolower($string), strtolower($last_name_matchs))  && $last_name_matchs != ""){
                        $last_name_flag = true;
                        break;
                    }
                }

                if ($last_name_flag == true) {
                    Logger("customer store 5");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use name, email and phone number in password.',
                    ]);
                }

                $email_flag = false;
                if(str_contains(strtolower($request->get('password')), strtolower($email))){
                    $email_flag = true;
                }
                if ($email_flag == true) {
                    Logger("customer store 6");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use name, email and phone number in password.',
                    ]);
                }

                $email_flag_start = false;
                if(str_contains(strtolower($request->get('password')), strtolower($namePart))){
                    $email_flag_start = true;
                }
                if ($email_flag_start == true) {
                    Logger("customer store 7");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use name, email and phone number in password.',
                    ]);
                }

                $chk_email_rule = preg_split("/[?&@#.]/", $namePart);

                $chk_email_rule_flag = false;
                foreach($chk_email_rule as $chk_email_rules){
                    if(str_contains(strtolower($string), strtolower($chk_email_rules))){
                        $chk_email_rule_flag = true;
                        break;
                    }
                }
                if ($chk_email_rule_flag == true) {
                    Logger("customer store 8");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use name, email and phone number in password.',
                    ]);
                }


                $mobile_number_flag = false;
                if(strpos($request->get('password'), $strippedNumber) === true){
                    $mobile_number_flag = true;
                } 

                if ($mobile_number_flag == true) {
                    Logger("customer store 9");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use name, email and phone number in password.',
                    ]);
                }


                else{
                    Logger("customer store 10");

                    $customer = new CustomerTemp;
                    $customer->title =  ($request->title == "Mrs." || $request->title == "Mrs") ? "Ms." : $request->title;
                    $customer->customer_type = $request->customer_type;
                    $customer->first_name = $request->first_name;
                    $customer->middle_name = $request->middle_name;
                    $customer->last_name = $request->last_name;
                    $customer->mobile_number = $request->mobile_number;
                    $customer->email = $request->email;
                    $customer->is_verified = false;
                    $customer->device_token = $request->device_token;
                    $customer->app_version = $request->app_version;
                    $customer->platform = $request->platform;
                    $customer->password = Hash::make($request->password);
                    $customer->save();

                    //$customer->otp_code = mt_rand(100000, 999999); //random code for otp
                    $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                    $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                    $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600);
                    $customer->customer_id = sprintf("%08s", $customer->id);
                    $customer->update();

                    $data = CustomerTemp::whereId($customer->id)->first();
                   
                    if (isset($request->hospitalAry)) {

                        //blank array to store hospital IDs created
                        $hospitalIds = [];
                        
                        //create all hospitals
                        foreach (json_decode($new_hospitalAry, true) as $hospital_req) {
                            if (isset($hospital_req['id'])) {
                                $hospital = HospitalTemp::findOrFail($hospital_req['id']);
                                if (empty($hospital)) {
                                    $hospital = new HospitalTemp;
                                }
                                $hospital->hospital_name = $hospital_req['hospital_name'];
                                $hospital->dept_id = $hospital_req['dept_id'];
                                $hospital->address = $hospital_req['address'];
                                $hospital->city = $hospital_req['city'];
                                $hospital->state = $hospital_req['state'];
                                $hospital->zip = $hospital_req['zip'];
                                $hospital->country = $hospital_req['country'];
                                $hospital->other_department_name = $hospital_req['other_department_name'] ?? null;
                                $hospital->customer_id = $customer->id;
                                $hospital->responsible_branch = (array_key_exists($hospital->state, \Config('oly.responsible_branches'))) ? \Config('oly.responsible_branches')[$hospital->state] : \Config('oly.default_responsible_branch');
                                $hospital->save();
                                $hospitalIds[]=$hospital->id;
                            } else {
                                $hospital = new HospitalTemp;
                                $hospital->hospital_name = $hospital_req['hospital_name'];
                                $hospital->dept_id = $hospital_req['dept_id'];
                                $hospital->address = $hospital_req['address'];
                                $hospital->city = $hospital_req['city'];
                                $hospital->state = $hospital_req['state'];
                                $hospital->zip = $hospital_req['zip'];
                                $hospital->country = $hospital_req['country'];
                                $hospital->other_department_name = $hospital_req['other_department_name'] ?? null;
                                $hospital->customer_id = $customer->id;
                                $hospital->responsible_branch = (array_key_exists($hospital->state, \Config('oly.responsible_branches'))) ? \Config('oly.responsible_branches')[$hospital->state] : \Config('oly.default_responsible_branch');
                                $hospital->save();
                                $hospitalIds[]=$hospital->id;
                            }
                        }
                        //update customer model with the hospital IDs
                        $customer->hospital_id = implode(',', $hospitalIds);
                        $customer->save();
                        $data->hospitalAry = HospitalTemp::whereIn('id', $hospitalIds)->get();
                        foreach ($data->hospitalAry as $hos) {
                            $departments = Departments::whereIn('id', explode(',', $hos->dept_id))->get();
                            $hos->deptAry = $departments;
                        }
                    }

                    Mail::to($customer->email)->send(new SendOtp($customer));
                    sendCustomerMobileSms('send_otp', $customer, "", "");
                    $respArr['status_code'] = 200;
                    $respArr['message'] = 'Your Data has been saved successfully.';
                    $respArr['id'] = $customer->id;
                    $respArr['otp_code'] = $customer->otp_code;
                    $respArr['customer_temp_id'] = $customer->id;
                    $respArr['data'] = $data;

                    Logger("customer store success");
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            }
        }catch (Exception $e) {
            $respArr['message'] = 'Invalid Request Data';
            $respArr['status_code'] = 400;
            return response(json_encode($respArr), 400)->header('Content-Type', 'text/plain');
        }
    }

    public function customerSignUpResendOtp(Request $request)
    {   
        $rules = [
            'email'        => 'required|email|exists:customer_temps,email', 
            'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/|exists:customer_temps,mobile_number' 
        ];
        
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
        ];

        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['status_code' => 203, 'message' => $validator->errors()->first()]);
        }else{ 
            $customer = CustomerTemp::where('email', strtolower($request->email))->where('mobile_number', $request->mobile_number)->first();
            if (!is_null($customer)) {
                $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600); 
                $customer->save(); 

                $customer_data = CustomerTemp::where('id', strtolower($customer->id))->first();

                Mail::to($customer_data->email)->send(new SendOtp($customer_data));
                sendCustomerMobileSms('send_otp', $customer_data, "", ""); 
                return Response::json(['status_code'=>200, 'message'=>'OTP Sent !']);
            } else {
                return Response::json(['status_code'=>403, 'message'=>'User Not Found !']);
            }
        } 
    }

    public function sendOtpBeforeLogin(Request $request)
    {
        $rules = [
            'email'        => 'required|email|exists:customers,email', 
            'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/|exists:customers,mobile_number', 
            'type' => 'required',
        ];
        
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
        ];

        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['status_code' => 203, 'message' => $validator->errors()->first()]);
        }else{ 

            if($request->type == "forgot_password"){
                $customer = Customers::where('email', strtolower($request->email))->where('mobile_number', $request->mobile_number)->first();
                if (!is_null($customer)) {
                    $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                    $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                    $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600); 
                    $customer->save(); 

                    $customer_data = Customers::where('id', strtolower($customer->id))->first();

                    Mail::to($customer_data->email)->send(new SendOtp($customer_data));
                    sendCustomerMobileSms('send_otp', $customer_data, "", ""); 
                    return Response::json(['status_code'=>200, 'message'=>'OTP Sent !']);
                } else {
                    return Response::json(['status_code'=>403, 'message'=>'User Not Found !']);
                }
            }elseif($request->type == "account_kyc"){
                $customer = Customers::where('email', strtolower($request->email))->where('mobile_number', $request->mobile_number)->first();
                if (!is_null($customer)) {
                    $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                    $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                    $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600); 
                    $customer->save(); 

                    $customer_data = Customers::where('id', strtolower($customer->id))->first();

                    Mail::to($customer_data->email)->send(new SendOtp($customer_data));
                    sendCustomerMobileSms('send_otp', $customer_data, "", ""); 
                    return Response::json(['status_code'=>200, 'message'=>'OTP Sent !']);
                } else {
                    return Response::json(['status_code'=>403, 'message'=>'User Not Found !']);
                }
            }
        }
    }


    public function customerPasswordUpdate(Request $request)
    {
        Logger("password update api request payload");
        Logger($request->all());

        $rules = [
            'auth_token' => 'required',
            'email'        => 'required|email|exists:customers,email', 
            //'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/|exists:customers,mobile_number',
            'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
        ];
        $messages = [
            'email.exists' => 'Sorry, no account exists with this email.',
            //'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
            'password.*'=>"Invalid password. Password should be in minimum 8 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
            $string = $password;

            $customerEmailCheck = Customers::where('email', $request->email)->first();
            $request->mobile_number = $customerEmailCheck->mobile_number;
            
            //$customerEmailCheck = Customers::where('mobile_number', $request->mobile_number)->first();
            if($customerEmailCheck->access_token == $request->auth_token){
                if (!empty($customerEmailCheck)) {
                    $chk_pass_space = $request->get('password');
                    if(str_contains($chk_pass_space, ' ')){

                        Logger("password update api You can not use space in your password");

                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use space in your password.',
                        ]);
                    }
                    $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
                    $string = $password;

                    $first_name = strtolower($customerEmailCheck->first_name);
                    $last_name = strtolower($customerEmailCheck->last_name);
                    $email = strtolower($customerEmailCheck->email);
                    $mobile_number = $customerEmailCheck->mobile_number;

                    $parts = explode('@', $email);
                    $namePart = $parts[0];

                    $mobile_number_parts = explode('91', $request->mobile_number);
                    $mobileamePart = $mobile_number_parts[1];

                    $strippedNumber = substr($request->mobile_number, 2);



                    $first_name_match = explode(' ', $first_name);
                    $last_name_match = explode(' ', $last_name);

                    $first_lat_name_flag = false;
                    if(str_contains(strtolower($string), strtolower($first_name.$last_name))){
                        $first_lat_name_flag = true;
                    }
                    if ($first_lat_name_flag == true) {
                        Logger("password update api You can not use name, email and phone number in password 1");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }
                    $first_name_flag = false;
                    foreach($first_name_match as $first_name_matchs){
                        if(str_contains(strtolower($string), strtolower($first_name_matchs)) && $first_name_match != ""){
                            $first_name_flag = true;
                            break;
                        }
                    }

                    if ($first_name_flag == true) {
                        Logger("password update api You can not use name, email and phone number in password 2");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $last_name_flag = false;
                    foreach($last_name_match as $last_name_matchs){
                        if(str_contains(strtolower($string), strtolower($last_name_matchs)) && $last_name_matchs != ""){
                            $last_name_flag = true;
                            break;
                        }
                    }
                    if ($last_name_flag == true) {
                        Logger("password update api You can not use name, email and phone number in password 3");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $email_flag = false;
                    if(str_contains(strtolower($request->get('password')), strtolower($email))){
                        $email_flag = true;
                    }
                    if ($email_flag == true) {
                        Logger("password update api You can not use name, email and phone number in password 4");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $email_flag_start = false;
                    if(str_contains(strtolower($request->get('password')), strtolower($namePart))){
                        $email_flag_start = true;
                    }
                    if ($email_flag_start == true) {
                        Logger("password update api You can not use name, email and phone number in password 5");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $chk_email_rule = preg_split("/[?&@#.]/", $namePart);

                    $chk_email_rule_flag = false;
                    foreach($chk_email_rule as $chk_email_rules){
                        if(str_contains(strtolower($string), strtolower($chk_email_rules))){
                            $chk_email_rule_flag = true;
                            break;
                        }
                    }
                    if ($chk_email_rule_flag == true) {
                        Logger("password update api You can not use name, email and phone number in password 6");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $mobile_number_flag = false;
                    // if(str_contains($request->get('password'), $mobileamePart)){
                    //     $mobile_number_flag = true;
                    // }
                    if(strpos($request->get('password'), $strippedNumber) === true){
                        $mobile_number_flag = true;
                    }
                    if ($mobile_number_flag == true) {
                        Logger("password update api You can not use name, email and phone number in password 7");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }
                }else{
                    return response()->json([
                        'status_code' => 400,
                        'message' => 'user not found',
                    ]);
                }
                $blacklistArray = ['abc', 'bcd', 'cde', 'def', 'efg', 'fgh', 'ghi', 'hij', 'Ijk', 'jkl', 'klm', 'lmn', 'mno', 'nop', 'opq', 'pqr', 'qrs', 'rst', 'stu', 'tuv', 'uvw', 'vwx', 'wxy', 'xyz', 'yza', 'zab','abc','ABC', 'BCD', 'CDE', 'DEF', 'EFG', 'FGH', 'GHI', 'HIJ', 'IJK', 'JKL', 'KLM', 'LMN', 'MNO', 'NOP', 'OPQ', 'PQR', 'QRS', 'RST', 'STU', 'TUV', 'UVW', 'VWX', 'WXY', 'XYZ', 'YZA', 'ZAB','ABC'];
                $flag = false;
                foreach ($blacklistArray as $k => $v) {
                    if(str_contains($string, $v)){
                        $flag = true;
                        break;
                    }
                }
                if ($flag == true) {
                    Logger("password update api You can not use name, email and phone number in password 8");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'Also, password should not contain 3 sequence alphabetic characters. For eg: abc, bcd etc.',
                    ]);
                }else{
                    $customer = Customers::where('mobile_number', $request->mobile_number)->first();
                    if ($customer) {
                        $total_password = PasswordHistory::where('customer_id', $customer->id)->count();
                        $old_pass_delete = PasswordHistory::where('customer_id', $customer->id)->latest()->take($total_password)->skip(5)->get();
                        foreach($old_pass_delete as $old_pass_deletes){
                            PasswordHistory::where('id',$old_pass_deletes->id)->delete();
                        }
                        $get_latest_password = PasswordHistory::where('customer_id', $customer->id)->orderBy('id','desc')->take(5)->get();
                        foreach($get_latest_password as $get_latest_passwords){
                            if (Hash::check($request->get('password'), $get_latest_passwords->password)) {
                                return Response::json(['status_code'=> 408,'message'=>'You can not use last 5 password.']);
                            }
                        }
                        Customers::where('id',$customer->id)->update([
                            'password' => bcrypt($request->get('password')),
                            'password_updated_at' => Carbon::now(),
                            'is_expired' => 0
                        ]);

                        //Insert Password In Password History Table
                        $pass = new PasswordHistory();
                        $pass->customer_id = $customer->id;
                        $pass->password = bcrypt($request->password);
                        $pass->save();

                        Customers::where('id',$customer->id)->update([
                            'access_token' => null
                        ]);

                        Logger("password update api success response");
                        return Response::json(['status_code'=>200,'message'=>'Password Updated']);
                    }else {
                        return Response::json(['status_code'=>403,'message'=>'User Not Found !']);
                    }
                }
            }else{ 
                return Response::json(['status_code' => 400, 'message'=>'Please try again !']);
            }
        }
    }

    public function customerUpdateProfile(Request $request)
    {

        Logger("customer update api");
        Logger($request->all());

        $new_hospitalAry = $request->hospitalAry;
        $hospitalAry = json_decode($request->hospitalAry, true);
        $request->merge(['hospitalAry' => $hospitalAry]);
        if(!is_array($request->hospitalAry)){
            return response()->json(['message' =>  'hospital ary must be an array', 'status_code' => 203 ]);
        }
        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
            if($user->is_expired == 0){
                $rules = [
                    'title' => 'required|regex:/^([a-zA-Z])(.)*$/',
                    'customer_type' => 'nullable',
                    'first_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                    'last_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                    'email' => 'nullable|email|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
                    'mobile_number' => 'nullable|regex:/^([+])(91)[0-9]{10}$/', 
                    "hospitalAry" => "required",
                    "hospitalAry.*.address" => "required|regex:/^[0-9A-Za-z:#&@\/\-.\s,'-()]*$/",
                    "hospitalAry.*.city" => "required|regex:/^[a-zA-Z\s]*$/",
                    "hospitalAry.*.country" => "required|regex:/^[a-zA-Z\s]*$/",
                    "hospitalAry.*.hospital_name" => "required|regex:/^[0-9A-Za-z&@.\s,'-()]*$/",
                    //"hospitalAry.*.dept_id" => "required", 
                    "hospitalAry.*.dept_id" => [
                        "required",
                        "string",
                        "regex:/^[0-9]+(,[0-9]+)*$/"
                    ],
                    "hospitalAry.*.other_department_name" => "nullable|regex:/^[a-zA-Z\s]*$/",
                    "hospitalAry.*.zip" => "required|digits:6|integer",
                    "hospitalAry.*.state" => "required|regex:/^[a-zA-Z\s]*$/",
                ];
                $messages = [
                    'hospitalAry.*.dept_id.required' => 'The department (:input) is required.',
                    'hospitalAry.*.address.regex' => 'The address name(:input) is invalid. Special characters are not allowed in the address name.',
                    'hospitalAry.*.city.regex' => 'The city name(:input) is invalid. Special characters are not allowed in the city name.',
                    'hospitalAry.*.country.regex' => 'The country name(:input) is invalid. Special characters are not allowed in the country name.',
                    'hospitalAry.*.hospital_name.regex' => 'The hospital name(:input) is invalid. Special characters are not allowed in the hospital name.',
                ];
                $validator = Validator::make( $request->all(), $rules,$messages);
                if ($validator->fails())
                {
                    return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
                }else{
                    $customer = Customers::findOrFail($user->id);

                    Logger("customer update api data");
                    Logger($customer);

                    if($request->email){

                        $customerEmailCheck = Customers::where('email',  $request->email)->first();

                        if($customer->email !=  $request->email){
                            $respArr['status_code'] = 401;
                            $respArr['message'] = 'You can not change your email.';
                            return response(json_encode($respArr), 401)->header('Content-Type', 'text/plain');
                        }
                    }

                    if($request->mobile_number){
                        $customerMobileCheck = Customers::where('mobile_number', $request->mobile_number)->first();
                        
                        if($customer->mobile_number !=  $request->mobile_number){
                            $respArr['status_code'] = 401;
                            $respArr['message'] = 'You can not change your mobile number.';
                            return response(json_encode($respArr), 401)->header('Content-Type', 'text/plain');
                        }
                    } 
                    
                    try {

                        $customer->title = $request->title;
                        $customer->customer_type = $request->customer_type;
                        $customer->first_name = $request->first_name;
                        $customer->middle_name = $request->middle_name;
                        $customer->last_name = $request->last_name; 

                        $customer->save();
                        if (isset($new_hospitalAry)) {
                            //blank array to store hospital IDs created
                            $hospitalIds = [];
                            
                            //create all hospitals
                            foreach ($hospitalAry as $hospital_req) {
                                // dd($hospital_req);
                                if (isset($hospital_req['id'])) {
                                    $hospital = Hospitals::findOrFail($hospital_req['id']);
                                    if (empty($hospital)) {
                                        $hospital = new Hospitals;
                                    }
                                    $hospital->hospital_name = $hospital_req['hospital_name'];
                                    $hospital->dept_id = $hospital_req['dept_id'];
                                    $hospital->address = $hospital_req['address'];
                                    $hospital->city = $hospital_req['city'];
                                    $hospital->state = $hospital_req['state'];
                                    $hospital->zip = $hospital_req['zip'];
                                    $hospital->country = $hospital_req['country'];
                                    $hospital->other_department_name = $hospital_req['other_department_name'] ?? null;
                                    $hospital->customer_id = $customer->id;
                                    $hospital->responsible_branch = (array_key_exists($hospital->state, \Config('oly.responsible_branches'))) ? \Config('oly.responsible_branches')[$hospital->state] : \Config('oly.default_responsible_branch');
                                    $hospital->save();
                                    $hospitalIds[]=$hospital->id;
                                } else {
                                    $hospital = new Hospitals;
                                    $hospital->hospital_name = $hospital_req['hospital_name'];
                                    $hospital->dept_id = $hospital_req['dept_id'];
                                    $hospital->address = $hospital_req['address'];
                                    $hospital->city = $hospital_req['city'];
                                    $hospital->state = $hospital_req['state'];
                                    $hospital->zip = $hospital_req['zip'];
                                    $hospital->country = $hospital_req['country'];
                                    $hospital->other_department_name = $hospital_req['other_department_name'] ?? null;
                                    $hospital->customer_id = $customer->id;
                                    $hospital->responsible_branch = (array_key_exists($hospital->state, \Config('oly.responsible_branches'))) ? \Config('oly.responsible_branches')[$hospital->state] : \Config('oly.default_responsible_branch');
                                    $hospital->save();
                                    $hospitalIds[]=$hospital->id;
                                }
                            }

                            //update customer model with the hospital IDs
                            $customer->hospital_id = implode(',', $hospitalIds);
                            $customer->save();
                            $customer->hospitalAry = Hospitals::whereIn('id', $hospitalIds)->get();
                            foreach ($customer->hospitalAry as $hos) {
                                $departments = Departments::whereIn('id', explode(',', $hos->dept_id))->get();
                                $hos->deptAry = $departments;
                            }
                            //NotifyCustomer::send_notification('account_update', '', $customer);

                            $respArr['status_code'] = 200;
                            $respArr['message'] = 'Your Data has been updated.'; 
                            $respArr['data'] = $customer->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);

                            Logger("customer update api response");
                            Logger($respArr);
                            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                        } else {
                            $respArr['status_code'] = 401;
                            $respArr['message'] = 'Hospital details is required.';  
                            return response(json_encode($respArr), 401)->header('Content-Type', 'text/plain');
                        }
                    }
                    catch (Exception $e) {
                    }

                }
            }else{
                return response()->json([
                    'status_code' => 407,
                    'message' => 'password expired',
                    'is_expired' => $user->is_expired
                ]);
            }
        }else {
            return response()->json([
                'status_code' => 400,
                'message' => 'user not found',
            ]);
        }
    }
    
    public function checkPasswordExpiredStatus(Request $request)
    {
        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {

            if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
                return response()->json([
                    'status'  => 423,
                    'status_code' => 423,
                    'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
                ]);
            }

            if($user->is_expired == 0){
                return response()->json([
                    'status_code' => 200,
                    'message' => 'success',
                    'is_expired' => $user->is_expired
                ]);
            }else{
                return response()->json([
                    'status_code' => 407,
                    'message' => 'Your password has been expired.Please reset your password now.',
                    'is_expired' => $user->is_expired
                ]);
            }
        }else {
            return response()->json([
                'status_code' => 400,
                'message' => 'user not found',
            ]);
        }
    }

    public function customerLogout(Request $request)
    {
         
        //Auth user from token (header)
        $user = auth('customer-api')->user();

        if (!$user) {
            return response()->json([
                'status_code' => 401,
                'message' => 'user not found',
            ]);
        }

        try {
            //Clear device token
            Customers::where('id', $user->id)->update([
                'jwt_token' => null,
                'device_token' => null,
            ]);

            //Invalidate JWT from header
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status_code' => 200,
                'message' => 'Logged out',
            ]);

        }catch (TokenExpiredException $e) {

            return response()->json([
                'status_code' => 401,
                'message' => 'Token expired',
            ], 401);

        } catch (TokenInvalidException $e) {

            return response()->json([
                'status_code' => 401,
                'message' => 'Token invalid',
            ], 401);

        } catch (JWTException $e) {

            return response()->json([
                'status_code' => 401,
                'message' => 'Token missing or malformed',
            ], 401);
        }
    } 

    public function historyCount(Request $request)
    {
        // 🔐 Auth check
        $user = auth('customer-api')->user();
        if (!$user) {
            return response()->json([
                'status_code' => 400,
                'message' => 'user not found',
            ]);
        }

        // ⛔ Password expired
        if ($user->is_expired == 1) {
            return response()->json([
                'status_code' => 407,
                'message' => 'password expired',
                'is_expired' => 1
            ]);
        }

        
        if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
            return response()->json([
                'status'  => 423,
                'status_code' => 423,
                'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
            ]);
        }

        $history = (object)[];
        $customer_id = $user->id;

        /* ================= CUSTOMER ================= */

        $customer = Customers::where('id', $customer_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$customer) {
            return response()->json([
                'status_code' => 400,
                'message' => 'customer not found',
            ]);
        }

        $customer->makeHidden([
            'sap_customer_id','otp_code','mobile_otp','email_otp','valid_upto',
            'is_testing','platform','app_version','created_at','updated_at',
            'is_expired','password_updated_at','access_token','is_deleted',
            'deleted_at','old_password','is_password_changed'
        ]);

        // ⏱ Days calculation
        // $days = $customer->account_verify_at
        //     ? Carbon::parse($customer->account_verify_at)->diffInDays(now())
        //     : 0;

        $kycDays = 90;

        if ($customer->account_verify_at) {

            $verifyDate = Carbon::parse($customer->account_verify_at)->startOfDay();
            $expiryDate = $verifyDate->copy()->addDays($kycDays);

            $daysLeft = Carbon::today()->diffInDays($expiryDate, false);

            $customer->days = $daysLeft > 0 ? $daysLeft : 0;

        } else {
            $customer->days = 0;
        }
        //$customer->days = $days;
        $customer->is_popup_show = 0;
        $customer->is_mandatory  = 0;
        $customer->is_kyc_message = null;
        if ($customer->days > 0 && $customer->days <= 15) {
            $customer->is_popup_show = 1;
            $customer->is_kyc_message = "Please Update Your KYC";
        } elseif ($customer->days === 0) {
            $customer->is_popup_show = 1;
            $customer->is_mandatory  = 1;
            $customer->is_kyc_message = "Please Update Your KYC";
        }

        $history->customer = $customer;

        /* ================= ONGOING COUNTS ================= */

        $history->ongoingCountService =
            ServiceRequests::where('customer_id', $customer_id)
                ->where('request_type', 'service')
                ->where('status', '!=', 'Closed')
                ->count()
            +
            ArchiveServiceRequests::where('customer_id', $customer_id)
                ->where('request_type', 'service')
                ->where('status', '!=', 'Closed')
                ->count();

        $history->ongoingCountAcademic =
            ServiceRequests::where('customer_id', $customer_id)
                ->where('request_type', 'academic')
                ->where('status', '!=', 'Closed')
                ->count()
            +
            ArchiveServiceRequests::where('customer_id', $customer_id)
                ->where('request_type', 'academic')
                ->where('status', '!=', 'Closed')
                ->count();

        $history->ongoingCountEnquiry =
            ServiceRequests::where('customer_id', $customer_id)
                ->where('request_type', 'enquiry')
                ->where('status', '!=', 'Closed')
                ->count()
            +
            ArchiveServiceRequests::where('customer_id', $customer_id)
                ->where('request_type', 'enquiry')
                ->where('status', '!=', 'Closed')
                ->count();

        /* ================= CLOSED COUNTS ================= */

        $history->closedCountAry = (object)[];

        $closedService = ServiceRequests::where('customer_id', $customer_id)
            ->where('status', 'Closed')
            ->whereNull('feedback_id')
            ->whereDate('created_at', '>', '2025-03-31');

        $closedArchive = ArchiveServiceRequests::where('customer_id', $customer_id)
            ->where('status', 'Closed')
            ->whereNull('feedback_id')
            ->whereDate('created_at', '>', '2025-03-31');

        $history->closedCountAry->count =
            $closedService->count() + $closedArchive->count();

        $history->closedCountAry->data =
            $closedService->select('cvm_id','request_type','sub_type','id','remarks','created_at','employee_code')
                ->get()
                ->merge(
                    $closedArchive->select('cvm_id','request_type','sub_type','id','remarks','created_at','employee_code')->get()
                );

        /* ================= INBOX ================= */

        $totalPromailers = Promailer::where('status', 1)->count();
        $shownPromailers = CustomerShowPromailer::where('customers_id', $customer_id)->count();

        $history->inboxCount = max($totalPromailers - $shownPromailers, 0);
        $history->inboxIds = Promailer::where('status', 1)->pluck('id')->toArray();

        /* ================= APP INFO ================= */

        $history->app_info = [
            'ios' => config('oly.current_version_iOS'),
            'android' => config('oly.current_version_android'),
            'is_app_update' => 0,
            'message' => "Dear customer!
Post-repair delivery acknowledgement is now available!
Update your app to access this feature."
        ];

        /* ================= EMPLOYEE INFO ================= */

        // foreach ($history->closedCountAry->data as $req) {
        //     if ($req->employee_code) {
        //         $emp = EmployeeTeam::getEmployee($req->employee_code);
        //         $req->employee_name = $emp->name ?? 'Employee -';
        //         $req->assigned_image = $emp->image
        //             ? config('app.url')."/storage/".$emp->image
        //             : config('app.url')."/storage/shared/employee_image.jpg";
        //     } else {
        //         $req->employee_name = 'Employee -';
        //         $req->assigned_image = config('app.url')."/storage/shared/employee_image.jpg";
        //     }
        // }
        foreach ($history->closedCountAry->data as $req) {
            $emp = !empty($req->employee_code)
                ? EmployeeTeam::getEmployee($req->employee_code)
                : null;
            $req->employee_name = $emp->name ?? 'Employee -';
            $req->assigned_image = (!empty($emp) && !empty($emp->image))
                ? config('app.url')."/storage/".$emp->image
                : config('app.url')."/storage/shared/employee_image.jpg";
        }

        return response()->json([
            'status_code' => 200,
            'history' => $history
        ]);
    }

    public function customerUpdateCustomerType(Request $request)
    {
        try {

            Logger("customer update customer type api");
            Logger($request->all());

            $user = auth('customer-api')->user();

            if (!$user) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'user not found',
                ]);
            }

            if ($user->is_expired == 1) {
                return response()->json([
                    'status_code' => 407,
                    'message' => 'password expired',
                    'is_expired' => $user->is_expired
                ]);
            }


            if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
                return response()->json([
                    'status'  => 423,
                    'status_code' => 423,
                    'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
                ]);
            }

            $rules = [
                'customer_type' => 'required|string|max:255',
            ];

            $messages = [
                'customer_type.required' => 'Customer type is required.',
            ];

            $validator = \Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 203,
                    'message' => $validator->errors()->first(),
                ]);
            }

            Logger("customer update customer type api data");
            Logger($user);

            $user->customer_type = $request->customer_type;
            $user->save();

            $respArr['status_code'] = 200;
            $respArr['message'] = 'Your Data has been updated.';
            $respArr['data'] = $user->makeHidden([
                'sap_customer_id',
                'otp_code',
                'mobile_otp',
                'email_otp',
                'valid_upto',
                'is_testing',
                'platform',
                'app_version',
                'created_at',
                'updated_at',
                'is_expired',
                'password_updated_at',
                'access_token',
                'is_deleted',
                'deleted_at',
                'old_password',
                'is_password_changed'
            ]);

            Logger("customer update api response");
            Logger($respArr);

            return response()->json($respArr);

        } catch (\Exception $e) {

            \Log::error('Customer Update Type API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ]);
        }
    }


    public function customerDeleteAccount(Request $request){
        $user = auth('customer-api')->user();

        if (!$user) {
            return response()->json([
                'status_code' => 400,
                'message' => 'user not found',
            ]);
        }

        if ($user->is_expired == 1) {
            return response()->json([
                'status_code' => 407,
                'message' => 'password expired',
                'is_expired' => $user->is_expired
            ]);
        } 

        $customer = Customers::where('id', $user->id)->whereNull('deleted_at')->first();
        
        if($customer) {
            
            Customers::where('id', $user->id)->update([
                'is_deleted' => 1,
                'deleted_at' => Carbon::now()
            ]);

            JWTAuth::invalidate($request->token);
            
            return response()->json([
                'status_code' => 200,
                'status_code' => 200,
                'message' => 'account deleted successfully',
            ]);
        }else {
            return response()->json([
                'status_code' => 404,
                'status_code' => 404,
                'message' => 'Not found',
            ]);
        }
             
    }


    public function kycSendOtp(Request $request)
    {   
       $user = auth('customer-api')->user();

        if (!$user) {
            return response()->json([
                'status_code' => 401,
                'message' => 'User not authenticated'
            ], 401);
        }

        if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
            return response()->json([
                'status'  => 423,
                'status_code' => 423,
                'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
            ]);
        }

        if (!empty($user->is_expired)) {
            return response()->json([
                'status_code' => 407,
                'message' => 'Password expired'
            ], 407);
        }

        $rules = [
            'email'        => 'required|email|exists:customers,email', 
            'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/|exists:customers,mobile_number',  
        ];
        
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
        ];

        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['status_code' => 203, 'message' => $validator->errors()->first()]);
        }else{  
            
            $customer = Customers::where('id', $user->id)->where('email', strtolower($request->email))->where('mobile_number', $request->mobile_number)->first();
            if (!is_null($customer)) {
                $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
                $customer->email_otp = mt_rand(100000, 999999); //random code for otp
                $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600); 
                $customer->save(); 

                $customer_data = Customers::where('id', strtolower($customer->id))->first();

                Mail::to($customer_data->email)->send(new SendOtp($customer_data));
                sendCustomerMobileSms('send_otp', $customer_data, "", ""); 
                return Response::json(['status_code'=>200, 'message'=>'OTP Sent !']);
            } else {
                return Response::json(['status_code'=>403, 'message'=>'User Not Found !']);
            } 
        }
    }

    public function kycAccountVerification(Request $request)
    {
        Logger("customer forget pwd otp verify api rquest payload");
        Logger($request->all());
        
        $user = auth('customer-api')->user();

        if (!$user) {
            return response()->json([
                'status_code' => 401,
                'message' => 'User not authenticated'
            ], 401);
        }

        if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
            return response()->json([
                'status'  => 423,
                'status_code' => 423,
                'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
            ]);
        }

        if (!empty($user->is_expired)) {
            return response()->json([
                'status_code' => 407,
                'message' => 'Password expired'
            ], 407);
        } 

       $customer = Customers::where('id', $user->id)->where('mobile_number', $request->mobile_number)->where('email', strtolower($request->email))->first();  
        
        $rules = [
            'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
            'mobile_otp'    => 'required|digits:6|integer',
            'email'         => 'required|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            'email_otp'     => 'required|digits:6|integer' 
        ]; 

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status_code' => 203
            ]);
        }

        if (!$customer) {
            return response(json_encode([
                'status_code' => 202,
                'message' => 'User Not Found !'
            ]), 202)->header('Content-Type', 'text/plain');
        }

        $login_attempt_data_check = UserLoginAttemptCount::where('user_id', $customer->id)->first();
        
        $login_attempt_check = UserLoginAttemptCount::where([
            'user_id' => $customer->id,
            'forget_pwd_otp_attempts' => 10
        ])->first();

        if (!empty($login_attempt_check)) {
            $to = Carbon::createFromFormat('Y-m-d H:i:s', $login_attempt_check->otp_attempts_updated_at);
            $from = Carbon::now();
            $diff_in_minutes = $to->diffInMinutes($from);
        } 

        /* 🔴 MOBILE OTP CHECK (ADDED – SAFE) */
        if (isset($customer->mobile_otp) && isset($request->mobile_otp)) {
            if ($customer->mobile_otp != $request->mobile_otp) {
                return response(json_encode([
                    'status_code' => 403,
                    'message' => 'Invalid Mobile OTP! Please try again.'
                ]), 200)->header('Content-Type', 'text/plain');
            }
        }

        /* 🔴 EMAIL OTP CHECK (ADDED – SAFE) */
        if (isset($customer->email_otp) && isset($request->email_otp)) {
            if ($customer->email_otp != $request->email_otp) {
                return response(json_encode([
                    'status_code' => 403,
                    'message' => 'Invalid Email OTP! Please try again.'
                ]), 200)->header('Content-Type', 'text/plain');
            }
        }

        /* 🔴 OTP EXPIRY CHECK (UNCHANGED) */
        if (strtotime($customer->valid_upto) < strtotime(now())) {

            $customer->mobile_otp = mt_rand(100000, 999999); //random code for otp
            $customer->email_otp = mt_rand(100000, 999999); //random code for otp
            $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600);
            $customer->update();

            Mail::to($customer->email)->send(new SendOtp($customer));
            sendCustomerMobileSms('send_otp', $customer, "", "");

            return response(json_encode([
                'status_code' => 403,
                'message' => 'Your OTP has been expired. We have sent a new OTP.'
            ]), 200)->header('Content-Type', 'text/plain');
        }

        /* 🔴 SUCCESS */ 

        $token = Str::random(80);
        $customer->otp_code = null;
        $customer->mobile_otp = null;
        $customer->email_otp = null;
        $customer->valid_upto = null; 
        $customer->access_token = null;
        $customer->account_verify_at = Carbon::now(); 
        $customer->save();

        if(isset($customer)){ 
            // Send KYC success mail
            if (!empty($customer->email)) {
                try {
                    Mail::to($customer->email)
                        ->send(new \App\Mail\Revamp\SendKYCVerificationMail($customer));
                } catch (\Exception $e) {
                    \Log::error('KYC Mail Failed: ' . $e->getMessage());
                } 
            }

            $access_token = JWTAuth::fromUser($customer);
            
            Customers::where('id', $customer->id)->update([
                'jwt_token' => $access_token,
            ]); 

            $get_customer_dtl = Customers::where('id', $customer->id)->first();
            $respArr['status_code'] = 200;
            $respArr['message'] = 'Your account has been verified successfully.';
            $respArr['data'] = $get_customer_dtl->makeHidden(['sap_customer_id', 'otp_code', 'mobile_otp', 'email_otp', 'valid_upto', 'is_testing', 'platform', 'app_version', 'created_at',  'updated_at', 'is_expired', 'password_updated_at', 'access_token', 'is_deleted', 'deleted_at', 'old_password', 'is_password_changed']);
            $respArr['access_token'] = $access_token;
            $respArr['token_type'] = 'bearer';
            $respArr['expires_in'] = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');

            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
            
        }else{
            
            $respArr['status_code'] = 202;
            $respArr['message'] = 'Somethingh went wrong.';
            $respArr['data'] = null;
            $respArr['access_token'] = null;
            $respArr['token_type'] = null;
            $respArr['expires_in'] = null; 

            return response(json_encode($respArr), 202)->header('Content-Type', 'text/plain'); 
        }  
    }
}
