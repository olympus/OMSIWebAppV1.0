<?php
namespace App\Http\Controllers\API\V2;
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
                'title' => 'required',
                'first_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                'middle_name' => 'regex:/^[a-zA-Z\s]*$/',
                'last_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'email' => 'required|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
                'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/',
                "hospitalAry" => "required",
                "hospitalAry.*.address" => "required|regex:/^[0-9A-Za-z:#&@\/\-.\s,'-()]*$/",
                "hospitalAry.*.city" => "required|regex:/^[a-zA-Z\s]*$/",
                "hospitalAry.*.country" => "required|regex:/^[a-zA-Z\s]*$/",
                "hospitalAry.*.hospital_name" => "required|regex:/^[0-9A-Za-z&@.\s,'-()]*$/",
                "hospitalAry.*.dept_id" => "required",
                //"hospitalAry.*.dept_id" => "required|regex:/^[1-9,1-9]+$/",
                "hospitalAry.*.zip" => "required|digits:6|integer",
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

                //dd(preg_split("/[?&@#.]/", $namePart));
                $mobile_number_parts = last(explode('91', $request->mobile_number));
                //$mobile_number_parts = explode('91', $request->mobile_number);
                $mobileamePart = $mobile_number_parts;

                $strippedNumber = substr($request->mobile_number, 2); // Removing "91" prefix
                //return strpos($value, $strippedNumber) === false;

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
                // if(str_contains($request->get('password'), $mobileamePart)){
                //     $mobile_number_flag = true;
                // }
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
                    $customer->first_name = $request->first_name;
                    $customer->middle_name = $request->middle_name;
                    $customer->last_name = $request->last_name;
                    $customer->mobile_number = $request->mobile_number;
                    $customer->email = $request->email;
                    $customer->is_verified = false;
                    $customer->password = Hash::make($request->password);
                    $customer->save();

                    $customer->otp_code = mt_rand(100000, 999999); //random code for otp
                    $customer->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600);
                    $customer->customer_id = sprintf("%08s", $customer->id);
                    $customer->update();
                    $data = CustomerTemp::whereId($customer->id)->first();
                    if (isset($request->hospitalAry)) {
                        //blank array to store hospital IDs created
                        $hospitalIds = [];
                        //create all hospitals
                        foreach (json_decode($new_hospitalAry, true) as $hospital_req) {
                            // dd($hospital_req);
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
                    send_sms('send_otp', $customer, "", "");
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
    public function updateOld(Request $request, $id)
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
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
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

    public function update(Request $request, $id)
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
                    'first_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                    'last_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                    'email' => 'nullable|email|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
                    'mobile_number' => 'nullable|regex:/^([+])(91)[0-9]{10}$/',
                    // "hospitalAry" => "required",
                    // "hospitalAry.*.address" => "required|string",
                    // "hospitalAry.*.city" => "required|string",
                    // "hospitalAry.*.country" => "required|string",
                    // "hospitalAry.*.hospital_name" => "required|string",
                    // "hospitalAry.*.dept_id" => "required",
                    // "hospitalAry.*.zip" => "required|digits:6|integer",
                    // "hospitalAry.*.state" => "required|string",
                    "hospitalAry" => "required",
                    "hospitalAry.*.address" => "required|regex:/^[0-9A-Za-z:#&@\/\-.\s,'-()]*$/",
                    "hospitalAry.*.city" => "required|regex:/^[a-zA-Z\s]*$/",
                    "hospitalAry.*.country" => "required|regex:/^[a-zA-Z\s]*$/",
                    "hospitalAry.*.hospital_name" => "required|regex:/^[0-9A-Za-z&@.\s,'-()]*$/",
                    "hospitalAry.*.dept_id" => "required",
                    //"hospitalAry.*.dept_id" => "required|regex:/^[1-9,1-9]+$/",
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
                    $customer = Customers::findOrFail($id);

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
                    //else {
                        try {

                            $customer->title = $request->title;
                            $customer->first_name = $request->first_name;
                            $customer->middle_name = $request->middle_name;
                            $customer->last_name = $request->last_name;
                            //$customer->mobile_number = $request->mobile_number;
                            //$customer->email = strtolower($request->email);
                            $customer->save();
                            if (isset($new_hospitalAry)) {
                                //blank array to store hospital IDs created
                                $hospitalIds = [];
                                // dd(json_decode($new_hospitalAry,true));
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
                                //NotifyCustomer::send_notification('account_update', '', $customer);

                                $respArr['status_code'] = 200;
                                $respArr['message'] = 'Your Data has been updated.';
                                // $respArr['customer_id'] = $customer->id;
                                $respArr['data'] = $customer;

                                Logger("customer update api response");
                                Logger($respArr);
                                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                            } else {
                                $respArr['status_code'] = 401;
                                $respArr['message'] = 'Hospital details is required.';
                                // $respArr['customer_id'] = $customer->id;
                                // $respArr['data'] = $customer;
                                return response(json_encode($respArr), 401)->header('Content-Type', 'text/plain');
                            }
                        }
                        catch (Exception $e) {
                        }
                    //}
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

    /**
     * Authenticate the customer
     *
     * @param  string  $username
     * @param  string  $password
     * @return \Illuminate\Http\Response
     */
    public function loginOld(Request $request)
    {
        //Log::info($request->toArray());
        $rules = [
            'email' => 'required|email|exists:customers,email',
            'password' => 'required',
            'device_token' => 'required|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[.:]/',
            'platform' => 'required|string|regex:/^[a-zA-Z\s]*$/',
            'app_version'      => 'required|string|regex:/[0-9]/|regex:/[.]/',
        ];
        $messages = [
            'email.exists' => 'The username or password is not valid.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $customer = Customers::where('email', strtolower($request->email))->whereNull('deleted_at')->first();
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
                            if($customer->is_expired == 0){
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
                                        $access_token = JWTAuth::fromUser($customer);
                                        $respArr['status_code'] = 200;
                                        $respArr['message'] = 'Success';
                                        $respArr['data'] = $customer;
                                        $respArr['access_token'] = $access_token;
                                        $respArr['token_type'] = 'bearer';
                                        $respArr['expires_in'] = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');
                                        if($customer->is_testing){
                                            $respArr['data']->testing_url = \Config('oly.testing_url');
                                        }
                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }else {
                                        if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                                            $customer = Customers::where('email', strtolower($request->email))->first();
                                            $customer->otp_code = mt_rand(100000, 999999);
                                            $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                                            $customer->save();
                                        }
                                        $respArr['status_code'] = 401;
                                        $respArr['message'] = 'Your Account is not verified yet.';
                                        $respArr['data'] = $customer;
                                        $customer_data = Customers::where('email', strtolower($request->email))->first();
                                        Mail::to($customer_data->email)
                                            ->send(new SendOtp($customer_data));
                                        send_sms('request_created', $customer_data, "", "");
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
                                    //if($login_attempt->login_attempts < 3){
                                        $respArr['status_code'] = 403;
                                        //$respArr['message'] = 'Password entered did not match our records.';
                                        //$respArr['message'] = 'Invalid credentials. Please try again.';
                                        //$respArr['message'] = 'Invalid credentials. Did you forgot your password? Please reset it now.';
                                        $respArr['message'] = 'You have '.$left_attempt.' login attempts remaining.';

                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }else{
                                        $respArr['status_code'] = 403;
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
                        }else{
                            $respArr['status_code'] = 403;
                            $respArr['message'] = 'Your Account has been locked due to multiple failed login attempts. Please try again after 15 minutes.';
                            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                        }
                    }else{
                        if($customer->is_expired == 0){
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
                                    $access_token = JWTAuth::fromUser($customer);
                                    $respArr['status_code'] = 200;
                                    $respArr['message'] = 'Success';
                                    $respArr['data'] = $customer;
                                    $respArr['access_token'] = $access_token;
                                    $respArr['token_type'] = 'bearer';
                                    $respArr['expires_in'] = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');
                                    if($customer->is_testing){
                                        $respArr['data']->testing_url = \Config('oly.testing_url');
                                    }
                                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                }else {
                                    if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                                        $customer = Customers::where('email', strtolower($request->email))->first();
                                        $customer->otp_code = mt_rand(100000, 999999);
                                        $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                                        $customer->save();
                                    }
                                    $respArr['status_code'] = 401;
                                    $respArr['message'] = 'Your Account is not verified yet.';
                                    $respArr['data'] = $customer;
                                    $customer_data = Customers::where('email', strtolower($request->email))->first();
                                    Mail::to($customer_data->email)
                                        ->send(new SendOtp($customer_data));
                                    send_sms('request_created', $customer_data, "", "");
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
                                //dd($login_attempt_chk->login_attempts < 9);
                                if($login_attempt_chk->login_attempts < 9){
                                    //Log::info($left_attempt);
                                //if($login_attempt->login_attempts < 3){
                                    $respArr['status_code'] = 403;
                                    //$respArr['message'] = 'Password entered did not match our records.';
                                    //$respArr['message'] = 'Invalid credentials. Please try again.';
                                    //$respArr['message'] = 'Invalid credentials. Did you forgot your password? Please reset it now.';
                                    $respArr['message'] = 'You have '.$left_attempt.' login attempts remaining.';
                                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                }else{
                                    $respArr['status_code'] = 403;
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
                    //$respArr['message'] = 'Sorry, no account with this email exists.';
                    $respArr['message'] = 'The username or password is not valid.';
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            }else {
                return response()->json([
                    'status' => 400,
                    'message' => 'The username or password is not valid.',
                ]);
            }
        }
    }

    public function login(Request $request)
    {
        //Log::info($request->toArray());

        Logger("customer login api request");
        Logger($request->all());

        $rules = [
            'email' => 'required|email|exists:customers,email',
            'password' => 'required',
            'device_token' => 'required|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[.:]/',
            'platform' => 'required|string|regex:/^[a-zA-Z\s]*$/',
            'app_version'      => 'required|string|regex:/[0-9]/|regex:/[.]/',
        ];
        $messages = [
            'email.exists' => 'The username or password is not valid.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $customer = Customers::where('email', strtolower($request->email))->whereNull('deleted_at')->first();

            Logger("customer login api customer data");
            Logger($customer);

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
                                        $access_token = JWTAuth::fromUser($customer);
                                        $respArr['status_code'] = 200;
                                        $respArr['message'] = 'Success';
                                        $respArr['data'] = $customer;
                                        $respArr['access_token'] = $access_token;
                                        $respArr['token_type'] = 'bearer';
                                        $respArr['expires_in'] = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');
                                        if($customer->is_testing){
                                            $respArr['data']->testing_url = \Config('oly.testing_url');
                                        }
                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }else {
                                        if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                                            $customer = Customers::where('email', strtolower($request->email))->first();
                                            $customer->otp_code = mt_rand(100000, 999999);
                                            $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                                            $customer->save();
                                        }
                                        $respArr['status_code'] = 401;
                                        $respArr['message'] = 'Your Account is not verified yet.';
                                        $respArr['data'] = $customer;
                                        $customer_data = Customers::where('email', strtolower($request->email))->first();
                                        Mail::to($customer_data->email)
                                            ->send(new SendOtp($customer_data));
                                        send_sms('request_created', $customer_data, "", "");
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
                                    //if($login_attempt->login_attempts < 3){
                                        $respArr['status_code'] = 403;
                                        //$respArr['message'] = 'Password entered did not match our records.';
                                        //$respArr['message'] = 'Invalid credentials. Please try again.';
                                        //$respArr['message'] = 'Invalid credentials. Did you forgot your password? Please reset it now.';
                                        $respArr['message'] = 'Invalid credentials!
Please try again.
You have '.$left_attempt.' login attempts remaining.';

                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }else{
                                        Logger("customer login api password account locked");
                                        $respArr['status_code'] = 403;
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
                            $respArr['message'] = 'Your Account has been locked due to multiple failed login attempts. Please try again after 15 minutes.';
                            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                        }
                    }else{
                        if($customer->is_expired == 0){
                            Logger("customer login api password when is expired zero");

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
                                    $access_token = JWTAuth::fromUser($customer);
                                    $respArr['status_code'] = 200;
                                    $respArr['message'] = 'Success';
                                    $respArr['data'] = $customer;
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
                                        $customer->otp_code = mt_rand(100000, 999999);
                                        $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                                        $customer->save();
                                    }
                                    $respArr['status_code'] = 401;
                                    $respArr['message'] = 'Your Account is not verified yet.';
                                    $respArr['data'] = $customer;
                                    $customer_data = Customers::where('email', strtolower($request->email))->first();
                                    Mail::to($customer_data->email)
                                        ->send(new SendOtp($customer_data));
                                    send_sms('request_created', $customer_data, "", "");
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
                                //dd($login_attempt_chk->login_attempts < 9);
                                if($login_attempt_chk->login_attempts < 9){
                                    Logger("customer login api Invalid credentials!");
                                    Logger($respArr);

                                    //Log::info($left_attempt);
                                //if($login_attempt->login_attempts < 3){
                                    $respArr['status_code'] = 403;
                                    //$respArr['message'] = 'Password entered did not match our records.';
                                    //$respArr['message'] = 'Invalid credentials. Please try again.';
                                    //$respArr['message'] = 'Invalid credentials. Did you forgot your password? Please reset it now.';
                                    $respArr['message'] = 'Invalid credentials!
Please try again.
You have '.$left_attempt.' login attempts remaining.';
                                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                }else{
                                    $respArr['status_code'] = 403;
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
                    //$respArr['message'] = 'Sorry, no account with this email exists.';
                    $respArr['message'] = 'The username or password is not valid.';
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            }else {
                return response()->json([
                    'status' => 400,
                    'message' => 'The username or password is not valid.',
                ]);
            }
        }
    }

    /**
     * OTP Verification API.
     *
     * @param  $request \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function forgetpwd_otp_verify_old(Request $request)
    {
        $rules = [
            'user_id' => 'required|numeric',
            'otp_code' => 'required',
            'device_token' => 'required|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[.:]/',
            'platform' => 'required|string|regex:/^[a-zA-Z\s]*$/',
            'app_version'      => 'required|string|regex:/[0-9]/|regex:/[.]/',
            'hospitalAry' => 'required',
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $customer = CustomerTemp::where('id', $request->user_id)->first();
            if($customer){
                if (($customer->otp_code == $request->otp_code)) {
                    if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                        $customer = CustomerTemp::where('id', $request->user_id)->first();
                        $customer->otp_code = mt_rand(100000, 999999);
                        $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                        $customer->save();
                        $respArr['status_code'] = 403;
                        $respArr['message'] = 'Your OTP has been expired. We have sent a new OTP to your registered mobile number (and email).';
                        $customer_send_otp = CustomerTemp::where('id', $request->user_id)->first();
                        Mail::to($customer_send_otp->email)
                        ->send(new SendOtp($customer_send_otp));
                        send_sms('request_created', $customer_send_otp, "", "");
                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                    }else{
                        if(Customers::where('email', $customer->email)->first()){
                            $respArr['status_code'] = 200;
                            $respArr['message'] = 'Your account already verified.';
                            $respArr['data'] = Customers::where('email', $customer->email)->first();
                        }else{
                            $customer_data = new Customers;
                            $customer_data->title =  ($customer->title == "Mrs." || $customer->title == "Mrs") ? "Ms." : $customer->title;
                            $customer_data->first_name = $customer->first_name;
                            $customer_data->middle_name = $customer->middle_name;
                            $customer_data->last_name = $customer->last_name;
                            $customer_data->mobile_number = $customer->mobile_number;
                            $customer_data->email = $customer->email;
                            $customer_data->is_verified = true;
                            $customer_data->password =  $customer->password;
                            $customer_data->save();

                            $pass = new PasswordHistory();
                            $pass->customer_id = $customer_data->id;
                            $pass->password = $customer->password;
                            $pass->save();

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
                                    $hospital->customer_id = $customer_data->id;
                                    $hospital->save();
                                    $hospitalIds[]=$hospital->id;
                                }

                                $customer_data->hospital_id = implode(',', $hospitalIds);
                                $customer_data->otp_code = mt_rand(100000, 999999); //random code for otp
                                $customer_data->valid_upto = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 600);
                                $customer_data->customer_id = sprintf("%08s", $customer_data->id);

                                if (isset($request->device_token) && !is_null($request->device_token) ) {
                                    $customer_data->device_token = $request->device_token;
                                }
                                if (isset($request->platform) && !is_null($request->platform) ) {
                                    $customer_data->platform = $request->platform;
                                }
                                if (isset($request->app_version) && !is_null($request->app_version)) {
                                    $customer_data->app_version = $request->app_version;
                                }

                                $customer_data->update();
                            }
                            $customer_get = Customers::where('id', $customer_data->id)->first();
                            $hospitals = Hospitals::where('customer_id', $customer_get->id)->get();
                            foreach ($hospitals as $hospital) {
                                $departments = Departments::whereIn('id', explode(',', $hospital->dept_id))->get();
                                $hospital->deptAry = $departments;
                            }
                            $respArr['status_code'] = 200;
                            $customer_get['hospitalAry'] = $hospitals;
                            $respArr['message'] = 'Your account has been verified successfully.';
                            $respArr['data'] = $customer_get;
                        }
                    }
                }else {
                    $respArr['status_code'] = 403;
                    $respArr['message'] = 'The OTP entered did not match the one sent to you. Please check and try again.';
                }
            }else{
                $respArr['status_code'] = 202;
                $respArr['message'] = 'Not Found.';
            }
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        }
    }


    public function forgetpwd_otp_verify(Request $request){
        Logger("customer forget pwd otp verify api rquest payload");
        Logger($request->all());
        if($request->type == 'account'){
            $customer = CustomerTemp::where('id', $request->temp_customer_id)->first();
            $rules = [
                'temp_customer_id' => 'required|numeric',
                'otp_code' => 'required',
                //'hospitalAry' => 'required',
                'type' => 'required',
            ];
            //$request->hospitalAry = json_decode($request->hospitalAry, true);
            // for ($i = 0; $i <= count($request->hospitalAry); $i++) {
            // //foreach ($request->hospitalAry as $key => $hospital_req) {
            //     //if($i['address']){
            //         //dd($hospital_req['address']);
            //     //}
            //     $rules[]  = [
            //         'hospitalAry.'.$i.'.address' => 'required',
            //         'hospitalAry.'.$i.'.city' => 'required',
            //         'hospitalAry.'.$i.'.country' => 'required',
            //         'hospitalAry.'.$i.'.hospital_name' => 'required',
            //         'hospitalAry.'.$i.'.dept_id' => 'required',
            //         'hospitalAry.'.$i.'.zip' => 'required',
            //         'hospitalAry.'.$i.'.state' => 'required',
            //     ];
            //     // dd($key);
            //     //  $rules[] = [
            //     //     $hospital_req['address'] => 'required',
            //     //     $hospital_req['city'] => 'required',
            //     //     $hospital_req['country'] => 'required',
            //     //     $hospital_req['hospital_name'] => 'required',
            //     //     $hospital_req['dept_id'] => 'required',
            //     //     $hospital_req['zip'] => 'required',
            //     //     $hospital_req['state'] => 'required',
            //     // ];
            // }
        }else{
            $customer = Customers::where('mobile_number', $request->mobile_number)->first();
            $rules = [
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'otp_code' => 'required',
                'type' => 'required'
            ];
        }
        $validator = Validator::make( $request->all(), $rules);
        if($validator->fails())
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            if($customer){
                $login_attempt_data_check = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                $login_attempt_check = UserLoginAttemptCount::where(['user_id' => $customer->id,'forget_pwd_otp_attempts' => 10])->first();

                if(!empty($login_attempt_check)){
                    $to = Carbon::createFromFormat('Y-m-d H:i:s', $login_attempt_check->otp_attempts_updated_at);
                    $from = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now());
                    $diff_in_hours = $to->diffInMinutes($from);
                }
                if($request->type == 'account'){
                    Logger("customer forget pwd otp verify api account type");

                    if($customer){
                        if (($customer->otp_code == $request->otp_code)) {
                            if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                                $customer = CustomerTemp::where('id', $request->temp_customer_id)->first();
                                $customer->otp_code = mt_rand(100000, 999999);
                                $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                                $customer->save();
                                $respArr['status_code'] = 403;
                                $respArr['message'] = 'Your OTP has been expired. We have sent a new OTP to your registered mobile number (and email).';
                                $customer_data = CustomerTemp::where('id', $request->temp_customer_id)->first();

                                Mail::to($customer_data->email)
                                    ->send(new SendOtp($customer_data));
                                send_sms('request_created', $customer_data, "", "");
                                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                            }else{
                                if(Customers::where('email', $customer->email)->first()){
                                    $respArr['status_code'] = 200;
                                    $respArr['message'] = 'Your account already verified.';
                                    $respArr['data'] = Customers::where('email', $customer->email)->first();
                                }else{
                                    $customer_data = new Customers;
                                    $customer_data->title =  ($customer->title == "Mrs." || $customer->title == "Mrs") ? "Ms." : $customer->title;
                                    $customer_data->first_name = $customer->first_name;
                                    $customer_data->middle_name = $customer->middle_name;
                                    $customer_data->last_name = $customer->last_name;
                                    $customer_data->mobile_number = $customer->mobile_number;
                                    $customer_data->email = $customer->email;
                                    $customer_data->is_verified = true;
                                    $customer_data->password =  $customer->password;
                                    $customer_data->password_updated_at =  Carbon::now();
                                    $customer_data->is_expired =  0;
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
                                            $hospital->customer_id = $customer_data->id;
                                            $hospital->responsible_branch = (array_key_exists($hospital->state, \Config('oly.responsible_branches'))) ? \Config('oly.responsible_branches')[$hospital->state] : \Config('oly.default_responsible_branch');
                                            $hospital->save();
                                            $hospitalIds[]=$hospital->id;
                                        }

                                        $customer_data->hospital_id = implode(',', $hospitalIds);
                                        $customer_data->customer_id = sprintf("%08s", $customer_data->id);

                                        if (isset($request->device_token) && !is_null($request->device_token) ) {
                                            $customer_data->device_token = $request->device_token;
                                        }
                                        if (isset($request->platform) && !is_null($request->platform) ) {
                                            $customer_data->platform = $request->platform;
                                        }
                                        if (isset($request->app_version) && !is_null($request->app_version)) {
                                            $customer_data->app_version = $request->app_version;
                                        }

                                        $customer_data->update();
                                    }
                                    $customer_get = Customers::where('id', $customer_data->id)->first();
                                    $hospitals = Hospitals::where('customer_id', $customer_get->id)->get();
                                    foreach ($hospitals as $hospital) {
                                        $departments = Departments::whereIn('id', explode(',', $hospital->dept_id))->get();
                                        $hospital->deptAry = $departments;
                                    }
                                    //$customer_get->token = Str::random(80);
                                    $respArr['status_code'] = 200;
                                    $customer_get['hospitalAry'] = $hospitals;
                                    $respArr['message'] = 'Your account has been verified successfully.';
                                    $respArr['data'] = $customer_get;

                                    Logger("customer forget pwd otp verify api account register");
                                    Logger($respArr);
                                }
                            }
                        }else {
                            $respArr['status_code'] = 403;
                            $respArr['message'] = 'The OTP entered did not match. Please try again.';
                        }
                    }else{
                        $respArr['status_code'] = 202;
                        $respArr['message'] = 'Not Found.';
                    }
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }else{
                    Logger("customer forget pwd otp verify api password update");
                    if($customer){
                        if (($customer->otp_code == $request->otp_code)) {
                            if(!empty($login_attempt_check)){
                                if($diff_in_hours >= 15){
                                    if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                                        $customer->otp_code = mt_rand(100000, 999999);
                                        $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                                        $customer->save();
                                        $respArr['status_code'] = 403;
                                        $respArr['message'] = 'Your OTP has been expired. We have sent a new OTP to your registered mobile number (and email).';
                                        $customer_otp_send = Customers::where('mobile_number', $request->mobile_number)->first();

                                        Mail::to($customer_otp_send->email)
                                            ->send(new SendForgotOtp($customer_otp_send));
                                        send_sms('request_created', $customer_otp_send, "", "");
                                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                    }else{
                                        if(!empty($login_attempt_data_check)){
                                            UserLoginAttemptCount::where('user_id', $customer->id)->update([
                                                'forget_pwd_otp_attempts' => 0,
                                            ]);
                                        }

                                        $token = Str::random(80);
                                        $respArr['status_code'] = 200;
                                        $customer->is_verified = true;
                                        $customer->access_token = $token;
                                        $customer->save();
                                        //$customer->token = $token;
                                        $respArr['message'] = 'Your account has been verified successfully.';
                                        $respArr['data'] = $customer;
                                        $respArr['password_access_token'] = $token;
                                    }
                                }else {
                                    Logger("customer forget pwd otp verify api You have exhausted all your attempts to enter the incorrect OTP. Please try again after 15 minutes.");

                                    $respArr['status_code'] = 403;
                                    //$respArr['message'] = 'Your Account has been locked due to multiple wrong otp verification attempts. Please try again after 15 minutes.';
                                    $respArr['message'] = 'You have exhausted all your attempts to enter the incorrect OTP. Please try again after 15 minutes.';
                                }
                            }else{
                                if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                                    $customer->otp_code = mt_rand(100000, 999999);
                                    $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                                    $customer->save();
                                    $respArr['status_code'] = 403;
                                    $respArr['message'] = 'Your OTP has been expired. We have sent a new OTP to your registered mobile number (and email).';
                                    $customer_otp_send = Customers::where('mobile_number', $request->mobile_number)->first();

                                    Mail::to($customer_otp_send->email)
                                        ->send(new SendForgotOtp($customer_otp_send));
                                    send_sms('request_created', $customer_otp_send, "", "");
                                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                                }else{
                                    if(!empty($login_attempt_data_check)){
                                        UserLoginAttemptCount::where('user_id', $customer->id)->update([
                                            'forget_pwd_otp_attempts' => 0,
                                        ]);
                                    }

                                    $token = Str::random(80);
                                    $respArr['status_code'] = 200;
                                    $customer->is_verified = true;
                                    $customer->access_token = $token;
                                    $customer->save();
                                    //$customer->token = $token;
                                    $respArr['message'] = 'Your account has been verified successfully.';
                                    $respArr['data'] = $customer;
                                    $respArr['password_access_token'] = $token;
                                }
                            }
                        }else {
                            $login_attempt = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                            if(empty($login_attempt)){
                                $left_attempt = 0;
                                $left_attempt =  10 - 1;
                                $user_login_attempt_count = new UserLoginAttemptCount();
                                $user_login_attempt_count->user_id = $customer->id;
                                $user_login_attempt_count->forget_pwd_otp_attempts = 1;
                                $user_login_attempt_count->otp_attempts_updated_at = Carbon::now();
                                $user_login_attempt_count->save();
                                $login_attempt_chk = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                            }else{

                                $login_attempt_chk = UserLoginAttemptCount::where(['user_id' => $customer->id])->first();
                                if($login_attempt_chk->forget_pwd_otp_attempts >= 0){
                                    $attmpt = $login_attempt_chk->forget_pwd_otp_attempts + 1;
                                    $left_attempt =  10 - (int)$attmpt;
                                }
                                if($login_attempt->forget_pwd_otp_attempts < 10){
                                    $user_login_attempt_count = UserLoginAttemptCount::find($login_attempt->id);
                                    $user_login_attempt_count->user_id = $customer->id;
                                    $user_login_attempt_count->forget_pwd_otp_attempts = $login_attempt->forget_pwd_otp_attempts + 1;
                                    $user_login_attempt_count->otp_attempts_updated_at = Carbon::now();
                                    $user_login_attempt_count->save();
                                }
                            }

                            if($login_attempt_chk->forget_pwd_otp_attempts < 9){
                                $respArr['status_code'] = 403;
                                $respArr['message'] = 'Invalid OTP!
Please try again.
You have '.$left_attempt.' attempts remaining';
                                //$respArr['message'] = 'The OTP Entered did not match. Please try again now.';
                            }else{
                                $respArr['status_code'] = 403;
                                $respArr['message'] = 'You have exhausted all your attempts to enter the incorrect OTP. Please try again after 15 minutes.';
                                //$respArr['message'] = 'Your account has been locked due to multiple wrong otp verification attempts. Please try again after 15 minutes.';
                            }
                        }
                    }else{
                        $respArr['status_code'] = 202;
                        $respArr['message'] = 'Not Found.';
                    }
                    return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            }else{
                $respArr['status_code'] = 202;
                $respArr['message'] = 'Sorry, no account exists with this mobile number.';
                return response(json_encode($respArr), 202)->header('Content-Type', 'text/plain');
            }
        }
    }

    public function otp_resend(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists:customers,email',
        ];
        $messages = [
            'email.exists' => 'The username or password is not valid.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
                    $customer = Customers::where('email', strtolower($request->email))->first();
                    if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                        $customer->otp_code = mt_rand(100000, 999999);
                        $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                        $customer->save();
                    }
                    $customer_otp_send = Customers::where('email', strtolower($request->email))->first();

                    Mail::to($customer_otp_send->email)
                        ->send(new SendResendOtp($customer_otp_send));
                    send_sms('request_created', $customer_otp_send, "", "");


                    return Response::json(['status_code'=>200,'message'=>'OTP Sent !','otp' => $customer->otp_code]);
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
    }

    public function send_otp(Request $request)
    {
        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
            if($user->is_expired == 0){
                $rules = [
                    'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/|exists:customers,mobile_number',
                ];
                $messages = [
                    'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
                ];
                $validator = Validator::make( $request->all(), $rules,$messages);

                if ( $validator->fails() )
                {
                    return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
                }else{
                    //$phone_no = preg_replace('/^\+|\|\D/', '', ($request->mobile_number));
                    $phone_no =  $request->mobile_number;
                    $customer = Customers::where('mobile_number', $phone_no)->first();
                    //$customer = Customers::where('mobile_number', $request->mobile_number)->first();
                    if (!is_null($customer)) {
                        if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                            $customer = Customers::where('email', strtolower($customer->email))->first();
                            $customer->otp_code = mt_rand(100000, 999999);
                            $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                            $customer->save();
                        }
                        $customer_otp_send = Customers::where('email', strtolower($customer->email))->first();

                        Mail::to($customer_otp_send->email)
                        ->send(new SendResendOtp($customer_otp_send));
                        send_sms('request_created', $customer_otp_send, "", "");
                        return Response::json(['status_code'=>200,'message'=>'OTP Sent !','otp' => $customer->otp_code ]);
                    } else {
                        return Response::json(['status_code'=>403,'message'=>'Incorrect Mobile Number !']);
                    }
                }
            }
        }else{
            return response()->json([
                'status_code' => 407,
                'message' => 'Password Expired',
                'is_expired' => $user->is_expired
            ]);
        }
    }

    public function forgetpwd_send_otp(Request $request)
    {
        $rules = [
            'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/|exists:customers,mobile_number',
        ];
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $phone_no =  $request->mobile_number;
            $customer = Customers::where('mobile_number', $phone_no)->first();
            if (!is_null($customer)) {
                //if (strtotime($customer->valid_upto) < strtotime(date('Y-m-d H:i:s'))) {
                    $customer = Customers::where('email', strtolower($customer->email))->first();
                    $customer->otp_code = mt_rand(100000, 999999);
                    $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                    $customer->save();
                //}
                $customer_otp_send = Customers::where('email', strtolower($customer->email))->first();

                //dd($customer_otp_send);
                Mail::to($customer_otp_send->email)
                ->send(new SendForgotOtp($customer_otp_send));
                send_sms('request_created', $customer_otp_send, "", "");


                return Response::json(['status_code'=>200,'message'=>'OTP Sent !','otp' => $customer_otp_send->otp_code ]);
            } else {
                return Response::json(['status_code'=>403,'message'=>'Incorrect Mobile Number !']);
            }
        }
    }

    public function otp_verify(Request $request)
    {
        $rules = [
            'user_id' => 'required|numeric',
            'otp_code' => 'required',
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
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
                            $customer->valid_upto=date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')) + 600);
                            $customer->save();
                            $respArr['status_code'] = 403;
                            $respArr['message'] = 'Your OTP has been expired. We have sent a new OTP to your registered mobile number (and email).';
                            $customer_otp_send = Customers::where('id', strtolower(ltrim($request->user_id, '0')))->first();

                            Mail::to($customer_otp_send->email)
                            ->send(new SendResendOtp($customer_otp_send));
                            send_sms('request_created', $customer_otp_send, "", "");


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
    }

    public function password_opt_verify(Request $request)
    {
        $rules = [
            'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/|exists:customers,mobile_number',
            'otp_code' => 'required|numeric',
        ];
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
                    $phone_no =  $request->mobile_number;
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
                        Mail::to($customer->email)->send(new SendResendOtp($customer));
                        send_sms('request_created', $customer, "", "");
                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
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
    }

    public function password_update(Request $request)
    {
        Logger("password update api request payload");
        Logger($request->all());

        $rules = [
            'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/|exists:customers,mobile_number',
            'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
        ];
        $messages = [
            'mobile_number.exists' => 'Sorry, no account exists with this mobile number.',
            'password.*'=>"Invalid password. Password should be in minimum 8 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
            $string = $password;

            $customerEmailCheck = Customers::where('mobile_number', $request->mobile_number)->first();
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
                        return Response::json(['status_code'=>403,'message'=>'Incorrect Mobile Number !']);
                    }
                }
            }else{
                // return response('Invalid Request', 400)
                //   ->header('Content-Type', 'text/plain');
                return Response::json(['status_code' => 400, 'message'=>'Please try again !']);
            }
        }
    }


    public function testingPasswordStatusChange(Request $request)
    {
        $customer = Customers::where('mobile_number', $request->mobile_number)->first();
        if($customer) {
            Customers::where('id', $customer->id)->update([
                'is_expired' => $request->is_expired
            ]);
            $data = Customers::where('mobile_number', $request->mobile_number)->first();
            $respArr['status_code'] = 200;
            $respArr['message'] = 'success';
            $respArr['data'] = $data;
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');

        } else {
            $respArr['status_code'] = 403;
            $respArr['message'] = 'Incorrect Mobile Number';
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        }
    }

    public function testingFcmToken(Request $request)
    {
        $customer = Customers::where('mobile_number', $request->mobile_number)->first();
        if($customer) {
            //new AppFCM('password_expired', $customer);
            return Response::json(['status_code'=>200,'message'=>'Notification Send successfully!']);
        } else {
            return Response::json(['status_code'=>403,'message'=>'Incorrect Mobile Number']);
        }
    }

    public function temp_resend_pwd_otp(Request $request)
    {
        $rules = [
            'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/'
        ];
        $validator = Validator::make( $request->all(), $rules);
        if ($validator->fails())
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $customer = CustomerTemp::where('mobile_number', $request->mobile_number)->orderBy('id','desc')->first();
            if ($customer) {
                $customer->otp_code = mt_rand(100000, 999999);
                $customer->save();
                Mail::to($customer->email)->send(new SendOtp($customer));

                send_sms('send_otp', $customer, "", "");
                $respArr['status_code'] = 200;
                $respArr['message'] = 'Your account already verified.';
                $respArr['data'] = $customer->otp_code;
                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
            } else {
                $respArr['status_code'] = 403;
                $respArr['message'] = 'This number is not registered.';
                return response(json_encode($respArr), 403)->header('Content-Type', 'text/plain');
            }
        }
    }


    public function password_status(Request $request)
    {
        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
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

    public function testingPasswordStatusChangeWeb(Request $request)
    {
        $customer = User::where('email', $request->email)->first();
        if($customer) {
            User::where('id', $customer->id)->update([
                'is_expired' => $request->is_expired
            ]);
            $data = User::where('email', $request->email )->first();
            $respArr['status_code'] = 200;
            $respArr['message'] = 'success';
            $respArr['data'] = $data;
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');

        } else {
            $respArr['status_code'] = 403;
            $respArr['message'] = 'Incorrect Email';
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        }
    }

    public function checkPasswordValidationOld(Request $request)
    {
        if($request->type == 'account'){
            $rules = [
                'first_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                'last_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                'email' => 'required|email|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
            ];
            $messages = [
                "password.required"=>"Password is required",
                "email.required"=>"Email is required",
                "password.*"=>"Invalid password. Password should be in minimum 8 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
            ];

            $validator = Validator::make( $request->all(), $rules,$messages);
            if ($validator->fails())
            {
                return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
            }else{
                try {
                    $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
                    $string = $password;

                    $first_name = strtolower($request->first_name);
                    $last_name = strtolower($request->last_name);
                    $email = strtolower($request->email);
                    $mobile_number = $request->mobile_number;

                    $parts = explode('@', $email);
                    $namePart = $parts[0];

                    $first_lat_name_flag = false;
                    if(str_contains($string, $first_name.$last_name)){
                        $first_lat_name_flag = true;
                    }
                    if ($first_lat_name_flag == true) {
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $first_name_flag = false;
                    if(str_contains($string, $first_name)){
                        $first_name_flag = true;
                    }
                    if ($first_name_flag == true) {
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $last_name_flag = false;
                    if(str_contains($string, $last_name)){
                        $last_name_flag = true;
                    }
                    if ($last_name_flag == true) {
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $email_flag = false;
                    if(str_contains($request->get('password'), $email)){
                        $email_flag = true;
                    }
                    if ($email_flag == true) {
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $email_flag_start = false;
                    if(str_contains($request->get('password'), $namePart)){
                        $email_flag_start = true;
                    }
                    if ($email_flag_start == true) {
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    $mobile_number_flag = false;
                    if(str_contains($request->get('password'), $mobile_number)){
                        $mobile_number_flag = true;
                    }
                    if ($mobile_number_flag == true) {
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    if($first_lat_name_flag == false || $first_name_flag == false || $last_name_flag == false || $email_flag == false || $email_flag_start == false || $mobile_number_flag == false){
                        return response()->json([
                            'status_code' => 202,
                            'message' => 'success.',
                        ]);
                    }

                }
                catch (Exception $e) {
                }
            }
        }else{
            $customerEmailCheck = Customers::where('mobile_number', $request->mobile_number)->first();
            $rules = [
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
            ];
            $messages = [
                "password.required"=>"Password is required",
                "mobile_number.required"=>"Mobile is required",
                "password.*"=>"Invalid password. Password should be in minimum 8 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
            ];

            //dd($customerEmailCheck);
            if (!empty($customerEmailCheck)) {

                $validator = Validator::make( $request->all(), $rules,$messages);
                if ($validator->fails())
                {
                    return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
                }else{
                    try {
                        $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
                        $string = $password;

                        $first_name = strtolower($customerEmailCheck->first_name);
                        $last_name = strtolower($customerEmailCheck->last_name);
                        $email = strtolower($customerEmailCheck->email);
                        $mobile_number = $customerEmailCheck->mobile_number;

                        $parts = explode('@', $email);
                        $namePart = $parts[0];

                        $first_lat_name_flag = false;
                        if(str_contains($string, $first_name.$last_name)){
                            $first_lat_name_flag = true;
                        }
                        if ($first_lat_name_flag == true) {
                            return response()->json([
                                'status_code' => 203,
                                'message' => 'You can not use name, email and phone number in password.',
                            ]);
                        }

                        $first_name_flag = false;
                        if(str_contains($string, $first_name)){
                            $first_name_flag = true;
                        }
                        if ($first_name_flag == true) {
                            return response()->json([
                                'status_code' => 203,
                                'message' => 'You can not use name, email and phone number in password.',
                            ]);
                        }

                        $last_name_flag = false;
                        if(str_contains($string, $last_name)){
                            $last_name_flag = true;
                        }
                        if ($last_name_flag == true) {
                            return response()->json([
                                'status_code' => 203,
                                'message' => 'You can not use name, email and phone number in password.',
                            ]);
                        }

                        $email_flag = false;
                        if(str_contains($request->get('password'), $email)){
                            $email_flag = true;
                        }
                        if ($email_flag == true) {
                            return response()->json([
                                'status_code' => 203,
                                'message' => 'You can not use name, email and phone number in password.',
                            ]);
                        }

                        $email_flag_start = false;
                        if(str_contains($request->get('password'), $namePart)){
                            $email_flag_start = true;
                        }
                        if ($email_flag_start == true) {
                            return response()->json([
                                'status_code' => 203,
                                'message' => 'You can not use name, email and phone number in password.',
                            ]);
                        }

                        $mobile_number_flag = false;
                        if(str_contains($request->get('password'), $mobile_number)){
                            $mobile_number_flag = true;
                        }
                        if ($mobile_number_flag == true) {
                            return response()->json([
                                'status_code' => 203,
                                'message' => 'You can not use name, email and phone number in password.',
                            ]);
                        }

                        if($first_lat_name_flag == false || $first_name_flag == false || $last_name_flag == false || $email_flag == false || $email_flag_start == false || $mobile_number_flag == false){
                            return response()->json([
                                'status_code' => 202,
                                'message' => 'success.',
                            ]);
                        }

                    }
                    catch (Exception $e) {
                    }
                }
            }else {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'user not found',
                ]);
            }
        }
    }

    public function checkPasswordValidation(Request $request)
    {
        Logger("check password validation api request payload");
        Logger($request->all());

        if($request->type == 'account'){
            $rules = [
                'first_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                'last_name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
                'email' => 'required|email|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
            ];
            $messages = [
                "password.required"=>"Password is required",
                "email.required"=>"Email is required",
                "password.*"=>"Invalid password. Password should be in minimum 8 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
            ];

            $validator = Validator::make( $request->all(), $rules,$messages);
            if ($validator->fails())
            {
                return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
            }else{
                $chk_pass_space = $request->get('password');
                if(str_contains($chk_pass_space, ' ')){
                    Logger("check password validation api You can not use space in your password.");
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'You can not use space in your password.',
                    ]);
                }
                try {
                    $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
                    $string = $password;

                    $first_name = strtolower($request->first_name);
                    $last_name = strtolower($request->last_name);

                    $email = strtolower($request->email);
                    $mobile_number = $request->mobile_number;

                    $parts = explode('@', $email);
                    $namePart = $parts[0];

                    //dd(preg_split("/[?&@#.]/", $namePart));
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
                        Logger("check password validation api You can not use name, email and phone number in password 1");
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
                        Logger("check password validation api You can not use name, email and phone number in password 2");
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
                        Logger("check password validation api You can not use name, email and phone number in password 3");
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
                        Logger("check password validation api You can not use name, email and phone number in password 4");
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
                        Logger("check password validation api You can not use name, email and phone number in password 5");
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
                        Logger("check password validation api You can not use name, email and phone number in password 6");
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
                        Logger("check password validation api You can not use name, email and phone number in password 7");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use name, email and phone number in password.',
                        ]);
                    }

                    if($first_lat_name_flag == false || $first_name_flag == false || $last_name_flag == false || $email_flag == false || $email_flag_start == false || $mobile_number_flag == false){
                        Logger("check password validation api password match success");
                        return response()->json([
                            'status_code' => 200,
                            'message' => 'success.',
                        ]);
                    }

                }
                catch (Exception $e) {
                }
            }
        }else{
            $customerEmailCheck = Customers::where('mobile_number', $request->mobile_number)->first();
            $rules = [
                'mobile_number' => 'required|regex:/^([+])(91)[0-9]{10}$/',
                'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
            ];
            $messages = [
                "password.required"=>"Password is required",
                "mobile_number.required"=>"Mobile is required",
                "password.*"=>"Invalid password. Password should be in minimum 8 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
            ];

            //dd($customerEmailCheck);
            if (!empty($customerEmailCheck)) {
                $validator = Validator::make( $request->all(), $rules,$messages);
                if ($validator->fails())
                {
                    return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
                }else{
                    $chk_pass_space = $request->get('password');
                    if(str_contains($chk_pass_space, ' ')){
                        Logger("check password validation api You can not use space in your password");
                        return response()->json([
                            'status_code' => 203,
                            'message' => 'You can not use space in your password.',
                        ]);
                    }
                    try {
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
                            Logger("check password validation api You can not use name, email and phone number in password 1");
                            return response()->json([
                                'status_code' => 203,
                                'message' => 'You can not use name, email and phone number in password.',
                            ]);
                        }

                        // $first_name_flag = false;
                        // if(str_contains(strtolower($string), strtolower($first_name))){
                        //     $first_name_flag = true;
                        // }
                        // if ($first_name_flag == true) {
                        //     return response()->json([
                        //         'status_code' => 203,
                        //         'message' => 'You can not use name, email and phone number in password.',
                        //     ]);
                        // }

                        // $last_name_flag = false;
                        // if(str_contains(strtolower($string), strtolower($last_name))){
                        //     $last_name_flag = true;
                        // }
                        // if ($last_name_flag == true) {
                        //     return response()->json([
                        //         'status_code' => 203,
                        //         'message' => 'You can not use name, email and phone number in password.',
                        //     ]);
                        // }

                        $first_name_flag = false;
                        foreach($first_name_match as $first_name_matchs){
                            if(str_contains(strtolower($string), strtolower($first_name_matchs)) && $first_name_match != ""){
                                $first_name_flag = true;
                                break;
                            }
                        }

                        if ($first_name_flag == true) {
                            Logger("check password validation api You can not use name, email and phone number in password 2");
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
                            Logger("check password validation api You can not use name, email and phone number in password 3");
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
                            Logger("check password validation api You can not use name, email and phone number in password 4");
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
                            Logger("check password validation api You can not use name, email and phone number in password 5");
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
                            Logger("check password validation api You can not use name, email and phone number in password 6");
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
                            Logger("check password validation api You can not use name, email and phone number in password 7");
                            return response()->json([
                                'status_code' => 203,
                                'message' => 'You can not use name, email and phone number in password.',
                            ]);
                        }


                        if($first_lat_name_flag == false || $first_name_flag == false || $last_name_flag == false || $email_flag == false || $email_flag_start == false || $mobile_number_flag == false){
                            Logger("check password validation api password match success");
                            return response()->json([
                                'status_code' => 200,
                                'message' => 'success.',
                            ]);
                        }
                    }
                    catch (Exception $e) {
                    }
                }
            }else {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'user not found',
                ]);
            }
        }
    }

    public function customerDeleteAccount(Request $request){
        $validator = Validator::make($request->only('token'), [
            'token' => 'required',
        ]);

        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
            if($user->is_expired == 0){
                $customer = Customers::where('id', $user->id)->whereNull('deleted_at')->first();
                if($customer) {
                    Customers::where('id', $user->id)->update([
                        'is_deleted' => 1,
                        'deleted_at' => Carbon::now()
                    ]);
                    JWTAuth::invalidate($request->token);
                    return response()->json([
                        'status_code' => 200,
                        'status' => 200,
                        'message' => 'success',
                    ]);
                }else {
                    return response()->json([
                        'status_code' => 202,
                        'status' => 202,
                        'message' => 'Not found',
                    ]);
                }
            }else{
                return response()->json([
                    'status' => 407,
                    'message' => 'Your password has been expired.</br>
                                  Please reset your password now.',
                ]);
            }
        }else {
            return response()->json([
                'status' => 400,
                'message' => 'user not found',
            ]);
        }
    }
}
