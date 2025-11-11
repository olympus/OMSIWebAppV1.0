<?php

namespace App\Http\Controllers\API\V2;

use App\Models\Departments;
use App\Http\Controllers\Controller;
use App\Models\EmployeeTeam;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\Models\ProductInfo;
use App\RequestReminderHistory;
use App\SFDC;
use App\StatusTimeline;
use App\TechnicalReport;
use Config;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Log;
use Response;
use Validator;


class DuplicateCallController extends Controller
{
    public function getOpenServiceRequestListOld(Request $request){
        //$user = Customers::where('id', 681)->first();
        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
            if($user->is_expired == 0){
                $history = (object)[];

                $ongoingServiceAry = ServiceRequests::where('request_type', 'service')->where('customer_id', $user['id'])->where('status', '!=', 'Closed')->latest()->get();

                $history->ongoingAry =  $ongoingServiceAry;

                foreach ($history->ongoingAry as $key => $value) {
                    $value->hospital_name = Hospitals::where('id', $value->hospital_id)->value('hospital_name');
                    $value->dept_name = Departments::where('dept_id', $value->dept_id)->value('name');

                    $value->escalation_detail = [];
                    $esc_detail1 = [];
                    $esc_count = 0;
                    $service_request_escalation_count = ServiceRequests::where('id', $value->id)->value('escalation_count');
                    if($service_request_escalation_count){
                        $esc_count = ServiceRequests::where('id', $value->id)->value('escalation_count');
                    }

                    $esc_count = ($esc_count > 4) ? 4 : $esc_count ;



                    if ($value->request_type=='service') {
                        if (!empty($value->employee_code) && !is_null($value->employee_code) && $esc_count > 0) {
                            $emp_data = EmployeeTeam::where('employee_code', $value->employee_code)->get()->toArray()[0];
                            for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                                $emp_mail = 'escalation_'.$repeat_1;
                                $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile', 'image', 'designation')->first();
                                if (!is_null($esc_detail1)) {
                                    $esc_detail1->employee_image = config('app.url')."/storage/".$esc_detail1->image;
                                    $esc_detail1->escalation_level = $repeat_1;
                                    $value->escalation_detail = array_merge($value->escalation_detail, array($esc_detail1));
                                }
                            }
                        }
                        if (!empty($value->employee_code)) {
                            $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                            if(!empty($value->fseAry[0]->employee_image)){
                                $value->fseAry[0]->employee_image = config('app.url')."/storage/".$value->fseAry[0]->image;
                            }
                        } else {
                            $value->fseAry = []; // Request Received  , Yet not assigned
                        }

                        $value->product_info = ProductInfo::where('service_requests_id', $value->id)->get();
                        $value->technical_report = TechnicalReport::where('service_requests_id', $value->id)->get();
                    } else {
                        if (!empty($value->employee_code) && !is_null($value->employee_code) && $esc_count > 0) {
                            $emp_data = EmployeeTeam::where('employee_code', $value->employee_code)->get()->toArray()[0];
                            for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                                $emp_mail = 'escalation_'.$repeat_1;
                                $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile', 'image', 'designation')->first();
                                if (!is_null($esc_detail1)) {
                                    $esc_detail1->employee_image = config('app.url')."/storage/".$esc_detail1->image;
                                    $esc_detail1->escalation_level = $repeat_1;
                                    $value->escalation_detail = array_merge($value->escalation_detail, array($esc_detail1));
                                }
                            }
                        }
                        if (!empty($value->employee_code)) {
                            $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                            if(!empty($value->fseAry[0]->employee_image)){
                                $value->fseAry[0]->employee_image = config('app.url')."/storage/".$value->fseAry[0]->image;
                            }
                        } else {
                            $value->fseAry = []; // Request Received  , Yet not assigned
                        }
                    }
                    $request_history = StatusTimeline::where('customer_id', $value->customer_id)->where('request_id', $value->id)->get();
                    $reminder_history = RequestReminderHistory::where('customer_id', $value->customer_id)->where('request_id', $value->id)->select('id','customer_id','request_id','status', 'created_at','updated_at')->get();

                    $mergedData = collect(array_merge($request_history->toArray(), $reminder_history->toArray()));

                    // Sort merged data by `created_at` in descending order
                    $sortedData = $mergedData->sortBy('created_at');
                    $value->timelineAry = $sortedData->values();

                    //$value->timelineAry = StatusTimeline::where('customer_id', $value->customer_id)->where('request_id', $value->id)->get();
                    $value->request_progress = request_progress($value->request_type, $value->status);
                }

                return Response::json(['status_code'=>200,'history'=>$history]);
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

    public function getOpenServiceRequestList(Request $request)
    {
        $user = auth('customer-api')->user();

        if (!$user) {
            return response()->json([
                'status_code' => 400,
                'message' => 'user not found',
            ]);
        }

        if ($user->is_expired) {
            return response()->json([
                'status_code' => 407,
                'message' => 'password expired',
                'is_expired' => $user->is_expired
            ]);
        }

        $history = (object)[];
        $ongoingServiceAry = ServiceRequests::where('request_type', 'service')
            ->where('customer_id', $user->id)
            ->where('status', '!=', 'Closed')
            ->latest()
            ->get();

        $history->ongoingAry = $ongoingServiceAry;

        foreach ($history->ongoingAry as $service) {

            // Hospital & Department
            $service->hospital_name = Hospitals::where('id', $service->hospital_id)->value('hospital_name') ?? '-';
            $service->dept_name = Departments::where('dept_id', $service->dept_id)->value('name') ?? '-';

            // Escalation
            $service->escalation_detail = [];
            $esc_count = min(ServiceRequests::where('id', $service->id)->value('escalation_count') ?? 0, 4);

            if (!empty($service->employee_code) && $esc_count > 0) {
                $emp_data = EmployeeTeam::where('employee_code', $service->employee_code)->first();
                if ($emp_data) {
                    for ($i = 1; $i <= $esc_count; $i++) {
                        $emp_email_field = 'escalation_'.$i;
                        if (!empty($emp_data->$emp_email_field)) {
                            $esc_detail = EmployeeTeam::where('email', $emp_data->$emp_email_field)
                                ->select('name', 'email', 'mobile', 'image', 'designation')
                                ->first();
                            if ($esc_detail) {
                                $esc_detail->employee_image = $esc_detail->image ? config('app.url')."/storage/".$esc_detail->image : null;
                                $esc_detail->escalation_level = $i;
                                $service->escalation_detail[] = $esc_detail;
                            }
                        }
                    }
                }
            }

            // FSE Info
            $service->fseAry = !empty($service->employee_code)
                ? EmployeeTeam::where('employee_code', $service->employee_code)->get()
                : collect();

            if (isset($service->fseAry[0]) && !empty($service->fseAry[0]->image)) {
                $service->fseAry[0]->employee_image = config('app.url')."/storage/".$service->fseAry[0]->image;
            }

            // Product & Technical Info
            $service->product_info = ProductInfo::where('service_requests_id', $service->id)->get();
            $service->technical_report = TechnicalReport::where('service_requests_id', $service->id)->get();

            // Timeline
            $request_history = StatusTimeline::where('customer_id', $service->customer_id)
                ->where('request_id', $service->id)->get();
            $reminder_history = RequestReminderHistory::where('customer_id', $service->customer_id)
                ->where('request_id', $service->id)
                ->select('id','customer_id','request_id','status','created_at','updated_at')
                ->get();

            $mergedData = collect(array_merge($request_history->toArray(), $reminder_history->toArray()));
            $service->timelineAry = $mergedData->sortByDesc('created_at')->values();

            // Request progress
            $service->request_progress = request_progress($service->request_type, $service->status);
        }

        return response()->json(['status_code' => 200, 'history' => $history]);
    }


    public function customerSubmitServiceRequestReminder(Request $request){
        $rules = [
            'request_id' => 'required|numeric|exists:service_requests,id',
            'message' => 'required|string|max:255'
        ];
        $validator = Validator::make( $request->all(), $rules);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->first(), 'status_code' => 203 ]);
        }else{
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
                    $data = ServiceRequests::where('id', $request->request_id)->where('status', '!=', 'Closed')->first();
                    if($data){
                        if($data->reminder_count == 2){
                            return Response::json([
                                'status_code'=>202,
                                'message' => 'Reminder limit exceeded. The maximum number of reminders per request is 2.',
                                'data'=> ServiceRequests::where('id', $request->request_id)->where('status', '!=', 'Closed')->first()
                            ]);

                        }else{
                            $reminder_count = 0;
                            $reminder_count = $data->reminder_count + 1;

                            ServiceRequests::where('id', $request->request_id)->where('status', '!=', 'Closed')->update([
                                'reminder_count' => $reminder_count
                            ]);

                            //start save reminder history data

                                $remind_req_history = new RequestReminderHistory();
                                $remind_req_history->request_id = $data->cvm_id;
                                $remind_req_history->status = "Follow Up";
                                $remind_req_history->customer_id = $data->customer_id;
                                $remind_req_history->previous_count = $data->reminder_count;
                                $remind_req_history->new_count = $reminder_count;
                                $remind_req_history->employee_code = $data->employee_code;
                                $remind_req_history->response = $request->message;
                                $remind_req_history->save();

                            //end save reminder history data


                            //start reminder data pass to SFDC API

                                $request_id_key = $data->sfdc_id;
                                $message = $request->message;

                                if(env("SFDC_ENABLED")){
                                    $SFDCCreateRequest = SFDC::reminderRequest($message, $request_id_key);
                                    if(isset($SFDCCreateRequest->success)){
                                        Log::info("\n===SFDC reminder request status success"."\n\n");
                                        Log::info($SFDCCreateRequest);
                                    }else{
                                        Log::info("\n===Error reminder request status error"."\n\n");
                                        Log::info($SFDCCreateRequest);
                                    }
                                }


                            //start reminder data pass to SFDC API

                            return Response::json([
                                'status_code'=>200,
                                'message' => 'This request is reminded successfully',
                                'data' => ServiceRequests::where('id', $request->request_id)->where('status', '!=', 'Closed')->first()
                            ]);

                        }
                    }else{
                        $get_data = ServiceRequests::where('id', $request->request_id)->first();
                        return Response::json([
                            'status_code'=>202,
                            'message' => 'This request can not be reminded.',
                            'data' => $get_data
                        ]);
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
