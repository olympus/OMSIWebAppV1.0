<?php

namespace App\Http\Controllers\API\V2;

use App\HappyCodeHistory;
use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\ServiceRequests;
use App\SFDC;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Response;
use Validator;

class RequestAcknowledgement extends Controller
{
    public function sendRequestAcknowledgementOtp(Request $request){
        Logger("Send Request Acknowledgement Otp :- ");
        Logger($request->all());
        $req_payload = $request->all();

        foreach($req_payload as $req_payloads){
            if(!empty($req_payloads['requestId']) && !empty($req_payloads['happyCode'])){
                $service_req = ServiceRequests::where('sfdc_id', $req_payloads['requestId'])->first();
                if(empty($service_req)){
                    $respArr['status_code'] = 202;
                    $respArr['message'] = 'Request not found.';
                    $respArr['data'] = null;
                    return response(json_encode($respArr), 202)->header('Content-Type', 'text/plain');
                }

                // Logger($service_req->id);
                // if(!empty($service_req)){

                $happy_code_delivered_time = Carbon::now();

                // Store or update happy code history
                HappyCodeHistory::updateOrCreate(
                    ['service_requests_id' => $service_req->id],
                    [
                        'service_requests_status' => $service_req->status,
                        'happy_code' => $req_payloads['happyCode'],
                        'happy_code_delivered_time' => $happy_code_delivered_time,
                    ]
                );


                if($service_req->status != "Closed"){
                    $customer = Customers::where('id', $service_req->customer_id)->first();

                    ServiceRequests::where('id', $service_req->id)->update([
                        'is_sms_send' => 1,
                        'is_happy_code' => 1,
                        'happy_code' => $req_payloads['happyCode'],
                        'happy_code_delivered_time' => $happy_code_delivered_time
                    ]);

                    //send_sms_request_acknowledged($customer, $service_req);

                    $respArr['status_code'] = 200;
                    $respArr['message'] = 'Otp send successfully.';
                    $respArr['data'] = null;
                    //return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }else{
                    $respArr['status_code'] = 200;
                    $respArr['message'] = 'This request is closed.';
                    $respArr['data'] = null;
                    //return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                }
            }else{
                $respArr['status_code'] = 202;
                $respArr['message'] = 'Request not found.';
                $respArr['data'] = null;

            }
        }
        return response(json_encode($respArr))->header('Content-Type', 'text/plain');
    }

    // public function sendRequestAcknowledgementOtpOld2(Request $request){
    //     Logger($request->all());
    //     $req_payload = $request->all();

    //     foreach($req_payload as $req_payloads){
    //         if (empty($req_payloads['requestId']) || empty($req_payloads['happyCode'])) {
    //             $respArr['status_code'] = 202;
    //             $respArr['message'] = 'Request not found.';
    //             $respArr['data'] = null;
    //             return response(json_encode($respArr), 202)->header('Content-Type', 'text/plain');
    //         }

    //         $service_req = ServiceRequests::where('sfdc_id', $req_payloads['requestId'])->first();

    //         if (!$service_req) {
    //             $respArr['status_code'] = 202;
    //             $respArr['message'] = 'Request not found.';
    //             $respArr['data'] = null;
    //             return response(json_encode($respArr), 202)->header('Content-Type', 'text/plain');
    //         }

    //         $happy_code_delivered_time = Carbon::now();
    //         $customer = Customers::find($service_req->customer_id);
    //         $send_sms = false; // Flag to track SMS sending

    //         DB::transaction(function () use ($service_req, $req_payloads, $happy_code_delivered_time, $customer) {

    //             // Update or create HappyCodeHistory
    //             HappyCodeHistory::updateOrCreate(
    //                 ['service_requests_id' => $service_req->id],
    //                 [
    //                     'service_requests_status' => $service_req->status,
    //                     'happy_code' => $req_payloads['happyCode'],
    //                     'happy_code_delivered_time' => $happy_code_delivered_time,
    //                 ]
    //             );

    //             if ($service_req->status !== "Closed") {
    //                 // Update service request
    //                 $service_req->update([
    //                     'is_happy_code' => 1,
    //                     'happy_code' => $req_payloads['happyCode'],
    //                     'happy_code_delivered_time' => $happy_code_delivered_time,
    //                 ]);
    //                 $send_sms = true; // Set flag to send SMS after transaction

    //                 // Send SMS notification
    //                 //send_sms_request_acknowledged($customer, $service_req);
    //             }
    //         });
    //         // Send SMS outside transaction to ensure it executes only once
    //         if ($send_sms) {
    //             send_sms_request_acknowledged($customer, $service_req);
    //         }
    //         $respArr['status_code'] = 200;
    //         $respArr['message'] =  $service_req->status === "Closed" ? 'This request is closed.' : 'OTP sent successfully.';
    //         $respArr['data'] = $service_req->happy_code;
    //         return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');

    //     }
    // }



    public function verifyRequestAcknowledgementHappyCode(Request $request){
        $validator = Validator::make($request->all(), [
            'happy_code' => 'required|numeric',
            'request_id' => 'required|numeric',
        ],[
            "happy_code.required"=>"happy code is required",
            "happy_code.numeric"=>"happy code will be integer",
            "request_id.required"=>"Request Id is required",
            "request_id.numeric"=>"Request Id is will be integer",
        ]);

        if($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $request_id = $request->request_id;
            $happy_code = $request->happy_code;
            $user = auth('customer-api')->user();

            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
                    $verify_otp = ServiceRequests::where(['id' => $request_id, 'happy_code' => $happy_code])->first();

                    if(!empty($verify_otp)){
                        $data = ServiceRequests::where('id', $request_id)->first();
                        if($data){
                            if($data->acknowledgement_status == 1){
                                return Response::json([
                                    'status_code'=>202,
                                    'message' => 'This request already acknowledged.',
                                    'data'=> ServiceRequests::where('id', $request_id)->first()
                                ]);
                            }else{
                                $acknowledgement_status_key = 'Yes';
                                $message = "Thank you for your confirmation. The issue has been successfully resolved and closed.";
                                $request_id_key = $data->sfdc_id;

                                if(env("SFDC_ENABLED")){

                                    Log::channel('acknowledgement_sms')->info("Check Acknowledge Request Status");

                                    $SFDCCreateRequest = SFDC::acknowledgeRequestHappyCode($acknowledgement_status_key, $request_id_key);

                                    Log::channel('acknowledgement_sms')->info($SFDCCreateRequest);
                                    Log::channel('acknowledgement_sms')->info("\n === SFDC acknowledge status success"."\n\n");
                                    Log::channel('acknowledgement_sms')->info($SFDCCreateRequest);

                                    ServiceRequests::where('id', $request_id)->update([
                                        'happy_code' => null,
                                        'is_happy_code' => 2,
                                        'acknowledgement_status' => 1,
                                        'acknowledgement_updated_at' => Carbon::now(),
                                        'happy_code_delivered_time' => null,
                                        'acknowledged_by' => 'customer'
                                    ]);
                                }
                                return Response::json([
                                    'status_code'=>200,
                                    'message' => $message,
                                    'data' => ServiceRequests::where('id', $request_id)->first()
                                ]);
                            }
                        }else{
                            $get_data = ServiceRequests::where('id', $request_id)->first();
                            return Response::json([
                                'status_code'=>202,
                                'message' => 'This request can not be acknowledged.',
                                'data' => $get_data
                            ]);
                        }
                    }else{
                        $respArr['status_code'] = 202;
                        $respArr['message'] = 'Invalid Happy Code.';
                        $respArr['data'] = null;
                        return response(json_encode($respArr), 202)->header('Content-Type', 'application/json');
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
}
