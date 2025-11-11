<?php
namespace App\Http\Controllers;

use App\Models\Departments;
use App\Mail\SendOtp;
use App\Models\Customers;
use App\Models\Hospitals;
use App\NotifyCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mail;
use Response;
use Validator;

/**
 * @resource Customers
 *
 * All endpoints related to Customers
 */
class CustomersApiController extends Controller
{
    /**
     * Display All Customers
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customers::get();
        return $customers;
    }

    /**
     * Store a submitted user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $respArr = [];
        $customer = Customers::where('email', $request->email)->where('mobile_number', $request->mobile_number)->first();
        $customerEmailCheck = Customers::where('email', $request->email)->first();
        $customerMobileCheck = Customers::where('mobile_number', $request->mobile_number)->first();
        $MobileNumLen = strlen(str_replace("+91", "", $request->mobile_number));
        if (!is_null($customer)) {
            $respArr['status_code'] = 401;
            $respArr['message'] = 'Account already exists. Please try logging in with your existing credentials.';
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        } elseif ($MobileNumLen < 10) {
            $respArr['status_code'] = 401;
            $respArr['message'] = 'Mobile number Invalid. Please enter valid mobile number';
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        } elseif (!is_null($customerMobileCheck)) {
            $respArr['status_code'] = 401;
            $respArr['message'] = 'Mobile number already exists. Please try again.';
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        } elseif (!is_null($customerEmailCheck)) {
            $respArr['status_code'] = 401;
            $respArr['message'] = 'Email already exists in our database. Please try again.';
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        } else {
            try {
                $rules = [
                    'first_name' => 'required|string',
                    'middle_name' => 'string',
                    'last_name' => 'required|string',
                    'mobile_number' => 'required',
                    'email' => 'required|email',
                    'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
                ];
                $messages = [
                    'Password must contain at least one number and both uppercase and lowercase letters and atleast one special character (@$!%*#?&).',
                ];
                $validator = Validator::make( $request->all(), $rules, $messages);

                if ( $validator->fails() )
                {
                    return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
                }else{
                    $customer = new Customers;
                    $customer->title =  ($request->title == "Mrs." || $request->title == "Mrs") ? "Ms." : $request->title;
                    $customer->first_name = $request->first_name;
                    $customer->middle_name = $request->middle_name;
                    $customer->last_name = $request->last_name;
                    $customer->mobile_number = $request->mobile_number;
                    $customer->email = $request->email;
                    $customer->is_verified = false;
                    $customer->password = Hash::make($request->password);
                    $customer->save();
                }

                if (isset($request->hospitalAry)) {
                    $hospitalIds = [];
                    //create all hospitals
                    foreach (json_decode($request->hospitalAry, true) as $hospital_req) {
                        $hospital = new Hospitals;
                        $hospital->hospital_name = $hospital_req['hospital_name'];
                        $hospital->dept_id = $hospital_req['dept_id'];
                        $hospital->address = $hospital_req['address'];
                        $hospital->city = $hospital_req['city'];
                        $hospital->state = $hospital_req['state'];
                        $hospital->zip = $hospital_req['zip'];
                        $hospital->country = $hospital_req['country'];
                        $hospital->responsible_branch = (array_key_exists($hospital->state, \Config('oly.responsible_branches'))) ? \Config('oly.responsible_branches')[$hospital->state] : \Config('oly.default_responsible_branch');
                        $hospital->customer_id = $customer->id;
                        $hospital->save();
                        $hospitalIds[]=$hospital->id;
                    }
                    //update customer model with the hospital IDs
                    $customer->hospital_id = implode(',', $hospitalIds);
                    $customer->otp_code = mt_rand(100000, 999999); //random code for otp
                    $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 7200);
                    $customer->customer_id = sprintf("%08s", $customer->id);
                    $customer->save();

                    $respArr['status_code'] = 200;
                    $respArr['message'] = '';
                    $respArr['id'] = $customer->id;
                    $respArr['otp_code'] = $customer->otp_code;

                    Mail::to($customer->email)
                    ->send(new SendOtp($customer));
                    send_sms('request_created', $customer, "", "");

                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            } catch (Exception $e) {
                $respArr['message'] = 'Invalid Request Data';
                $respArr['status_code'] = 400;
                return response(json_encode($respArr), 400)->header('Content-Type', 'text/plain');
            }
        }
    }

    /**
     * Display the specified customer
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = Customers::findOrFail($id);
        return $customer;
    }

    /**
     * Update the specified customer
     *
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'first_name' => 'required|string',
            'middle_name' => 'string',
            'last_name' => 'required|string',
            'mobile_number' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
        ];
        $messages = [
            'Password must contain at least one number and both uppercase and lowercase letters and atleast one special character (@$!%*#?&).',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
        }else{
            $customer = Customers::findOrFail($id);
            $customerEmailCheck = Customers::where('email', strtolower($request->email))->first();
            $customerMobileCheck = Customers::where('mobile_number', $request->mobile_number)->first();
            if($customer->email != strtolower($request->email)){
                if (!is_null($customerEmailCheck)) {
                    $respArr['status_code'] = 401;
                    $respArr['message'] = 'Email already exists in our database. Please try again.';
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            }
            if(!is_null($customerMobileCheck) && $customer->mobile_number != strtolower($request->mobile_number)){
                    $respArr['status_code'] = 401;
                    $respArr['message'] = 'Mobile number already exists. Please try again.';
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
            }else {
                try {
                    $customer->title = $request->title;
                    $customer->first_name = $request->first_name;
                    $customer->middle_name = $request->middle_name;
                    $customer->last_name = $request->last_name;
                    $customer->mobile_number = $request->mobile_number;
                    $customer->email = strtolower($request->email);
                    $customer->save();
                    if (isset($request->hospitalAry)) {
                            //blank array to store hospital IDs created
                        $hospitalIds = [];
                        // dd(json_decode($request->hospitalAry,true));
                        //create all hospitals
                        foreach (json_decode($request->hospitalAry, true) as $hospital_req) {
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
                        NotifyCustomer::send_notification('account_update', '', $customer);

                        $respArr['status_code'] = 200;
                        $respArr['message'] = 'Your Data has been updated.';
                        // $respArr['customer_id'] = $customer->id;
                        $respArr['data'] = $customer;
                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                    } else {
                        $respArr['status_code'] = 400;
                        $respArr['message'] = 'Invalid Data';
                        // $respArr['customer_id'] = $customer->id;
                        // $respArr['data'] = $customer;
                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                    }
                }
                catch (Exception $e) {
                }
            }
        }
    }

    /**
     * Authenticate the customer
     *
     * @param  string  $username
     * @param  string  $password
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {

        $respArr['status_code'] = 407;
        $respArr['message'] = "Dear customer! We've enhanced the app security! Please update your app.";
        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');

        //Log::info($request->toArray());
        $rules = [
            'email' => 'required|exists:customers,email',
            'password' => 'required',
            'device_token' => 'required',
            'platform' => 'required',
            'app_version' => 'required',
        ];
        $messages = [
            'email.exists' => 'Sorry, no account with this email exists.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
        }else{
            $customer = Customers::where('email', strtolower($request->email))->first();
            if (!is_null($customer)) {
                $respArr = [];
                if (isset($request->device_token)) {
                    if ($request->device_token!=null) {
                        $customer->device_token = $request->device_token;
                        $customer->platform = $request->platform;
                        $customer->save();
                    }
                }
                if (isset($request->app_version)) {
                    if ($request->app_version!=null) {
                        $customer->app_version = $request->app_version;
                        $customer->save();
                    }
                }
                $hospitals = Hospitals::whereIn('id', explode(',', $customer->hospital_id))->get();
                if (Hash::check($request->password, $customer->password)) {
                    foreach ($hospitals as $hospital) {
                        $departments = Departments::whereIn('id', explode(',', $hospital->dept_id))->get();
                        $hospital->deptAry = $departments;
                    }
                    if ($customer->is_verified) {
                        $customer->hospitalAry = $hospitals;
                        $respArr['status_code'] = 200;
                        $respArr['message'] = 'Success';
                        $respArr['data'] = $customer;
                        if($customer->is_testing){
                            $respArr['data']->testing_url = \Config('oly.testing_url');
                        }
                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                    } else {
                        if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                            $customer = Customers::where('email', strtolower($request->email))->first();
                            $customer->otp_code = mt_rand(100000, 999999);
                            $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 7200);
                            $customer->save();
                        }
                        $respArr['status_code'] = 401;
                        $respArr['message'] = 'Your Account is not verified yet.';
                        $respArr['data'] = $customer;
                        Mail::to($customer->email)
                            ->send(new SendOtp($customer));
                        send_sms('request_created', $customer, "", "");
                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                    }
                } else {
                    $respArr['status_code'] = 403;
                    $respArr['message'] = 'Password entered did not match our records.';
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            } else {
                $respArr['status_code'] = 404;
                $respArr['message'] = 'Sorry, no account with this email exists.';
                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
            }
        }
    }

    /**
     * OTP Verification API.
     *
     * @param  $request \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function otp_verify(Request $request)
    {
        $rules = [
            'user_id' => 'required|numeric',
            'otp_code' => 'required',
            //'device_token' => 'required',
            //'platform' => 'required',
            //'app_version' => 'required',
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
        }else{
            $customer = Customers::findOrFail($request->user_id);
            $hospitals = Hospitals::where('customer_id', $customer->id)->get();
            foreach ($hospitals as $hospital) {
                $departments = Departments::whereIn('id', explode(',', $hospital->dept_id))->get();
                $hospital->deptAry = $departments;
            }

            if (($customer->otp_code == $request->otp_code)) {
                if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                    $customer = Customers::where('id', strtolower(ltrim($request->user_id, '0')))->first();
                    $customer->otp_code = mt_rand(100000, 999999);
                    $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 7200);
                    $customer->save();
                    $respArr['status_code'] = 403;
                    $respArr['message'] = 'Your OTP has expired. We have sent another OTP to your number and email.';
                    Mail::to($customer->email)
                    ->send(new SendOtp($customer));
                    send_sms('request_created', $customer, "", "");
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }else{
                    $respArr['status_code'] = 200;
                    $customer->is_verified = true;
                    if (isset($request->device_token) && !is_null($request->device_token) ) {
                        $customer->device_token = $request->device_token;
                    }
                    if (isset($request->platform) && !is_null($request->platform) ) {
                        $customer->platform = $request->platform;
                    }
                    if (isset($request->app_version) && !is_null($request->app_version)) {
                        $customer->app_version = $request->app_version;
                    }
                    $customer->save();
                    $customer['hospitalAry'] = $hospitals;
                    $respArr['message'] = 'Your account has been verified successfully.';
                    $respArr['data'] = $customer;
                }
            } else {
                $respArr['status_code'] = 403;
                $respArr['message'] = 'The OTP entered did not match the one sent to you. Please check and try again.';
            }
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        }
    }
    public function otp_resend(Request $request)
    {
        $rules = [
            'email' => 'required|exists:customers,email',
            //'device_token' => 'required',
            //'platform' => 'required',
            //'app_version' => 'required',
        ];
        $messages = [
            'email.exists' => 'Sorry, no account with this email exists.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
        }else{
            $customer = Customers::where('email', strtolower($request->email))->first();
            if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                $customer->otp_code = mt_rand(100000, 999999);
                $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 7200);
                $customer->save();
            }
            Mail::to($customer->email)
              ->send(new SendOtp($customer));
            send_sms('request_created', $customer, "", "");
            return Response::json(['status'=>200,'message'=>'OTP Sent !','otp' => $customer->otp_code]);
        }
    }

    public function send_otp(Request $request)
    {
        $rules = [
            'mobile_number' => 'required|exists:customers,mobile_number',
            //'device_token' => 'required',
            //'platform' => 'required',
            //'app_version' => 'required',
        ];
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
        }else{
            //$phone_no = preg_replace('/^\+|\|\D/', '', ($request->mobile_number));
            $phone_no =  $request->mobile_number;
            $customer = Customers::where('mobile_number', $phone_no)->first();
            //$customer = Customers::where('mobile_number', $request->mobile_number)->first();
            if (!is_null($customer)) {
                if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                    $customer = Customers::where('email', strtolower($customer->email))->first();
                    $customer->otp_code = mt_rand(100000, 999999);
                    $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 7200);
                    $customer->save();
                }
                Mail::to($customer->email)
                    ->send(new SendOtp($customer));
                send_sms('request_created', $customer, "", "");
                return Response::json(['status'=>200,'message'=>'OTP Sent !','otp' => $customer->otp_code ]);
            } else {
                return Response::json(['status'=>403,'message'=>'Incorrect Mobile Number !']);
            }
        }
    }

    public function password_opt_verify(Request $request)
    {
        $rules = [
            'mobile_number' => 'required|exists:customers,mobile_number',
            'otp_code' => 'required|numeric',
            ////'device_token' => 'required',
            ////'platform' => 'required',
            ////'app_version' => 'required',
        ];
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
        }else{
            $phone_no = $request->mobile_number;
            //dd($phone_no);
            $customer = Customers::where('mobile_number', $phone_no)->first();
            //$customer = Customers::where('mobile_number', $request->mobile_number)->first();
            if ($customer->otp_code == $request->otp_code) {
                $respArr['status_code'] = 200;
                $respArr['message'] = 'OTP verified !';
                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
            } else {
                $respArr['status_code'] = 403;
                $respArr['message'] = 'The OTP entered did not match the one sent to you. Please check and try again.';
                send_sms('request_created', $customer, "", "");
                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
            }
        }
    }

    public function password_update(Request $request)
    {
        $rules = [
            'mobile_number' => 'required|exists:customers,mobile_number',
            //'device_token' => 'required',
            //'platform' => 'required',
            //'app_version' => 'required',
            'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
        ];
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
            'Password must contain at least one number and both uppercase and lowercase letters and atleast one special character (@$!%*#?&).',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
        }else{
            $phone_no = $request->mobile_number;
            //dd($phone_no);
            $customer = Customers::where('mobile_number', $phone_no)->first();
            //dd($customer);
            if (!is_null($customer)) {
                $customer->password = Hash::make($request->password);
                $customer->save();
                return Response::json(['status'=>200,'message'=>'Password Updated']);
            } else {
                return Response::json(['status'=>403,'message'=>'Incorrect Mobile Number !']);
            }
        }
    }
}
