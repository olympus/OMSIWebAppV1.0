<?php

namespace App\Http\Controllers;

use App\DataTables;
use App\Models\Departments;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\PasswordHistory;
use Auth;
use Carbon\Carbon;
use DB;
use Excel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use JWTAuth;
use Tymon\JWTAuth\Middleware\GetUserFromToken;
use Validator;

class CustomersController extends Controller
{
    /**
     * Display index page and process dataTable ajax request.
     *
     * @param DataTables\UsersDataTable $dataTable
     * @return JsonResponse|View
     */
    public function index()
    {
        return view('customers.index');
    }

    public function customerList(Request $request)
    {

        // if($request->from_date && $request->to_date && $request->data_type == 'archive_data'){
        //     $from_date = $request->from_date.' 00:00:00';
        //     $to_date = $request->to_date.' 23:59:59';
        // }elseif($request->from_date && $request->to_date && $request->data_type == 'current_data'){
        //     $month = date('m');
        //     if($month >= 4){
        //         $y = date('Y');
        //         $pt = date('Y', strtotime('+1 year'));
        //     }else{
        //         $pt = date('Y');
        //         $y = date('Y', strtotime('-1 year'));
        //     }
        //     $from_date = $y."-04-01".' 00:00:00';
        //     $to_date = $pt."-03-31".' 23:59:59';
        //     //dd($from_date,$to_date);
        // }else{
        //     $month = date('m');
        //     if($month >= 4){
        //         $y = date('Y');
        //         $pt = date('Y', strtotime('+1 year'));
        //     }else{
        //         $pt = date('Y');
        //         $y = date('Y', strtotime('-1 year'));
        //     }
        //     $from_date = $y."-04-01".' 00:00:00';
        //     $to_date = $pt."-03-31".' 23:59:59';
        // }
        if($request->from_date && $request->to_date){
            $from_date = $request->from_date.' 00:00:00';
            $to_date = $request->to_date.' 23:59:59';
        }else{
            $month = date('m');
            if($month >= 4){
                $y = date('Y');
                $pt = date('Y', strtotime('+1 year'));
            }else{
                $pt = date('Y');
                $y = date('Y', strtotime('-1 year'));
            }
            $from_date = $y."-04-01".' 00:00:00';
            $to_date = $pt."-03-31".' 23:59:59';
        }

        try{
            $columns = array(
                0 => 'id',
                1 => 'customer_id',
                2 => 'sap_customer_id',
                3 => 'first_name',
                4 => 'last_name',
                5 => 'mobile_number',
                6 => 'email',
                7 => 'otp_code',
                8 => 'is_verified',
                9 => 'created_at',
            );

            if($request->page_url =='admin/customers'){
                $totalData = Customers::whereBetween('created_at', [$from_date, $to_date])->where('email', 'NOT LIKE', '%@olympus.com%')->count();
            }else{
                $totalData = Customers::where('email', 'LIKE', '%@olympus.com%')->count();
            }

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                if($request->page_url == 'admin/customers'){
                    $posts = Customers::
                    whereBetween('created_at', [$from_date, $to_date])
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy('id',$dir)
                    ->select('id','customer_id', 'sap_customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
                    ->where('email', 'NOT LIKE', '%@olympus.com%')
                    ->get();
                }else{
                    $posts = Customers::
                    offset($start)
                    ->limit($limit)
                    ->orderBy('id',$dir)
                    ->select('id','customer_id', 'sap_customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
                    ->where('email', 'LIKE', '%@olympus.com%')
                    ->get();
                }
            }
            else{
                $search = $request->input('search.value');
                if($request->page_url =='admin/customers'){
                    $posts =  Customers::
                    whereBetween('created_at', [$from_date, $to_date])
                    ->select('id','customer_id', 'sap_customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
                    ->where('email', 'NOT LIKE', '%@olympus.com%')
                    ->where('id','LIKE',"%{$search}%")
                    ->orWhere('customer_id', 'LIKE',"%{$search}%")
                    ->orWhere('sap_customer_id', 'LIKE',"%{$search}%")
                    ->orWhere('first_name', 'LIKE',"%{$search}%")
                    ->orWhere('last_name', 'LIKE',"%{$search}%")
                    ->orWhere('mobile_number', 'LIKE',"%{$search}%")
                    ->orWhere('email', 'LIKE',"%{$search}%")
                    ->orWhere('otp_code', 'LIKE',"%{$search}%")
                    ->orWhere('is_verified', 'LIKE',"%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy('id',$dir)
                    ->get();

                    $totalFiltered = Customers::
                    whereBetween('created_at', [$from_date, $to_date])
                    ->select('id','customer_id', 'sap_customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
                    ->where('email', 'NOT LIKE', '%@olympus.com%')
                    ->where('id','LIKE',"%{$search}%")
                    ->orWhere('customer_id', 'LIKE',"%{$search}%")
                    ->orWhere('sap_customer_id', 'LIKE',"%{$search}%")
                    ->orWhere('first_name', 'LIKE',"%{$search}%")
                    ->orWhere('last_name', 'LIKE',"%{$search}%")
                    ->orWhere('mobile_number', 'LIKE',"%{$search}%")
                    ->orWhere('email', 'LIKE',"%{$search}%")
                    ->orWhere('otp_code', 'LIKE',"%{$search}%")
                    ->orWhere('is_verified', 'LIKE',"%{$search}%")
                    ->count();
                }else{
                    $posts =  Customers::
                    select('id','customer_id', 'sap_customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
                    ->where('email', 'LIKE', '%@olympus.com%')
                    ->where('id','LIKE',"%{$search}%")
                    ->orWhere('customer_id', 'LIKE',"%{$search}%")
                    ->orWhere('sap_customer_id', 'LIKE',"%{$search}%")
                    ->orWhere('first_name', 'LIKE',"%{$search}%")
                    ->orWhere('last_name', 'LIKE',"%{$search}%")
                    ->orWhere('mobile_number', 'LIKE',"%{$search}%")
                    ->orWhere('email', 'LIKE',"%{$search}%")
                    ->orWhere('otp_code', 'LIKE',"%{$search}%")
                    ->orWhere('is_verified', 'LIKE',"%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy('id',$dir)
                    ->get();

                    $totalFiltered = Customers::
                    select('id','customer_id', 'sap_customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
                    ->where('email', 'LIKE', '%@olympus.com%')
                    ->where('id','LIKE',"%{$search}%")
                    ->orWhere('customer_id', 'LIKE',"%{$search}%")
                    ->orWhere('sap_customer_id', 'LIKE',"%{$search}%")
                    ->orWhere('first_name', 'LIKE',"%{$search}%")
                    ->orWhere('last_name', 'LIKE',"%{$search}%")
                    ->orWhere('mobile_number', 'LIKE',"%{$search}%")
                    ->orWhere('email', 'LIKE',"%{$search}%")
                    ->orWhere('otp_code', 'LIKE',"%{$search}%")
                    ->orWhere('is_verified', 'LIKE',"%{$search}%")
                    ->count();
                }
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $post)
                {
                    $hospitals = Hospitals::where('customer_id',$post->id)->get();
                    $hospitals_name = Hospitals::where('customer_id',$post->id)->pluck('hospital_name')->all();
                    $hospital_names = implode(', ', $hospitals_name);
                    $count = 1;

                    $city = [];
                    $state = [];
                    foreach($hospitals as $hospital){
                        $dept_ids = explode(',',$hospital->dept_id);
                        $departments = Departments::whereIn('id',$dept_ids)->pluck('name')->all();
                        $depart_names = implode(', ', $departments);
                        $city[] = $hospital->city;
                        $state[] = $hospital->state;
                    }

                    $show =  route('customers.show',$post->id);
                    $edit =  route('customers.edit',$post->id);
                    $nestedData['id'] = $post->id;
                    $nestedData['customer_id'] = $post->customer_id;
                    $nestedData['sap_customer_id'] = $post->sap_customer_id;
                    $nestedData['first_name'] = $post->first_name;
                    $nestedData['last_name'] = $post->last_name;
                    $nestedData['mobile_number'] = $post->mobile_number;
                    $nestedData['email'] = $post->email;
                    $nestedData['hospital_names'] = $hospital_names;
                    $nestedData['departments'] = $depart_names;
                    $nestedData['city_names'] = implode(',',array_unique($city));
                    $nestedData['state_names'] = implode(',',array_unique($state));
                    $nestedData['otp_code'] = $post->otp_code;
                    $nestedData['is_verified'] = $post->is_verified;
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($post->created_at));
                    if(Auth::user()->isA('superadministrator|administrator|reader|administratorservice|administratorenquiry|administratoracademic')){
                        $nestedData['options'] = "
                            <a style='width: 100%;' class='btn btn-xs btn-success' href='{$show}'  title='Show'>Show</a>
                            <a style='width: 100%;' class='btn btn-xs btn-info' href='{$edit}' title='Edit'>Edit</a>
                            <a style='width: 100%;'
                             class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure you want to delete`)' href='customers/deletes/{$post->id}'>
                                Delete
                            </a>
                        ";
                    }else{
                        $nestedData['options'] = "
                            <a style='width: 100%;' class='btn btn-xs btn-success' href='{$show}'  title='Show'>Show</a>

                        ";
                    }

                    $data[] = $nestedData;
                }
            }
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );
            echo json_encode($json_data);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function show($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|integer',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $user = Customers::findOrFail($id);
        return view('customers.show', ['user'=>$user]);
    }

    public function edit($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|integer',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $user = Customers::findOrFail($id);
        return view('customers.edit', ['user'=>$user]);
    }

    public function update(Request $request, $id)
    {
        $this->validate(request(), [
            'first_name'      => 'required|regex:/^[a-zA-Z\s]*$/|min:2|max:100',
            'last_name'      => 'regex:/^[a-zA-Z\s]*$/|min:2|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('customers')->ignore($id)->whereNull('deleted_at'),
                // Rule::unique('customers')->where(function ($query) {
                //     return $query->whereNull('deleted_at');
                // }),
            ],
            'mobile_number' => [
                'required',
                'string',
                Rule::unique('customers')->ignore($id)->whereNull('deleted_at'),
            ],
            'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
        ],[
            "password.required"=>"Password is required",
            "password.*"=>"Invalid password. Password should be in minimum 8 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
        ]);



        $chk_pass_space = $request->get('password');
        if(str_contains($chk_pass_space, ' ')){
            return back()->with('error', 'you can not use any white space password.');
        }
        $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->get('password')));
        $string = $password;
        $blacklistArray = ['abc', 'bcd', 'cde', 'def', 'efg', 'fgh', 'ghi', 'hij', 'Ijk', 'jkl', 'klm', 'lmn', 'mno', 'nop', 'opq', 'pqr', 'qrs', 'rst', 'stu', 'tuv', 'uvw', 'vwx', 'wxy', 'xyz', 'yza', 'zab','abc','ABC', 'BCD', 'CDE', 'DEF', 'EFG', 'FGH', 'GHI', 'HIJ', 'IJK', 'JKL', 'KLM', 'LMN', 'MNO', 'NOP', 'OPQ', 'PQR', 'QRS', 'RST', 'STU', 'TUV', 'UVW', 'VWX', 'WXY', 'XYZ', 'YZA', 'ZAB','ABC'];
        $flag = false;
        foreach ($blacklistArray as $k => $v) {
            if(str_contains($string, $v)){
                $flag = true;
                break;
            }
        }
        if ($flag == true) {
           return back()->with('error', 'Also, password should not contain 3 sequence alphabetic characters. For eg: abc, bcd etc.');
        }

        $customerEmailCheck = Customers::where('id', $id)->first();

        // if($customerEmailCheck->mobile_number != $request->mobile_number){
        //     $chk_mobile = Customers::where('mobile_number', $request->mobile_number)->whereNull('deleted_at')->get();
        //     if(!empty($chk_mobile)){
        //         return back()->with('error', 'Mobile number already exists');
        //     }
        // }
        // if($customerEmailCheck->email != $request->email){
        //     $chk_email = Customers::where('email', $request->email)->whereNull('deleted_at')->get();
        //     if(!empty($chk_email)){
        //         return back()->with('error', 'Email number already exists');
        //     }
        // }

        $first_name = strtolower($customerEmailCheck->first_name);
        $last_name = strtolower($customerEmailCheck->last_name);
        $email = strtolower($customerEmailCheck->email);
        $mobile_number = $customerEmailCheck->mobile_number;

        $parts = explode('@', $email);
        $namePart = $parts[0];

        $mobile_number_parts = explode('91', $mobile_number);
        $mobileamePart = $mobile_number_parts[1];


        $first_name_match = explode(' ', $first_name);
        $last_name_match = explode(' ', $last_name);

        $first_lat_name_flag = false;
        if(str_contains(strtolower($string), strtolower($first_name.$last_name))){
            $first_lat_name_flag = true;
        }
        if ($first_lat_name_flag == true) {
            return back()->with('error', 'you can not use name, email and phone number in password..');
        }

        $first_name_flag = false;
        // if(str_contains(strtolower($string), strtolower($first_name))){
        //     $first_name_flag = true;
        // }
        foreach($first_name_match as $first_name_matchs){
            if(str_contains(strtolower($string), strtolower($first_name_matchs))){
                $first_name_flag = true;
                break;
            }
        }
        if ($first_name_flag == true) {
            return back()->with('error', 'you can not use name, email and phone number in password.');
        }

        $last_name_flag = false;
        // if(str_contains(strtolower($string), strtolower($last_name))){
        //     $last_name_flag = true;
        // }
        foreach($last_name_match as $last_name_matchs){
            if(str_contains(strtolower($string), strtolower($last_name_matchs))){
                $last_name_flag = true;
                break;
            }
        }
        if ($last_name_flag == true) {
            return back()->with('error', 'you can not use name, email and phone number in password.');
        }

        $email_flag = false;
        if(str_contains(strtolower($request->get('password')), strtolower($email))){
            $email_flag = true;
        }
        if ($email_flag == true) {
            return back()->with('error', 'you can not use name, email and phone number in password.');
        }

        $email_flag_start = false;
        if(str_contains(strtolower($request->get('password')), strtolower($namePart))){
            $email_flag_start = true;
        }
        if ($email_flag_start == true) {
            return back()->with('error', 'you can not use name, email and phone number in password.');
        }

        $mobile_number_flag = false;
        if(str_contains($request->get('password'), $mobileamePart)){
            $mobile_number_flag = true;
        }
        if ($mobile_number_flag == true) {
            return back()->with('error', 'you can not use name, email and phone number in password.');
        }

        if($first_lat_name_flag == false && $first_name_flag == false && $last_name_flag == false && $email_flag == false && $email_flag_start == false && $mobile_number_flag == false){
            // else{
            $customer = Customers::where('id', $id)->first();
            if($customer->password != $request->password) {
                if ($customer) {
                    $total_password = PasswordHistory::where('customer_id', $customer->id)->count();
                    $old_pass_delete = PasswordHistory::where('customer_id', $customer->id)->latest()->take($total_password)->skip(5)->get();
                    foreach($old_pass_delete as $old_pass_deletes){
                        PasswordHistory::where('id',$old_pass_deletes->id)->delete();
                    }
                    $get_latest_password = PasswordHistory::where('customer_id', $customer->id)->orderBy('id','desc')->take(5)->get();
                    foreach($get_latest_password as $get_latest_passwords){
                        if (Hash::check($request->get('password'), $get_latest_passwords->password)) {
                            return back()->with('error', 'You can not use last 5 password.');
                        }
                    }
                    Customers::where('id',$customer->id)->update([
                        'first_name'      => request('first_name'),
                        'last_name'       => request('last_name'),
                        'password'        => bcrypt($request->get('password')),
                        'password_updated_at' => Carbon::now(),
                        'is_expired'      => 0,
                        'mobile_number'   => request('mobile_number'),
                        'email'           => request('email'),
                        'is_verified'     => request('is_verified')=='on'?true:false,
                    ]);

                    //Insert Password In Password History Table
                    $pass = new PasswordHistory();
                    $pass->customer_id = $customer->id;
                    $pass->password = bcrypt($request->password);
                    $pass->save();

                    return redirect('/admin/customers')->with('message', 'Account updated');
                }
                else {
                    return redirect('/admin/customers')->with('error', 'Customer Not Found');
                }
            }else{
                if ($customer) {
                    Customers::where('id',$customer->id)->update([
                        'first_name'      => request('first_name'),
                        'last_name'       => request('last_name'),
                        'mobile_number'   => request('mobile_number'),
                        'email'           => request('email'),
                        'is_verified'     => request('is_verified')=='on'?true:false,
                    ]);
                    return redirect('/admin/customers')->with('message', 'Account updated');
                }
                else {
                    return redirect('/admin/customers')->with('error', 'Customer Not Found');
                }
            }
        }
        // Customers::where('id', $id)->update(array(
        //     'first_name'      => request('first_name'),
        //     'last_name'       => request('last_name'),
        //     'password'        => request('password'),
        //     'mobile_number'   => request('mobile_number'),
        //     'is_verified'     => request('is_verified')=='on'?true:false,
        // ));
        // return redirect('/admin/customers')->with('message', 'Account updated');
    }

    public function destroy($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|integer',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        Customers::where('id', $id)->delete();//Delete user
        ServiceRequests::where('customer_id',$id)->delete();
        Hospitals::where('customer_id',$id)->delete();
        return redirect('/admin/customers')->with('message', "Customer $id deleted");
    }

    public function deletes($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|integer',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        Customers::where('id', $id)->delete();//Delete user
        ServiceRequests::where('customer_id',$id)->delete();
        Hospitals::where('customer_id',$id)->delete();
        return redirect('/admin/customers')->with('message', "Customer $id deleted");
    }

    public function export(Request $request)
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        // if($request->from_date && $request->to_date && $request->data_type == 'archive_data'){
        //     $from_date = $request->from_date.' 00:00:00';
        //     $to_date = $request->to_date.' 23:59:59';
        // }elseif($request->from_date && $request->to_date && $request->data_type == 'current_data'){
        //     $month = date('m');
        //     if($month >= 4){
        //         $y = date('Y');
        //         $pt = date('Y', strtotime('+1 year'));
        //     }else{
        //         $pt = date('Y');
        //         $y = date('Y', strtotime('-1 year'));
        //     }
        //     $from_date = $y."-04-01".' 00:00:00';
        //     $to_date = $pt."-03-31".' 23:59:59';
        // }else{
        //     $month = date('m');
        //     if($month >= 4){
        //         $y = date('Y');
        //         $pt = date('Y', strtotime('+1 year'));
        //     }else{
        //         $pt = date('Y');
        //         $y = date('Y', strtotime('-1 year'));
        //     }
        //     $from_date = $y."-04-01".' 00:00:00';
        //     $to_date = $pt."-03-31".' 23:59:59';
        // }
        if($request->from_date && $request->to_date){
            $from_date = $request->from_date.' 00:00:00';
            $to_date = $request->to_date.' 23:59:59';
        }else{
            $month = date('m');
            if($month >= 4){
                $y = date('Y');
                $pt = date('Y', strtotime('+1 year'));
            }else{
                $pt = date('Y');
                $y = date('Y', strtotime('-1 year'));
            }
            $from_date = $y."-04-01".' 00:00:00';
            $to_date = $pt."-03-31".' 23:59:59';
        }
        if($request->page_url =='admin/customers'){
            $file_name = 'customer';
            $user = Customers::whereBetween('created_at', [$from_date, $to_date])->select('id', 'sap_customer_id', 'customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
            ->where('email', 'NOT LIKE', '%@olympus.com%')
            ->get();
        }else{
            $file_name = 'olympus_customer';

            $user = Customers::select('id', 'sap_customer_id', 'customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
            ->where('email', 'LIKE', '%@olympus.com%')
            ->get();
        }
        foreach ($user as $user_temp) {
            $hospitals = Hospitals::where('customer_id', $user_temp->id)->get();

            $hospitals_name = Hospitals::where('customer_id', $user_temp->id)->pluck('hospital_name')->all();
            $hospital_names = implode(', ', $hospitals_name);
            $city = [];
            $state = [];
            $region = [];
            $branch = [];
            foreach ($hospitals as $hospital) {
                $dept_ids = explode(',', $hospital->dept_id);
                $departments = Departments::whereIn('id', $dept_ids)->pluck('name')->all();
                $depart_names = implode(', ', $departments);
                $region[] = ucfirst(find_region($hospital->state));
                $city[] = $hospital->city;
                $state[] = $hospital->state;
                $branch[] = $hospital->responsible_branch;
            }
            $user_temp->City = implode(',', array_unique($city));
            $user_temp->State = implode(',', array_unique($state));
            $user_temp->Region = implode(',', array_unique($region));
            $user_temp->Branch = implode(',', array_unique($branch));
            $user_temp->hospital_names = $hospital_names;

            $user_temp->departments = $depart_names;

        }
        Excel::create($file_name, function ($excel) use ($user) {
            $excel->sheet('Sheet 1', function ($sheet) use ($user) {
                $sheet->fromArray($user);
            });
        })->export('xls');
    }

    public function dataExcel(){
       $data = Customers::select('id', 'sap_customer_id', 'customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
            ->where('email', 'NOT LIKE', '%@olympus.com%')
            ->get();

        Excel::create('Customers', function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->export('xls');
    }

}
