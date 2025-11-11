<?php

namespace App\Http\Controllers;

use App\AssignRequest;
use App\Mail\AssignRequest as AssignRequestMail;
use App\Models\Customers;
use App\Models\EmployeeTeam;
use App\Models\ServiceRequests;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class AssignRequestsController extends Controller
{
    public function sendMail($id)
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
        $servicerequest = ServiceRequests::findOrFail($id);
        $customer = Customers::findOrFail($servicerequest->customer_id);
        $isMailAlreadySent = AssignRequest::where('service_request_id',$id)->first();
        if (is_null($isMailAlreadySent)) {
            $assign_request = new AssignRequest;
            $assign_request->service_request_id =$id;
            $assign_request->token = $id.'_'.bin2hex(openssl_random_pseudo_bytes(64));
            $assign_request->expired_at = date('Y-m-d H:i:s', strtotime('+7 days'));
            $assign_request ->save();
        }else{
            if(new DateTime() > new DateTime($isMailAlreadySent->expired_at)){
                $old_expired_at = $isMailAlreadySent->expired_at;
                $new_expired_at = date('Y-m-d H:i:s', strtotime('+7 days'));
                AssignRequest::where('service_request_id',$id)->update(['expired_at'=> $new_expired_at]);
                echo "Link Expired at ".$old_expired_at."<br><br>New expired_at ".$new_expired_at."<br><br>";
            }
        }
        $assign_request = AssignRequest::where('service_request_id',$id)->first();
        if(!is_null($assign_request)){
            Mail::to(\Config('oly.developer_email'))
            ->send(new AssignRequestMail($servicerequest, $customer, $assign_request));
            return 'success';
        }
        return 'error';
    }

    public function edit($token)
    {
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
        $assign_request = AssignRequest::where('token',$token)->first();
        if(is_null($assign_request)){
            return "Error: Token Invalid";
        }
        $servicerequest = ServiceRequests::find($assign_request->service_request_id);
        $customer = Customers::findOrFail($servicerequest->customer_id);
        if($assign_request->is_processed){
            if (!is_null($servicerequest->employee_code)) {
                $assigned_employee = EmployeeTeam::where('employee_code',$servicerequest->employee_code)->value('name');
                if(!is_null($assigned_employee)){
                    return "Request $servicerequest->id already processed. <br><br>Assigned to <b>$assigned_employee</b>";
                }
            }
            return "Request $servicerequest->id already processed.";
        }
        if(new DateTime() > new DateTime($assign_request->expired_at)){
            return "Link Expired at ".$assign_request->expired_at;
        }
        $employee_team = EmployeeTeam::where('disabled','0')->orderby('id', 'ASC')->select('name','employee_code','category')->get();
        return view('assign_request.assign', ['cust'=>$customer,'servicerequest'=>$servicerequest,'employee_team'=>$employee_team, 'token'=>$token]);
    }

    public function update(Request $request, $token)
    {
        $employee_code = $request->employee_code;
        $validator = Validator::make(
          [
            'token' => $token,
            'employee_code' => $employee_code
          ],[
            'token' => 'required|string|regex:/^[a-zA-Z0-9\s]*$/',
            'employee_code' => 'string|regex:/^[a-zA-Z0-9\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }

        // dd($request->toArray(),$token);
        $assign_request = AssignRequest::where('token',$token)->first();
        if(is_null($assign_request)){
            return "Error: Token Invalid";
        }
        $servicerequest = ServiceRequests::find($assign_request->service_request_id);
        $customer = Customers::findOrFail($servicerequest->customer_id);
        if(new DateTime() > new DateTime($assign_request->expired_at)){
            return "Link Expired at ".$assign_request->expired_at;
        }
        if($assign_request->is_processed){
            return "Request $servicerequest->id already processed.";
        }else{
            $servicerequest->status = "Assigned";
            $servicerequest->employee_code = $request->employee_code;
            $servicerequest->save();

            // Send mail

            // Mail::to($users_final)->cc($cc_final)
            // ->send(new RequestUpdated($pathToImage, $servicerequest, $customer));
            return "Request $servicerequest->id successfully updated";
        }
    }

}
