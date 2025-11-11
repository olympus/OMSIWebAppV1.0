<?php

namespace App\Http\Controllers;

use App\AdminPasswordHistory;
use App\Models\User;
use App\PasswordReset;
use App\Role;
use App\RoleUser;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mail;
use Validator;

//To hash the password

class AdminsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*public function index(AdminsDataTable $dataTable)
    {
        return $dataTable->render('admins.index');
    }*/

    public function index()
    {
        return view('admins.index');
    }

    public function adminList(Request $request)
    {
        try {
            $columns = array(
                0 => 'id',
                1 => 'name',
                2 => 'email',
                3 => 'Roles',
                4 => 'created_at',
                5 => 'updated_at'
            );

            $totalData = User::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = User::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('id','name','email','created_at','updated_at')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  User::
                select('id','name','email','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('name', 'LIKE',"%{$search}%")
                ->orWhere('email', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();

                $totalFiltered = User::
                select('id','name','email','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('name', 'LIKE',"%{$search}%")
                ->orWhere('email', 'LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $user) {
                    $roles = \App\RoleUser::where('user_id', $user->id)->pluck("role_id")->toArray();
                    $role_list = \App\Role::whereIn('id', $roles)->pluck("display_name")->toArray();
                    $user->roles = implode(', ', $role_list);


                    $edit =  route('admins.edit',$user->id);
                    $nestedData['id'] = $user->id;
                    $nestedData['name'] = $user->name;
                    $nestedData['email'] = $user->email;
                    $nestedData['roles'] = $user->roles;
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($user->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($user->updated_at));
                    $nestedData['options'] = "
                        <a style='width: 100%;' class='btn btn-xs btn-info' href='{$edit}' title='Edit'>Edit</a>
                        <a style='width: 100%;'
                         class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure you want to delete`)' href='admins/deletes/{$user->id}'>
                            Delete
                        </a>
                    ";
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
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admins.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeOld(Request $request)
    {
        $this->validate(request(), [
            'name' 		=> 'required|min:2|max:100',
            'email' 	=> 'required|email|unique:users,email|min:2|max:100',
            'password'	=> 'required|min:6|max:100'
        ]);//Validation for form data

        //Create new user data
        $user = new User;
        $user->name = request('name');
        $user->email = request('email');
        $user->password = Hash::make(request('password')); //hash password
        $user->save(); // save user to db

        return redirect('/admin/admins')->with('message', 'New account created');
    }

    public function store(Request $request)
    {
        $this->validate(request(), [
            'name'      => 'required|regex:/^[a-zA-Z\s]*$/|min:2|max:100',
            'email'     => 'required|email|unique:users,email',
            'password' => 'required|string|min:20|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
        ],[
            "password.required"=>"Password is required",
            "email.required"=>"Email is required",
            "email.unique" => "Email already exists",
            "password.*"=>"Invalid password. Password should be in minimum 20 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
        ]);
        $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->password));
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

        $chk_pass_space = $request->get('password');
        if(str_contains($chk_pass_space, ' ')){
            return back()->with('error', 'You can not use space in your password.');
        }


        $name = strtolower(str_replace(' ', '', $request->name));
        $email = strtolower($request->email);
        $parts = explode('@', $email);
        $namePart = $parts[0];

        $first_name_match = explode(' ', $name);

        $name_flag = false;
        foreach($first_name_match as $first_name_matchs){
            if(str_contains(strtolower($string), strtolower($first_name_matchs))){
                $name_flag = true;
                break;
            }
        }

        if ($name_flag == true) {
            return back()->with('error', 'You can not use name and email in password.');
        }

        $complete_name_flag = false;
        if(str_contains(strtolower($string), strtolower($name))){
            $complete_name_flag = true;
        }

        if ($complete_name_flag == true) {
            return back()->with('error', 'You can not use name and email in password.');
        }

        $email_flag = false;
        if(str_contains(strtolower($request->get('password')), strtolower($email))){
            $email_flag = true;
        }
        if ($email_flag == true) {
            return back()->with('error', 'You can not use name and email in password.');
        }

        $email_flag_start = false;
        if(str_contains(strtolower($request->get('password')), strtolower($namePart))){
            $email_flag_start = true;
        }
        if ($email_flag_start == true) {
            return back()->with('error', 'You can not use name and email in password.');
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
            return back()->with('error', 'You can not use name and email in password.');
        }


        if($flag == false || $name_flag == false || $email_flag == false || $email_flag_start == false || $chk_email_rule_flag == false || $complete_name_flag == false){
            $user = User::create([
                'name' => request('name'),
                'email' => request('email'),
                'password' => bcrypt(request('password')),
                'password_updated_at' => Carbon::now(),
                'is_expired' => 0,
            ]);
            $pass = new AdminPasswordHistory();
            $pass->user_id = $user->id;
            $pass->password = bcrypt($request->password);
            $pass->save();
            return redirect('/admin/admins')->with('message', 'New account created');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
        $user = User::findOrFail($id);
        $roles = Role::get();
        $role_list = RoleUser::where('user_id', $id)->pluck("role_id")->toArray();
        $user_roles = Role::whereIn('id', $role_list)->select("display_name","id")->get()->toArray();
        // dd($user_roles);
        return view('admins.edit', ['user'=>$user,'roles'=>$roles, 'user_roles'=>$user_roles]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateOld(Request $request, $id)
    {
        $this->validate(request(), [
            'name' 		=> 'required|min:2|max:100',
            'password'	=> 'required|min:6|max:100'
        ]);//Validation for form data
        $role_ids = RoleUser::where('user_id', $id)->pluck("role_id")->toArray();
        $all_roles_list = Role::pluck("id")->toArray();

        // dd($request->toArray(),$role_ids,$all_roles_list);
        $password = User::where('id', $id)->get()->pluck('password')[0]; //Get hashed user password as string
        User::where('id', $id)->update(array(
            'name' 	  => request('name'),
            //Check if password changed, then update, else keep old password
            'password'  => ($password === request('password') ? $password : Hash::make(request('password')))
        ));//Update values

        // Remove all existing roles
        RoleUser::where('user_id', $id)->delete();

        foreach($request->roles as $new_role){
            if(in_array($new_role,$all_roles_list)){
                // $exists = RoleUser::where('user_id', $id)->where( 'role_id',$new_role)->first();
                // if (is_null($exists)) {
                    RoleUser::insert(array(
                    'role_id'=>$new_role,
                    'user_id'=>$id,
                    'user_type'=> 'App\Models\User'
                ));
                // }
            }
        }
        return redirect('/admin/admins')->with('message', 'Account updated');
    }

    public function update(Request $request, $id)
    {
        $this->validate(request(), [
            'id' => 'numeric',
            'name'      => 'required|regex:/^[a-zA-Z\s]*$/|min:2|max:100',
            'password' => 'required|string|min:20|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/'
        ],[
            "password.required"=>"Password is required",
            "password.*"=>"Invalid password. Password should be in minimum 20 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
        ]);

        $user = User::whereId($id)->firstOrFail();
        if($user->password != $request->password) {
            $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->password));
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

            $chk_pass_space = $request->get('password');
            if(str_contains($chk_pass_space, ' ')){
                return back()->with('error', 'You can not use space in your password.');
            }


            $email_check = User::where('id', $id)->first();
            $name = strtolower($request->name);
            $email = strtolower($email_check->email);

            $parts = explode('@', $email);
            $namePart = $parts[0];

            $first_name_match = explode(' ', $name);
            //dd($first_name_match);

            $name_flag = false;
            foreach($first_name_match as $first_name_matchs){
                if(str_contains(strtolower($string), strtolower($first_name_matchs))){
                    $name_flag = true;
                    break;
                }
            }

            if ($name_flag == true) {
                return back()->with('error', 'You can not use name and email in password.');
            }

            $complete_name_flag = false;
            if(str_contains(strtolower($string), strtolower($name))){
                $complete_name_flag = true;

            }

            if ($complete_name_flag == true) {
                return back()->with('error', 'You can not use name and email in password.');
            }

            $email_flag = false;
            if(str_contains(strtolower($request->get('password')), strtolower($email))){
                $email_flag = true;
            }
            if ($email_flag == true) {
                return back()->with('error', 'You can not use name and email in password.');
            }

            $email_flag_start = false;
            if(str_contains(strtolower($request->get('password')), strtolower($namePart))){
                $email_flag_start = true;
            }
            if ($email_flag_start == true) {
                return back()->with('error', 'You can not use name and email in password.');
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
                return back()->with('error', 'You can not use name and email in password.');
            }

            if($flag == false || $name_flag == false || $email_flag == false || $email_flag_start == false){
                $total_password = AdminPasswordHistory::where('user_id', $id)->count();
                $old_pass_delete = AdminPasswordHistory::where('user_id', $id)->latest()->take($total_password)->skip(5)->get();
                foreach($old_pass_delete as $old_pass_deletes){
                    AdminPasswordHistory::where('id',$old_pass_deletes->id)->delete();
                }
                $get_latest_password = $user->adminPasswordHistories()->orderBy('id','desc')->take(5)->get();
                foreach($get_latest_password as $get_latest_passwords){
                    if (Hash::check($request->get('password'), $get_latest_passwords->password)) {
                        return back()->with('error', 'You can not use last 5 password.');
                    }
                }
                $role_ids = RoleUser::where('user_id', $id)->pluck("role_id")->toArray();
                $all_roles_list = Role::pluck("id")->toArray();

                $password = User::where('id', $id)->get()->pluck('password')[0];
                User::where('id', $id)->update(array(
                    'name'    => request('name'),
                    'password'  => ($password === request('password') ? $password : Hash::make(request('password'))),
                    'password_updated_at' => Carbon::now(),
                    'is_expired' => 0
                ));

                // Remove all existing roles
                RoleUser::where('user_id', $id)->delete();

                if(isset($request->roles)){
                    foreach($request->roles as $new_role){
                        if(in_array($new_role,$all_roles_list)){
                            RoleUser::insert(array(
                                'role_id'=>$new_role,
                                'user_id'=>$id,
                                'user_type'=> 'App\Models\User'
                            ));
                        }
                    }
                }
                $pass = new AdminPasswordHistory();
                $pass->user_id = $user->id;
                $pass->password = bcrypt($request->password);
                $pass->save();
                if(!empty($request->roles)){ $user->syncRoles($request->roles); }
                return redirect('/admin/admins')->with('message', 'Account updated');
            }
        }else {
            $role_ids = RoleUser::where('user_id', $id)->pluck("role_id")->toArray();
            $all_roles_list = Role::pluck("id")->toArray();

            $password = User::where('id', $id)->get()->pluck('password')[0];
            User::where('id', $id)->update(array(
                'name'    => request('name'),
                'password'  => ($password === request('password') ? $password : Hash::make(request('password')))
            ));

            // Remove all existing roles
            RoleUser::where('user_id', $id)->delete();

            if(isset($request->roles)){
                foreach($request->roles as $new_role){
                    if(in_array($new_role,$all_roles_list)){
                        RoleUser::insert(array(
                            'role_id'=>$new_role,
                            'user_id'=>$id,
                            'user_type'=> 'App\Models\User'
                        ));
                    }
                }
            }
            return redirect('/admin/admins')->with('message', 'Account updated');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
        $role =  \App\Role::where('id', 1)->first();
        $role_user =  \App\RoleUser::where('role_id', 1)->get();
        foreach($role_user as $role_users){
            if($role_users->user_id == $id){
                return redirect('/admin/admins')->with('message', 'Super Admin Account Can Not deleted');
            }else{
                User::where('id', $id)->delete();//Delete user
                return redirect('/admin/admins')->with('message', 'Account deleted');
            }
        }
    }

    public function deletes($id)
    {
        //dd("deletes:".$id);

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
        $role = \App\Role::where('id', 1)->first();
        $role_user = \App\RoleUser::where('role_id', 1)->get();
        foreach($role_user as $role_users){
            if($role_users->user_id == $id){
                return redirect('/admin/admins')->with('message', 'Super Admin Account Can Not deleted');
            }else{
                User::where('id', $id)->delete();//Delete user
                return redirect('/admin/admins')->with('message', 'Account deleted');
            }
        }
    }

    public function showForgetPasswordForm(){
        return view('vendor.adminlte.passwords.email');
    }

    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ],[
            "email.exists"=>"If your email id is registered then reset password link will be sent",
        ]);

        $token = Str::random(64);

        $password_reset = new PasswordReset();
        $password_reset->email = $request->email;
        $password_reset->token = $token;
        $password_reset->created_at = Carbon::now();
        $password_reset->updated_at = Carbon::now();
        $password_reset->save();



        Mail::send('emails.forgetPassword', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return back()->with('message', 'If your email id is registered then reset password link will be sent');
    }

    public function showResetPasswordForm($token) {
        $validator = Validator::make(
          [
            'token' => $token,
          ],[
            'token' => 'required|string|regex:/^[a-zA-Z0-9\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }

        $updatePassword = PasswordReset::where([
            'token' => $token
        ])->first();
        if($updatePassword){
            return view('vendor.adminlte.passwords.reset', ['token' => $token, 'email' => $updatePassword->email]);
        }else{
            return redirect('/login');
        }
    }

    public function submitResetPasswordForm(Request $request){
        $this->validate(request(), [
            'email' => 'required|exists:users',
            'password' => 'required|string|min:20|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[#?!@$%^&*-]/',
            'password_confirmation' => 'same:password'

        ],[
            "email.required"=>"Email is required",
            "password.required"=>"Password is required",
            "password.*"=>"Invalid password. Password should be in minimum 20 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character."
        ]);
        $chk_token_email = PasswordReset::where(['token' => $request->token, 'email' => $request->email])->first();
        if(empty($chk_token_email)){
            return back()->with('error', 'Please Enter Valid Email.');
        }
        $user = User::where('email', $request->email)->first();
        if(!empty($user)){
            $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $request->password));
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

            $chk_pass_space = $request->get('password');
            if(str_contains($chk_pass_space, ' ')){
                return back()->with('error', 'You can not use space in your password.');
            }

            $name = strtolower($user->name);
            $email = strtolower($request->email);

            $parts = explode('@', $email);
            $namePart = $parts[0];

            $first_name_match = explode(' ', $name);
            //dd($first_name_match);

            $name_flag = false;
            foreach($first_name_match as $first_name_matchs){
                if(str_contains(strtolower($string), strtolower($first_name_matchs))){
                    $name_flag = true;
                    break;
                }
            }

            if ($name_flag == true) {
                return back()->with('error', 'You can not use name and email in password.');
            }

            $complete_name_flag = false;
            if(str_contains(strtolower($string), strtolower($name))){
                $complete_name_flag = true;

            }

            if ($complete_name_flag == true) {
                return back()->with('error', 'You can not use name and email in password.');
            }

            $email_flag = false;
            if(str_contains(strtolower($request->get('password')), strtolower($email))){
                $email_flag = true;
            }
            if ($email_flag == true) {
                return back()->with('error', 'You can not use name and email in password.');
            }

            $email_flag_start = false;
            if(str_contains(strtolower($request->get('password')), strtolower($namePart))){
                $email_flag_start = true;
            }
            if ($email_flag_start == true) {
                return back()->with('error', 'You can not use name and email in password.');
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
                return back()->with('error', 'You can not use name and email in password.');
            }
            if($flag == false || $name_flag == false || $email_flag == false || $email_flag_start == false){
                $total_password = AdminPasswordHistory::where('user_id', $user->id)->count();
                $old_pass_delete = AdminPasswordHistory::where('user_id', $user->id)->latest()->take($total_password)->skip(5)->get();
                foreach($old_pass_delete as $old_pass_deletes){
                    AdminPasswordHistory::where('id',$old_pass_deletes->id)->delete();
                }
                $get_latest_password = $user->adminPasswordHistories()->orderBy('id','desc')->take(5)->get();
                foreach($get_latest_password as $get_latest_passwords){
                    if (Hash::check($request->get('password'), $get_latest_passwords->password)) {

                        return back()->with('error', 'You can not use last 5 password.');
                    }
                }
                $user->update([
                    'password'=> bcrypt($request->password),
                    'password_updated_at' => Carbon::now(),
                    'is_expired' => 0,
                    'token'=>null
                ]);
                $pass = new AdminPasswordHistory();
                $pass->user_id = $user->id;
                $pass->password = bcrypt($request->password);
                $pass->save();

                PasswordReset::where(['email'=> $request->email])->delete();
                // Auth::login($user);
                // return redirect('/admin/requests');
                return redirect('/login')->with('message','Please login with your new credentials.');
            }
        }else{
           return back()->with('error', 'Not Found.');
        }
    }

    public function loginSubmit(Request $request){
        $this->validate(request(), [
            'email' => 'required|exists:users',
            'password' => 'required'

        ],[
            "email.required"=>"Email is required",
            "password.required"=>"Password is required",
        ]);
        $user = User::where('email', $request->email)->where('is_expired', 0)->first();
        if(!empty($user)){
            $userData = array(
                'email'     => $request->get('email'),
                'password'  => $request->get('password')
            );
            if (Auth::attempt($userData)) {
                return redirect('/admin');
            } else {
                return redirect('/login')->with('error','Invalid Credentials.');
                //return redirect('/login')->with('error','These credentials do not match our records.');
            }
        }else{
            return redirect('/login')->with('error','Your password has been expired.Please reset your password now.');
        }
    }
}

