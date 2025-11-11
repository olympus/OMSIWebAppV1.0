<?php

namespace App\Http\Controllers\API\V2;
use App\Events\RequestStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\EmployeeTeam;
use App\Models\Feedback;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\NotifyCustomer;
use App\SFDC;
use App\SFDCLog;
use App\StatusTimeline;
use Illuminate\Http\Request;
use Log;


class SFDCController extends Controller
{
    public function updateStatus(Request $request)
    {
        Log::info("\n===SFDC Request===\n".print_r($request->toArray(), TRUE)."\n\n");
        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
            if($user->is_expired == 0){
                $respArr = [];

                $SFDCLog = SFDCLog::create([
                    'request_id' => $request->id,
                    'new_status' => $request->status,
                    'splits' => $request->splits,
                    'employee_code' => $request->employee_code,
                ]);

                $servicerequest = ServiceRequests::where('id',$request->id)->first();

                if(!$servicerequest){
                    $SFDCLog->update(['action'=>'skip','response'=>'Request Not Found']);
                    return response(('Request Not Found'), 200)->header('Content-Type', 'text/plain');
                }
                $SFDCLog->update(['previous_status'=>$servicerequest->status]);

                if($request->status === "Closed_Duplicate"){
                    $SFDCLog->update(['action'=>'delete']);

                    $servicerequest->delete();
                    Feedback::where('request_id',$request->id)->delete();
                    StatusTimeline::where('request_id',$request->id)->delete();
        			$respArr['message'] =  $request->id." deleted";

                    $SFDCLog->update(['response'=>$request->id." deleted"]);
                }else{
                    if(!empty($request->status)){
                        $SFDCLog->update(['action'=>'update_status']);

                        $employee = EmployeeTeam::where('employee_code',$request->employee_code)->first();
                        if(!$employee){
                            $SFDCLog->update(['response'=>'Employee Not Found']);
                            return response(('Employee Not Found'), 200)->header('Content-Type', 'text/plain');
                        }

                        $this->reuestUpdate($request, $servicerequest, $SFDCLog);
                        $SFDCLog->update(['response'=>'status_updated']);
                    }

                    if(!empty($request->splits)){
                        $SFDCLog->update(['action'=>$SFDCLog->action?$SFDCLog->action.';split':'split']);

                        $childRequests = ServiceRequests::where("import_id",$request->id)->count();
                        if($request->splits != $childRequests && $request->splits > $childRequests){
                            if($request->splits < 20 ){
                                $split_ids = $this->splitRequest($servicerequest, ($request->splits - $childRequests));
                                $respArr['splits'] =  implode(";", $split_ids);
        					}
                        }
        				$SFDCLog->update(['response'=>$SFDCLog->response?$SFDCLog->response.';splited':'splited']);
                    }
                }
                $respArr['status_code'] = "200";
                return response(($respArr), 200)->header('Content-Type', 'text/plain');
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

    private function reuestUpdate($request, $servicerequest, $SFDCLog){

        $customer = Customers::findOrFail($servicerequest->customer_id);
        $oldData = $servicerequest;
        $servicerequest->status = $request->status;
        $servicerequest->employee_code = $request->employee_code;
        $servicerequest->last_updated_by = "SFDC_API";
        $servicerequest->save();

        $status = new StatusTimeline;
        $status->status =$servicerequest->status;
        $status->customer_id = $servicerequest->customer_id;
        $status->request_id = $servicerequest->id;
        $status ->save();


        //send_sms('status_update', $customer, $servicerequest, '');
        NotifyCustomer::send_notification('request_update', $servicerequest, $customer);
        event(new RequestStatusUpdated($servicerequest, $customer, $oldData));
    }

    public function splitRequest($servicerequest, $number){
        $split_ids = [];
        for($i = 1; $i <= $number; $i++ ){
            if(!is_null($servicerequest)){
                $newrequest = new ServiceRequests;
                $newrequest->import_id = $servicerequest->id;
                $newrequest->request_type = $servicerequest->request_type;
                $newrequest->sub_type = $servicerequest->sub_type;
                $newrequest->customer_id = $servicerequest->customer_id;
                $newrequest->hospital_id = $servicerequest->hospital_id;
                $newrequest->last_updated_by = "SFDC_API";
                $newrequest->dept_id = $servicerequest->dept_id;
                $newrequest->remarks = $servicerequest->remarks;
                // $newrequest->sfdc_id = $request->sfdc_id;
                // $newrequest->sfdc_customer_id = $request->sfdc_customer_id;
                $newrequest->product_category = $servicerequest->product_category;
                $newrequest->status = "Received";
                // if($data["status"]!= "Received"){
                //  $newrequest->employee_code = $data["fse_code"];
                // }
                $newrequest->save();

                $oldData = $newrequest;

                $status = new StatusTimeline;
                $status->status =$newrequest->status;
                $status->customer_id = $newrequest->customer_id;
                $status->request_id = $newrequest->id;
                $status ->save();

                $newrequestId = $newrequest->id;
                $split_ids[] = $newrequestId;

                $hospitals = Hospitals::find($newrequest->hospital_id);
                $customer = Customers::findOrFail($servicerequest->customer_id);
                // Add Split ParentID
                $SFDCCreateRequest = SFDC::createRequest($newrequest, $customer, $hospitals, $servicerequest->sfdc_id);
                if(isset($SFDCCreateRequest->success)){
                    if($SFDCCreateRequest->success == "true" && isset($SFDCCreateRequest->id)){
                        $newrequest->sfdc_id = $SFDCCreateRequest->id;
                    }else{
                        Log::info("\n===Error SFDC Split Creation===\n".print_r($SFDCCreateRequest, TRUE)."\n\n");
                    }
                }
                $newrequest->save();

                // send_sms('status_update', $customer, $servicerequest, '');
                // NotifyCustomer::send_notification('request_create', $servicerequest, $customer);
                // event(new RequestCreated($servicerequest, $customer, $oldData));
            }
        }
        return $split_ids;
    }

    public function manual_push($id){
        $service = ServiceRequests::find($id);
        $customer = Customers::findOrFail($service->customer_id);
        $hospitals = Hospitals::find($service->hospital_id);

        $SFDCCreateRequest = SFDC::createRequest($service, $customer, $hospitals, "");
        if(isset($SFDCCreateRequest->success)){
            if($SFDCCreateRequest->success == "true" && isset($SFDCCreateRequest->id)){
                $service->sfdc_id = $SFDCCreateRequest->id;
                $service->save();
                return "Success - $service->id - $service->sfdc_id";
            }
        }else{
            Log::info("\n===Error SFDCCreateRequest manual_push==="."\n\n");
            Log::info($SFDCCreateRequest);
            return "Failed - $service->id";
        }
    }
}
