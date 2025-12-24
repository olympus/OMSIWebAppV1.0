<?php
namespace App\Http\Controllers\API\V2;
use App\Calender;
use App\CustomerShowPromailer;
use App\Models\Departments;
use App\Http\Controllers\Controller;
use App\Mail\FeedbackCreated;
use App\Mail\RequestCreated;
use App\Mail\RequestEscalated;
use App\Models\ArchiveServiceRequests;
use App\Models\AutoEmails;
use App\Models\Customers;
use App\Models\EmployeeTeam;
use App\Models\Feedback;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\NotifyCustomer;
use App\Models\ProductInfo;
use App\Promailer;
use App\RequestReminderHistory;
use App\SFDC;
use App\StatusTimeline;
use App\TechnicalReport;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use JWTAuth;
use Log;
use Response;
use Spatie\Browsershot\Browsershot;
use Validator;

// use Spatie\Image;
// use Spatie\Image\Manipulations;

class ServiceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $history = (object)[];
        $ongoingServiceAry = ServiceRequests::where('status', '!=', 'Closed')->orderBy('id', 'DESC')->get();
        $closedServiceAry = ServiceRequests::where('status', 'Closed')->orderBy('id', 'DESC')->get();

        $ongoingArchiveServiceAry = ArchiveServiceRequests::where('status', '!=', 'Closed')->orderBy('id', 'DESC')->get();
        $closedArchiveServiceAry = ArchiveServiceRequests::where('status', 'Closed')->orderBy('id', 'DESC')->get();

        $history->ongoingAry = $ongoingServiceAry->merge($ongoingArchiveServiceAry);
        $history->closedAry = $closedServiceAry->merge($closedArchiveServiceAry);
        return Response::json(['status_code'=>200,'history'=>$history]);
    }

    /**
     * Logout API
     *
     * @return \Illuminate\Http\Response
    */
    public function logoutOld(Request $request)
    {
        $customer = Customers::findOrFail($request->customer_id);
        $customer->device_token = null;
        $customer->save();
        return Response::json(['status_code'=>200,'message'=>'Logged out']);
    }

    public function logoutOld1(Request $request)
    {
        $validator = Validator::make($request->only('token','customer_id'), [
            'token' => 'required',
            'customer_id' => 'required',
        ]);

        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
            //if($user->is_expired == 0){
                try {
                    $customer = Customers::findOrFail($request->customer_id)->update([
                        'device_token' => null,
                    ]);
                    JWTAuth::invalidate($request->token);
                    return Response::json(['status_code'=>200,'message'=>'Logged out']);
                } catch (JWTException $exception) {
                    return Response::json(['status_code'=>200,'message'=>'Sorry, user cannot be logged out']);
                }
            // }else{
            //     return Response::json(['status_code'=>407,'message'=>'Your password has been expired.Please reset your password now.','is_expired' => $user->is_expired]);
            // }
        }else {
            return Response::json(['status_code' => 400,'message'=>'user not found']);
        }
    }

    public function logout(Request $request)
    {
        $rules = [
            'token' => 'required',
            'customer_id' => 'required|exists:customers,id'
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);
        }else{
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->id == $request->customer_id){
                    //if($user->is_expired == 0){
                        try {
                            $customer = Customers::findOrFail($request->customer_id)->update([
                                'device_token' => null,
                            ]);
                            JWTAuth::invalidate($request->token);
                            return Response::json(['status_code'=>200,'message'=>'Logged out']);
                        } catch (JWTException $exception) {
                            return Response::json(['status_code'=>200,'message'=>'Sorry, user cannot be logged out']);
                        }
                    // }else{
                    //     return Response::json(['status_code'=>407,'message'=>'Your password has been expired.Please reset your password now.','is_expired' => $user->is_expired]);
                    // }
                }else{
                    return Response::json(['status_code' => 400,'message'=>'customer id is different']);
                }
            }else {
                return Response::json(['status_code' => 400,'message'=>'user not found']);
            }
        }
    }

    public function capture_emails_version_two($id, $type)
    {
        switch ($type) {
            case 'created':
                return view('emails.request_created_new', ['id'=>$id]);
                break;
            case 'updated':
                $oldData_employee_code = Input::get('oldData_employee_code') ;
                return view('emails.request_updated_new', ['id'=>$id, 'oldData_employee_code'=> $oldData_employee_code]);
                break;
            case 'escalated':
                return view('emails.request_escalated_new', ['id'=>$id]);
                break;
            case 'feedback':
                return view('emails.feedback_recvd_new', ['id'=>$id]);
                break;
        }
    }


    public function capture_screenshot_version_two($id, $type)
    {
        $base_url = env('APP_URL').'/capture_emails_version_two/';
        switch ($type){
            case 'created':
                $urlImage = $base_url.$id."/created";
                break;
            case 'updated':
            $oldData_employee_code = Input::get('oldData_employee_code') ;
            $urlImage = $base_url.$id."/updated?oldData_employee_code=".$oldData_employee_code;
                break;
            case 'escalated':
                $urlImage = $base_url.$id."/escalated";
                break;
            case 'feedback':
                $urlImage = $base_url.$id."/feedback";
                break;
        }
        $pathToImage = public_path()."/serviceImages/".$id.".jpg";
        if(file_exists($pathToImage)){
        }
        Browsershot::url($urlImage)
            ->setNodeBinary('/usr/local/bin/node')
            ->device('iPhone X')
            ->fullPage()
            ->setNpmBinary('/usr/local/bin/npm')
            ->setScreenshotType('jpeg', 50)
            ->save($pathToImage);

        // ImageOptimizer::optimize($pathToImage);
        return response(json_encode($pathToImage), 200)->header('Content-Type', 'text/plain');
        // return '<img src=config('app.url')."/serviceImages/'.$id.'.jpg" >';
    }




    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Logger('Service Store Api Request Payload');
        Logger($request->all());
        $rules = [
            'request_type' => 'required|in:enquiry,academic,service|regex:/^[a-zA-Z\s]*$/',
            'customer_id' => 'required|exists:customers,id',
            'hospital_id' => 'required|numeric',
            'dept_id' => 'required|numeric',
            'remarks' => 'required|string',
            //'sub_type' => 'required|in:BreakDown Call,Service Support,Product Info,Demonstration,Quotation,Quotations,Conference,Clinical Info,Training|regex:/^[a-zA-Z\s]*$/',
            'sub_type' => 'required',
        ];

        $messages = [
            'hospital_id.required' => 'hospital field is required',
            'dept_id.required' => 'department field is required',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(), 'status_code' => 203 ]);
        }else{
            $hospitals = Hospitals::find($request->hospital_id);
            if (!isset($hospitals)>0) {
                $respArr['status_code'] = 401;
                $respArr['msg'] = 'The hospital does not exist !';
                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
            }
            $departments = Departments::find($request->dept_id);
            if (!isset($departments)>0) {
                $respArr['status_code'] = 401;
                $respArr['msg'] = 'The department does not exist !';
                return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
            }
            $service = new ServiceRequests;
            $service->request_type = $request->request_type;
            $service->sub_type = $request->sub_type;
            $service->customer_id = $request->customer_id;
            $service->hospital_id = $request->hospital_id;
            $service->dept_id = $request->dept_id;
            $service->remarks = $request->remarks;
            $service->status = 'Received';
            $service->cvm_id = sprintf('%08d', $service->id);
            if ($request->request_type=='enquiry') {
                $service->product_category = rtrim($request->product_category, ',');
            }
            $service->save();


            $status = new StatusTimeline;
            $status->status ='Received';
            $status->customer_id = $service->customer_id;
            $status->request_id = $service->id;
            $status ->save();

            $customer = Customers::findOrFail($service->customer_id);
            $service->cvm_id = sprintf('%08d', $service->id);
            $service->save();

            if(env("SFDC_ENABLED") && $request->request_type == 'service'){
                $SFDCCreateRequest = SFDC::createRequest($service, $customer, $hospitals, "");
                if(isset($SFDCCreateRequest->success)){
                    if($SFDCCreateRequest->success == "true" && isset($SFDCCreateRequest->id)){
                        $service->sfdc_id = $SFDCCreateRequest->id;
                        $service->save();
                    }
                    else{
                        Log::info("\n===Error SFDCCreateRequest new_request"."\n\n");
                        Log::info($SFDCCreateRequest);
                    }
                }else{
                    Log::info("\n===Error SFDCCreateRequest new_request"."\n\n");
                    Log::info($SFDCCreateRequest);
                }
            }


            if (strpos($customer->email, '@olympus.com')) {
                $service->is_practice = true;
                $service->save();
            }
            $respArr['status_code'] = 200;
            $respArr['cvm_id'] = $service->cvm_id;
            $respArr['message_top'] = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.",\n\nWe have received your request with the following details:";

            $working_day_today = Calender::where('date', date('Y-m-d'))->first();
            // If today is working day
            if ((date('H')>0) && (date('H')<5) && (is_null($working_day_today))) {//Midnight to Morning 5AM
                $filter_text_msg = $filter_text = "today";
            } elseif ((date('H')>5) && (date('H')<16) && (is_null($working_day_today))) {// Morning 5AM to 4PM
                $filter_text_msg = $filter_text = "shortly";
            }
            else {
                $followup_day = $this->findNextWorkingDay(); //Find next working day
                $filter_text_msg = $filter_text = "on ".ucfirst(date('l', $followup_day))." (".date('d-m-Y', $followup_day).")";
            }
            $text = ($request->request_type=='service') ? "Our engineer" : "Our executive" ;
            $respArr['message_bottom'] = $text.' will reach out to you '.$filter_text.".\n\nThank you very much.\nOlympus India";
            $servicerequest = ServiceRequests::find($service->id);

            if ($service->request_type=='service') {
                //send_sms('service_notifcation', $customer, $servicerequest, $filter_text_msg);
            } else {
                //send_sms('enquiry_notification', $customer, $servicerequest, $filter_text_msg);
            }
            NotifyCustomer::send_notification('request_create', $servicerequest, $customer);

            if ((!$servicerequest->is_practice)) {
                //if(env("APP_ENV")  == 'staging'){
                    $to_emails = [];
                    $cc_emails = [];
                    if ($servicerequest->request_type=='enquiry') {
                        $product_category_arr = explode(',', $servicerequest->product_category);
                        for ($i=0; $i < sizeof($product_category_arr); $i++) {
                            if (trim($product_category_arr[$i])=='Accessory') {
                                $rules_list = AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'accessory')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                                $to_emails[$i] = explode(',', $rules_list->to_emails);
                                $cc_emails[$i] = explode(',', $rules_list->cc_emails);
                            } elseif (trim($product_category_arr[$i])=='Capital Product') {
                                $rules_list = AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'capital')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                                $to_emails[$i] = explode(',', $rules_list->to_emails);
                                $cc_emails[$i] = explode(',', $rules_list->cc_emails);
                            } elseif (trim($product_category_arr[$i])=='Other') {
                                $rules_list = AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'other')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                                $to_emails[$i] = explode(',', $rules_list->to_emails);
                                $cc_emails[$i] = explode(',', $rules_list->cc_emails);
                            }
                        }
                        $to_emails_final['email'] = collect($to_emails)->flatten()->unique()->toArray();
                        $cc_emails_final['email'] = collect($cc_emails)->flatten()->unique()->toArray();
                    } elseif ($servicerequest->request_type=='others') {
                        $rules_list = AutoEmails::where('request_type', 'academic')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                        $to_emails_final['email'] = explode(',', $rules_list->to_emails);
                        $cc_emails_final['email'] = explode(',', $rules_list->cc_emails);
                    } else {
                        $rules_list = AutoEmails::where('request_type', $servicerequest->request_type)->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                        $to_emails_final['email'] = explode(',', $rules_list->to_emails);
                        $cc_emails_final['email'] = explode(',', $rules_list->cc_emails);
                    }
                    if ($servicerequest->request_type!='service') {
                        $cc_emails_final['email'] = array_merge($cc_emails_final,\Config('oly.enq_acad_coordinator_email'));
                    }
                    $users = collect($to_emails_final['email'])->flatten()->unique()->toArray();
                    $cc = collect($cc_emails_final['email'])->flatten()->unique()->toArray();
                    $users_final = [];
                    foreach ($users as $user) {
                        $users_final[] = ['email' => $user];
                    }

                    $cc_final = [];
                    foreach ($cc as $email) {
                        $cc_final[] = ['email' => $email];
                    }


                    Logger('Service Store Api Success Response Payload');
                    Logger($respArr);
                    // dd($to_emails_final,$users_final,$cc_emails_final,$cc_final);
                    //$pathToImage = json_decode(file_get_contents(env('APP_URL').'/capture_screenshot_version_two/'.$servicerequest->id.'/created'));

                    // $isMailAlreadySent = AssignRequest::where('service_request_id',$servicerequest->id)->first();
                    // if (is_null($isMailAlreadySent)) {
                    //     $assign_request = new AssignRequest;
                    //     $assign_request->service_request_id =$servicerequest->id;
                    //     $assign_request->token = $servicerequest->id.'_'.bin2hex(openssl_random_pseudo_bytes(64));
                    //     $assign_request->expired_at = date('Y-m-d H:i:s', strtotime('+7 days'));
                    //     $assign_request ->save();
                    // }else{
                    //     if(new \DateTime() > new \DateTime($isMailAlreadySent->expired_at)){
                    //         $old_expired_at = $isMailAlreadySent->expired_at;
                    //         $new_expired_at = date('Y-m-d H:i:s', strtotime('+7 days'));
                    //         AssignRequest::where('service_request_id',$servicerequest->id)->update(['expired_at'=> $new_expired_at]);
                    //         echo "Link Expired at ".$old_expired_at."<br><br>New expired_at ".$new_expired_at."<br><br>";
                    //     }
                    // }
                    // $assign_request = AssignRequest::where('service_request_id',$servicerequest->id)->first();

                    // file_get_contents(asset('/exports/'.$servicerequest->id));
                    // dd(storage_path().'/exports/ServiceRequests-'.$servicerequest->id.'.xls');
                //}
                $assign_request = "";
                //code comment on 13 dec
                //$users_final = 'sandeep.gupta@lyxellabs.com';
                //$cc_final = 'sandeep.gupta@lyxellabs.com';
                // Mail::to($users_final)->cc($cc_final)
                // ->send(new RequestCreated($pathToImage, $servicerequest, $customer, $assign_request));

                if(env('APP_ENV') != "staging"){
                    Mail::to($users_final)->cc($cc_final)
                    ->send(new RequestCreated($service->id, $servicerequest, $customer, $assign_request));
                }
                // // Mail::to($users_final)->cc($cc_final)
                // ->send(new AssignRequest($servicerequest, $customer));
            }
            return response(($respArr), 200)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showOld($id)
    {

        $service_request = ServiceRequests::where('id', $id)->first();
        if($service_request){
            $history = ServiceRequests::where('id', $id)->first();
        }

        $archive_service_request = ArchiveServiceRequests::where('id', $id)->first();
        if($archive_service_request){
            $history = ArchiveServiceRequests::where('id', $id)->first();
        }

        $history->hospital_name = Hospitals::where('id', $history->hospital_id)->value('hospital_name');
        $history->dept_name = Departments::where('dept_id', $history->dept_id)->value('name');

        $history->fseAry = EmployeeTeam::where('employee_code', $history->employee_code)->first();
        $history->fseAry->employee_image = (!is_null($history->fseAry) ? config('app.url')."/storage/".$history->fseAry->image : config('app.url')."/storage/shared/employee_image.jpg");

        $history->escalation_detail = [];
        $esc_detail1 = [];
        $esc_count = 0;
        $service_request_escalation_count = ServiceRequests::where('id', $history->id)->value('escalation_count');
        if($service_request_escalation_count){
            $esc_count = ServiceRequests::where('id', $history->id)->value('escalation_count');
        }

        $archive_service_request_escalation_count = ArchiveServiceRequests::where('id', $history->id)->value('escalation_count');
        if($archive_service_request_escalation_count){
            $esc_count = ArchiveServiceRequests::where('id', $history->id)->value('escalation_count');
        }

        //$esc_count = ServiceRequests::where('id', $history->id)->value('escalation_count');
        $esc_count = ($esc_count > 4) ? 4 : $esc_count ;

        if (!empty($history->employee_code) && !is_null($history->employee_code) && $esc_count > 0) {
            $emp_data = EmployeeTeam::where('employee_code', $history->employee_code)->get()->toArray()[0];
            for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                $emp_mail = 'escalation_'.$repeat_1;
                $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile', 'image')->first();
                if (!is_null($esc_detail1)) {
                    $esc_detail1->employee_image = config('app.url')."/storage/".$esc_detail1->image;
                    $esc_detail1->escalation_level = $repeat_1;
                    $history->escalation_detail = array_merge($history->escalation_detail, array($esc_detail1));
                }
            }
        }

        $history->timelineAry = StatusTimeline::where('customer_id', $history->customer_id)->where('request_id', $history->id)->get();

        $history->product_info = ProductInfo::where('service_requests_id', $id)->get();
        $history->technical_report = TechnicalReport::where('service_requests_id', $id)->get();
        $history->request_progress = request_progress($history->request_type, $history->status);
        return Response::json(['status_code'=>200,'data'=>$history]);
    }

    public function show($id)
    {

        $service_request = ServiceRequests::where('id', $id)->first();
        if($service_request){
            $history = ServiceRequests::where('id', $id)->first();
        }

        $archive_service_request = ArchiveServiceRequests::where('id', $id)->first();
        if($archive_service_request){
            $history = ArchiveServiceRequests::where('id', $id)->first();
        }

        $history->hospital_name = Hospitals::where('id', $history->hospital_id)->value('hospital_name');
        $history->dept_name = Departments::where('dept_id', $history->dept_id)->value('name');

        $history->fseAry = EmployeeTeam::where('employee_code', $history->employee_code)->first();
        if(!empty($history->fseAry)){
            $history->fseAry->employee_image = (!is_null($history->fseAry) ? config('app.url')."/storage/".$history->fseAry->image : config('app.url')."/storage/shared/employee_image.jpg");
        }
        $history->escalation_detail = [];
        $esc_detail1 = [];
        $esc_count = 0;
        $service_request_escalation_count = ServiceRequests::where('id', $history->id)->value('escalation_count');
        if($service_request_escalation_count){
            $esc_count = ServiceRequests::where('id', $history->id)->value('escalation_count');
        }

        $archive_service_request_escalation_count = ArchiveServiceRequests::where('id', $history->id)->value('escalation_count');
        if($archive_service_request_escalation_count){
            $esc_count = ArchiveServiceRequests::where('id', $history->id)->value('escalation_count');
        }

        //$esc_count = ServiceRequests::where('id', $history->id)->value('escalation_count');
        $esc_count = ($esc_count > 4) ? 4 : $esc_count ;

        if (!empty($history->employee_code) && !is_null($history->employee_code) && $esc_count > 0) {
            $emp_data = EmployeeTeam::where('employee_code', $history->employee_code)->get()->toArray()[0];
            for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                $emp_mail = 'escalation_'.$repeat_1;
                $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile', 'image')->first();
                if (!is_null($esc_detail1)) {
                    $esc_detail1->employee_image = config('app.url')."/storage/".$esc_detail1->image;
                    $esc_detail1->escalation_level = $repeat_1;
                    $history->escalation_detail = array_merge($history->escalation_detail, array($esc_detail1));
                }
            }
        }

        //$history->timelineAry = StatusTimeline::where('customer_id', $history->customer_id)->where('request_id', $history->id)->get();
        $request_history = StatusTimeline::where('customer_id', $history->customer_id)->where('request_id', $history->id)->get();
        $reminder_history = RequestReminderHistory::where('customer_id', $history->customer_id)->where('request_id', $history->id)->select('id','customer_id','request_id','status', 'created_at','updated_at')->get();

        $mergedData = collect(array_merge($request_history->toArray(), $reminder_history->toArray()));

        // Sort merged data by `created_at` in descending order
        $sortedData = $mergedData->sortBy('created_at');
        $history->timelineAry = $sortedData->values();


        $history->product_info = ProductInfo::where('service_requests_id', $id)->get();
        $history->technical_report = TechnicalReport::where('service_requests_id', $id)->get();
        $history->request_progress = request_progress($history->request_type, $history->status);
        return Response::json(['status_code'=>200,'data'=>$history]);
    }

    public function history_count($id, Request $request)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|numeric',
          ]
        );

        if ($validator->fails()) {
            return Response::json(['status_code'=>200,'data'=>$validator->messages()->first()]);
        }
        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
            if($user->is_expired == 0){
                $customers_id = $user->id;
                $history = (object)[];

                $ongoingCountService= count(ServiceRequests::where('request_type', 'service')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                $ongoingCountArchiveService= count(ArchiveServiceRequests::where('request_type', 'service')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                $history->ongoingCountService = $ongoingCountService + $ongoingCountArchiveService;

                $ongoingCountAcademic= count(ServiceRequests::where('request_type', 'academic')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                $ongoingCountArchiveAcademic= count(ArchiveServiceRequests::where('request_type', 'academic')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                $history->ongoingCountAcademic = $ongoingCountAcademic + $ongoingCountArchiveAcademic;

                $ongoingCountEnquiry= count(ServiceRequests::where('request_type', 'enquiry')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                $ongoingCountArchiveEnquiry= count(ArchiveServiceRequests::where('request_type', 'enquiry')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                $history->ongoingCountEnquiry = $ongoingCountEnquiry + $ongoingCountArchiveEnquiry;


                // $history->ongoingCountService= count(ServiceRequests::where('request_type', 'service')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                // $history->ongoingCountAcademic= count(ServiceRequests::where('request_type', 'academic')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                // $history->ongoingCountEnquiry= count(ServiceRequests::where('request_type', 'enquiry')->where('status', '!=', 'Closed')->where('customer_id', $id)->get());
                $history->closedCountAry = (object)[];

                $closedCountServiceAryCount  = count(ServiceRequests::where('customer_id', $id)->where('status', 'Closed')->where('feedback_id', null)->get());
                $closedCountArchiveServiceAryCount  = count(ArchiveServiceRequests::where('customer_id', $id)->where('status', 'Closed')->where('feedback_id', null)->get());

                $history->closedCountAry->count = $closedCountServiceAryCount + $closedCountArchiveServiceAryCount;

                $closedCountServiceAryData = ServiceRequests::where('customer_id', $id)->where('status', 'Closed')->where('feedback_id', null)->select('cvm_id', 'request_type', 'sub_type', 'id', 'remarks', 'created_at', 'employee_code')->get();
                $closedCountArchiveServiceAryData = ArchiveServiceRequests::where('customer_id', $id)->where('status', 'Closed')->where('feedback_id', null)->select('cvm_id', 'request_type', 'sub_type', 'id', 'remarks', 'created_at', 'employee_code')->get();

                $history->closedCountAry->data =  $closedCountServiceAryData->merge($closedCountArchiveServiceAryData);

                $count_promailers = Promailer::where('status', 1)->get();
                $show_promailer = CustomerShowPromailer::where('customers_id', $customers_id)->get();
                if(!empty($show_promailer) || count($show_promailer) > 0){
                    $history->inboxCount = count($count_promailers) - count($show_promailer);
                }else{
                    $history->inboxCount = count($count_promailers);
                }

                $history->app_info = array(
                    'ios'=>\Config('oly.current_version_iOS'),
                    'android'=>\Config('oly.current_version_android'),
                    'message'=>"Dear customer!

Post-repair delivery acknowledgement is now available! Update your app to access this feature and stay on top of your requests effortlessly.

Update now!."
                    //'message'=>'New app update is available. Please update to latest version to app'
                );
                $history->inboxIds = Promailer::where('id', '>', 0)->where('status', 1)->pluck('id')->toArray();

                foreach ($history->closedCountAry->data as $request1) {
                    $has_assigned_person = (is_null($request1->employee_code)) ? false : true;
                    if (!empty($has_assigned_person)) {
                        $assigned_person = EmployeeTeam::getEmployee($request1->employee_code);

                        $request1->employee_name = $assigned_person->name;
                        $request1->assigned_image = (!is_null($assigned_person->image)) ? config('app.url')."/storage/".$assigned_person->image : config('app.url')."/storage/shared/employee_image.jpg" ;
                    } else {
                        $request1->assigned_image = config('app.url')."/storage/shared/employee_image.jpg";
                        $request1->employee_name = "Employee -";
                    }
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

    public function get_request_history_old_one(Request $request)
    {
        $rules = [
            'request_type' => 'regex:/^[a-zA-Z\s]*$/',
            'customer_id' => 'required|exists:customers,id'
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(), 'status_code' => 203 ]);
        }else{
            //Change for academic and enquiry
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
                    $history = (object)[];
                    $new_statuses = array('Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched');
                    if ($request->request_type == 'service') {
                        $ongoingServiceAry = ServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', '!=', 'Closed')->latest()->get();
                        $ongoingArchiveServiceAry = ArchiveServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', '!=', 'Closed')->latest()->get();

                        $history->ongoingAry =  $ongoingServiceAry->merge($ongoingArchiveServiceAry);
                    }else {
                        $ongoingServiceAry = ServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', '=', 'Received')->latest()->get();
                        $ongoingArchiveServiceAry = ArchiveServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', '=', 'Received')->latest()->get();

                        $history->ongoingAry =  $ongoingServiceAry->merge($ongoingArchiveServiceAry);
                    }
                    //dd($history->ongoingAry);
                    foreach ($history->ongoingAry as $key => $value) {
                        if (in_array($value->status, $new_statuses)) {
                            $value->status = 'Assigned';
                        }
                        $value->escalation_detail = [];
                        $esc_detail = [];
                        $esc_count = 0;
                        //$esc_count = ServiceRequests::where('id', $value->id)->value('escalation_count');
                        $service_request_escalation_count = ServiceRequests::where('id', $value->id)->value('escalation_count');
                        if($service_request_escalation_count){
                            $esc_count = ServiceRequests::where('id', $value->id)->value('escalation_count');
                        }

                        $archive_service_request_escalation_count = ArchiveServiceRequests::where('id', $value->id)->value('escalation_count');
                        if($archive_service_request_escalation_count){
                            $esc_count = ArchiveServiceRequests::where('id', $value->id)->value('escalation_count');
                        }

                        $esc_count = ($esc_count > 4) ? 4 : $esc_count ;
                        $value->escalation_detail = [];
                        if ($request->request_type=='service') {
                            $hospital = Hospitals::find($value->hospital_id);
                            $value->hospital_name = $hospital->name;
                            if (!empty($value->employee_code) && !is_null($value->employee_code) && $esc_count > 0) {
                                $emp_data = EmployeeTeam::where('employee_code', $value->employee_code)->get()->toArray()[0];
                                for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                                    $emp_mail = 'escalation_'.$repeat_1;
                                    $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile')->get();
                                    foreach ($esc_detail1 as $arr) {
                                        $arr->escalation_level = $repeat_1;
                                    }
                                    $esc_detail = $esc_detail1->toArray();
                                    if (!empty($esc_detail)) {
                                        $value->escalation_detail = array_merge($value->escalation_detail, $esc_detail);
                                    }
                                }
                            }

                            if (!empty($value->employee_code)) {
                                $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                            } else {
                                $value->fseAry = []; // Request Received  , Yet not assigned
                            }
                        } else {
                            if (!empty($value->assigned_to) && !is_null($value->assigned_to) && $esc_count > 0) {
                                $emp_data = EmployeeTeam::where('employee_code', $value->assigned_to)->get()->toArray()[0];
                                for ($repeat=1; $repeat <= $esc_count; $repeat++) {
                                    $emp_mail = 'escalation_'.$repeat;
                                    $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile')->get();
                                    foreach ($esc_detail1 as $arr) {
                                        $arr->escalation_level = $repeat;
                                    }
                                    $esc_detail = $esc_detail1->toArray();
                                    if (!empty($esc_detail)) {
                                        $value->escalation_detail = array_merge($value->escalation_detail, $esc_detail);
                                    }
                                }
                            }
                            if (!empty($value->employee_code)) {
                                $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                            } else {
                                $value->fseAry = []; // Request Received  , Yet not assigned
                            }
                        }
                        $value->timelineAry = StatusTimeline::where('customer_id', $value->customer_id)->where('request_id', $value->id)->whereNotIn('status', $new_statuses)->get();
                    }

                    if ($request->request_type=='service') {
                        // $history->closedAry= ServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', 'Closed')->latest()->get();

                        $closedServiceAry = ServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', 'Closed')->latest()->get();
                        $closedArchiveServiceAry = ArchiveServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', 'Closed')->latest()->get();

                        $history->closedAry =  $closedServiceAry->merge($closedArchiveServiceAry);

                    } else {
                        $closedServiceAry = ServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', '!=', 'Received')->latest()->get();
                        $closedArchiveServiceAry = ArchiveServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', '!=', 'Received')->latest()->get();

                        $history->closedAry =  $closedServiceAry->merge($closedArchiveServiceAry);

                        // $history->closedAry= ServiceRequests::where('request_type', $request->request_type)->where('customer_id', $request->customer_id)->where('status', '!=', 'Received')->latest()->get();
                    }
                    foreach ($history->closedAry as $key => $value) {
                        if (in_array($value->status, $new_statuses)) {
                            $value->status = 'Assigned';
                        }
                        $value->escalation_detail = [];
                        $esc_detail = [];
                        $esc_count = 0;
                        $service_request_escalation_count = ServiceRequests::where('id', $value->id)->value('escalation_count');
                        if($service_request_escalation_count){
                            $esc_count = ServiceRequests::where('id', $value->id)->value('escalation_count');
                        }

                        $archive_service_request_escalation_count = ArchiveServiceRequests::where('id', $value->id)->value('escalation_count');
                        if($archive_service_request_escalation_count){
                            $esc_count = ArchiveServiceRequests::where('id', $value->id)->value('escalation_count');
                        }

                        $esc_count = ($esc_count > 4) ? 4 : $esc_count ;
                        $value->escalation_detail = [];

                        if (!is_null($value->feedback_id)) {
                            $value->feedback =  Feedback::where('request_id', $value->id)->get();
                        } else {
                            $value->feedback = [];
                        }
                        if ($request->request_type=='service') {
                            if (!empty($value->employee_code) && !is_null($value->employee_code) && $esc_count > 0) {
                                $emp_data = EmployeeTeam::where('employee_code', $value->employee_code)->get()->toArray()[0];
                                for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                                    $emp_mail = 'escalation_'.$repeat_1;
                                    $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile')->get();
                                    foreach ($esc_detail1 as $arr) {
                                        $arr->escalation_level = $repeat_1;
                                    }
                                    $esc_detail = $esc_detail1->toArray();
                                    if (!empty($esc_detail)) {
                                        $value->escalation_detail = array_merge($value->escalation_detail, $esc_detail);
                                    }
                                }
                            }

                            if (!empty($value->employee_code)) {
                                $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                            } else {
                                $value->fseAry = []; // Request Received  , Yet not assigned
                            }
                        } else {
                            if (!empty($value->assigned_to) && !is_null($value->assigned_to) && $esc_count > 0) {
                                $emp_data = EmployeeTeam::where('employee_code', $value->assigned_to)->get()->toArray()[0];
                                for ($repeat=1; $repeat <= $esc_count; $repeat++) {
                                    $emp_mail = 'escalation_'.$repeat;
                                    $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile')->get();
                                    foreach ($esc_detail1 as $arr) {
                                        $arr->escalation_level = $repeat;
                                    }
                                    $esc_detail = $esc_detail1->toArray();
                                    if (!empty($esc_detail)) {
                                        $value->escalation_detail = array_merge($value->escalation_detail, $esc_detail);
                                    }
                                }
                            }

                            if (!empty($value->employee_code)) {
                                $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                            } else {
                                $value->fseAry = []; // Request Received  , Yet not assigned
                            }
                        }
                        $value->timelineAry = StatusTimeline::where('customer_id', $value->customer_id)->where('request_id', $value->id)->get();
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
    }

    public function get_request_history(Request $request)
    {
        $rules = [
            'request_type' => 'regex:/^[a-zA-Z\s]*$/',
            'customer_id' => 'required|exists:customers,id'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'status_code' => 203]);
        }

        $user = auth('customer-api')->user();
        if (!$user) {
            return response()->json(['status_code' => 400, 'message' => 'user not found']);
        }

        if ($user->is_expired) {
            return response()->json([
                'status_code' => 407,
                'message' => 'password expired',
                'is_expired' => $user->is_expired
            ]);
        }

        $history = (object)[];
        $new_statuses = ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'];

        // Fetch ongoing requests
        if ($request->request_type == 'service') {
            $ongoing = ServiceRequests::where('request_type', $request->request_type)
                ->where('customer_id', $request->customer_id)
                ->where('status', '!=', 'Closed')
                ->latest()->get();
            $ongoingArchive = ArchiveServiceRequests::where('request_type', $request->request_type)
                ->where('customer_id', $request->customer_id)
                ->where('status', '!=', 'Closed')
                ->latest()->get();
        } else {
            $ongoing = ServiceRequests::where('request_type', $request->request_type)
                ->where('customer_id', $request->customer_id)
                ->where('status', 'Received')
                ->latest()->get();
            $ongoingArchive = ArchiveServiceRequests::where('request_type', $request->request_type)
                ->where('customer_id', $request->customer_id)
                ->where('status', 'Received')
                ->latest()->get();
        }

        $history->ongoingAry = $ongoing->merge($ongoingArchive);

        // Process each request
        foreach ($history->ongoingAry as $service) {
            if (in_array($service->status, $new_statuses)) {
                $service->status = 'Assigned';
            }

            // Hospital & Department
            $service->hospital_name = Hospitals::find($service->hospital_id)?->name ?? '-';

            // Escalation
            $esc_count = min(
                ServiceRequests::where('id', $service->id)->value('escalation_count') ?? 0,
                4
            );
            $service->escalation_detail = [];

            $employee_code = $request->request_type == 'service' ? $service->employee_code : $service->assigned_to;

            if (!empty($employee_code) && $esc_count > 0) {
                $emp_data = EmployeeTeam::where('employee_code', $employee_code)->first();
                if ($emp_data) {
                    for ($i = 1; $i <= $esc_count; $i++) {
                        $email_field = 'escalation_'.$i;
                        if (!empty($emp_data->$email_field)) {
                            $esc_detail = EmployeeTeam::where('email', $emp_data->$email_field)
                                ->select('name','email','mobile','image')
                                ->first();
                            if ($esc_detail) {
                                $esc_detail->escalation_level = $i;
                                $esc_detail->employee_image = $esc_detail->image ? config('app.url')."/storage/".$esc_detail->image : null;
                                $service->escalation_detail[] = $esc_detail;
                            }
                        }
                    }
                }
            }

            // FSE info
            $service->fseAry = !empty($service->employee_code)
                ? EmployeeTeam::where('employee_code', $service->employee_code)->get()
                : collect();

            if (isset($service->fseAry[0]) && !empty($service->fseAry[0]->image)) {
                $service->fseAry[0]->employee_image = config('app.url')."/storage/".$service->fseAry[0]->image;
            }

            // Feedback
            $service->feedback = !is_null($service->feedback_id)
                ? Feedback::where('request_id', $service->id)->get()
                : collect();

            // Timeline
            $service->timelineAry = StatusTimeline::where('customer_id', $service->customer_id)
                ->where('request_id', $service->id)
                ->whereNotIn('status', $new_statuses)
                ->get();
        }

        // Fetch closed requests
        if ($request->request_type == 'service') {
            $closed = ServiceRequests::where('request_type', $request->request_type)
                ->where('customer_id', $request->customer_id)
                ->where('status', 'Closed')->latest()->get();
            $closedArchive = ArchiveServiceRequests::where('request_type', $request->request_type)
                ->where('customer_id', $request->customer_id)
                ->where('status', 'Closed')->latest()->get();
        } else {
            $closed = ServiceRequests::where('request_type', $request->request_type)
                ->where('customer_id', $request->customer_id)
                ->where('status', '!=', 'Received')->latest()->get();
            $closedArchive = ArchiveServiceRequests::where('request_type', $request->request_type)
                ->where('customer_id', $request->customer_id)
                ->where('status', '!=', 'Received')->latest()->get();
        }

        $history->closedAry = $closed->merge($closedArchive);

        foreach ($history->closedAry as $service) {
            if (in_array($service->status, $new_statuses)) {
                $service->status = 'Assigned';
            }

            $service->escalation_detail = [];
            $esc_count = min(
                ServiceRequests::where('id', $service->id)->value('escalation_count') ?? 0,
                4
            );

            $employee_code = $request->request_type == 'service' ? $service->employee_code : $service->assigned_to;

            if (!empty($employee_code) && $esc_count > 0) {
                $emp_data = EmployeeTeam::where('employee_code', $employee_code)->first();
                if ($emp_data) {
                    for ($i = 1; $i <= $esc_count; $i++) {
                        $email_field = 'escalation_'.$i;
                        if (!empty($emp_data->$email_field)) {
                            $esc_detail = EmployeeTeam::where('email', $emp_data->$email_field)
                                ->select('name','email','mobile','image')
                                ->first();
                            if ($esc_detail) {
                                $esc_detail->escalation_level = $i;
                                $esc_detail->employee_image = $esc_detail->image ? config('app.url')."/storage/".$esc_detail->image : null;
                                $service->escalation_detail[] = $esc_detail;
                            }
                        }
                    }
                }
            }

            $service->fseAry = !empty($service->employee_code)
                ? EmployeeTeam::where('employee_code', $service->employee_code)->get()
                : collect();

            if (isset($service->fseAry[0]) && !empty($service->fseAry[0]->image)) {
                $service->fseAry[0]->employee_image = config('app.url')."/storage/".$service->fseAry[0]->image;
            }

            $service->feedback = !is_null($service->feedback_id)
                ? Feedback::where('request_id', $service->id)->get()
                : collect();

            $service->timelineAry = StatusTimeline::where('customer_id', $service->customer_id)
                ->where('request_id', $service->id)
                ->get();
        }

        return response()->json(['status_code' => 200, 'history' => $history]);
    }


    /**
     * Escalate the specified request ID.
     *
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function escalate(Request $request)
    {
        $new_reason = $request->reasons;
        $reasons = json_decode($request->reasons);
        $request->merge(['reasons' => $reasons]);
        if(!is_array($request->reasons)){
            return response()->json(['message' =>  'reason must be an array', 'status_code' => 203 ]);
        }
        $rules = [
            'request_id' => 'required|numeric',
            'reasons' => 'required|array',
            'remarks' => 'required|string',
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {

            return response()->json(['message' => $validator->errors()->first(), 'status_code' => 203 ]);
        }else{
            $user = auth('customer-api')->user();

            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
                    $chk_service_request = ServiceRequests::findOrFail($request->request_id);
                    // if($service_request){
                    //     $servicerequest = ServiceRequests::findOrFail($request->request_id);
                    // }

                    // $archive_service_request = ArchiveServiceRequests::findOrFail($request->request_id);
                    // if($archive_service_request){
                    //     $servicerequest = ArchiveServiceRequests::findOrFail($request->request_id);
                    // }

                    if(!empty($chk_service_request)){
                        $servicerequest = ServiceRequests::findOrFail($request->request_id);
                    }else{
                        $servicerequest = ArchiveServiceRequests::findOrFail($request->request_id);
                    }

                    //$servicerequest = ServiceRequests::findOrFail($request->request_id);
                    if (isset($request->reasons)) {
                        $reasons = implode(',', json_decode($new_reason, true));
                        $servicerequest->escalation_count = ($servicerequest->escalation_count > 3) ? 4 : $servicerequest->escalation_count+1 ;
                        // $servicerequest->escalation_count = 1;
                        $servicerequest->escalation_reasons = $reasons;
                        $servicerequest->is_escalated = 1;
                        $servicerequest->escalation_remarks = $request->remarks;
                        $servicerequest->save();

                        if(env("SFDC_ENABLED") && $servicerequest->request_type == 'service'){
                            $SFDCCreateEscalation = SFDC::createEscalation($servicerequest);
                            // if($SFDCCreateEscalation->success == "true" && isset($SFDCCreateEscalation->id)){
                                // $service->sfdc_id = $SFDCCreateEscalation->id;
                            // }
                        }

                        //if(env("APP_ENV")  == 'staging'){
                            $to_emails = [];
                            $cc_emails = [];
                            $final_to_list = [];
                            $final_cc_list = [];
                            $escalation_count = $servicerequest->escalation_count;

                            if (!empty($servicerequest->employee_code)) {
                                $assigned_employee = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('email');
                                array_push($cc_emails,$assigned_employee);
                                $employee_data = EmployeeTeam::select('email','escalation_1','escalation_2','escalation_3','escalation_4')->where('employee_code', $servicerequest->employee_code)->first()->toArray();
                            }else{
                                $employee_data = []; // Request is received status
                            }
                            if ($servicerequest->request_type == 'service') {
                                $cc_emails=array_merge($cc_emails,explode(",",\Config('oly.service_coordinator_email')));
                                if ($escalation_count == 3) {
                                    array_push($cc_emails,\Config('oly.service_level_3_esc'));
                                    $level_3_emails = \Config('oly.service_level_3_esc');
                                } elseif ($escalation_count > 3) {
                                    array_push($cc_emails,\Config('oly.service_level_3_esc'),\Config('oly.service_level_4_esc'));
                                }
                            } else {
                                $cc_emails=array_merge($cc_emails,\Config('oly.enq_acad_coordinator_email'));
                                if ($servicerequest->request_type == 'enquiry') {
                                    if ($escalation_count == 3) {
                                        array_push($cc_emails,\Config('oly.enq_acad_level_3_esc'));
                                    } elseif ($escalation_count > 3) {
                                        array_push($cc_emails,\Config('oly.enq_acad_level_3_esc'));
                                    }
                                } elseif ($servicerequest->request_type == 'academic') {
                                    if ($escalation_count > 3) {
                                        array_push($cc_emails,\Config('oly.service_level_4_esc'));
                                    }
                                }
                            }
                        //}

                        $hospital_state = Hospitals::where('id', $servicerequest->hospital_id)->value('state');
                        $dept_name = Departments::where('id', $servicerequest->dept_id)->value('name');

                        //if(env("APP_ENV")  == 'staging'){
                            if ($servicerequest->request_type != 'enquiry') {
                                $subtype = "";
                                $emails = AutoEmails::where("request_type",$servicerequest->request_type)
                                    ->where("states","like","%$hospital_state%")
                                    ->where("departments","like","%$dept_name%")
                                    ->first();
                            } else {
                                $subtype = get_enq_type(explode(',', $servicerequest->product_category)[0]);
                                $emails = AutoEmails::where("request_type",$servicerequest->request_type)
                                    ->where("sub_type","$subtype")
                                    ->where("states","like","%$hospital_state%")
                                    ->where("departments","like","%$dept_name%")
                                    ->first();
                            }
                            $cc_emails=array_merge($cc_emails,explode(",",$emails['to_emails']));
                            $cc_emails=array_merge($cc_emails,explode(",",$emails['cc_emails']));
                            $cc_emails = array_merge($cc_emails, \Config('oly.escalation_cc'));

                            if ($servicerequest->request_type == 'service') {
                                $to_emails = AutoEmails::where("request_type","service")
                                    ->where("states","like","%$hospital_state%")
                                    ->where("departments","like","%$dept_name%")
                                    ->value("escalation_".$escalation_count);
                            }else{
                                $to_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value("escalation_".$escalation_count);
                            }
                            if (empty($to_emails)) {
                                switch ($escalation_count) {
                                    case '1': $to_emails = $emails['to_emails'];break;
                                    case '2': $to_emails = $emails['cc_emails'];break;
                                    case '3': $to_emails = \Config('oly.service_level_3_esc');break;
                                    case '4': $to_emails = \Config('oly.service_level_4_esc');break;
                                }
                            }
                            $to_emails = explode(",", $to_emails);
                        //}

                        // $hospital_region = find_region($hospital_state[0]['state']);
                        // switch ($hospital_region) {
                        //     case 'north': $cc_list[]=\Config('oly.workshopmanagers_north');break;
                        //     case 'east': $cc_list[]=\Config('oly.workshopmanagers_east');break;
                        //     case 'south': $cc_list[]=\Config('oly.workshopmanagers_south');break;
                        //     case 'west': $cc_list[]=\Config('oly.workshopmanagers_west');break;
                        //     default: break;
                        // }
                        // $final_to_list = ['email'=>$to_emails];
                        //if(env("APP_ENV")  == 'staging'){
                            foreach (array_filter(array_unique($to_emails)) as $values) {
                                array_push($final_to_list, array('email'=>$values));
                            }
                            if (!empty($servicerequest->employee_code)) {
                                foreach (array_filter(array_unique($cc_emails)) as $values) {
                                    array_push($final_cc_list, array('email'=>$values));
                                }
                            }
                            if ($servicerequest->escalation_count < 5) {
                                eval('$servicerequest->escalation_assign'.$servicerequest->escalation_count .' = $to_emails[0];');
                            }
                        //}
                        $servicerequest->save();
                        // dd(
                        //     "subtype: ".$subtype,
                        //     "Request Details: ".$servicerequest->id." > ".$servicerequest->request_type." > ".$servicerequest->status,
                        //     "Escalation count: ".$servicerequest->escalation_count,
                        //     "Assigned Employee Data: ",$employee_data,
                        //     // "DD ToEmails: ",$to_dd,
                        //     "To Emails: ",$to_emails,
                        //     "CC Emails: ",$cc_emails,
                        //     $servicerequest->toArray()
                        // );
                        $status = new StatusTimeline;
                        $status->status ='Escalated';
                        $status->customer_id = $servicerequest->customer_id;
                        $status->request_id = $servicerequest->id;
                        $status ->save();

                        $customer = Customers::findOrFail($servicerequest->customer_id);

                        $respArr['status_code'] = 200;
                        $respArr['cvm_id'] = $servicerequest->cvm_id;
                        $respArr['data'] = $servicerequest;

                        //send_sms('request_escalated', $customer, $servicerequest, "");
                        NotifyCustomer::send_notification('request_escalate', $servicerequest, $customer);

                        // $pathToImage = json_decode(file_get_contents(env('APP_URL').'/capture_screenshot_version_two/'.$servicerequest->id.'/escalated'));

                        // Mail::to($final_to_list)->cc($final_cc_list)
                        // ->send(new RequestEscalated($pathToImage, $servicerequest, $customer));

                        //code comment on 13 dec
                        //$final_to_list = 'sandeep.gupta@lyxellabs.com';
                        //$final_cc_list = 'sandeep.gupta@lyxellabs.com';
                        if(env('APP_ENV') != "staging"){
                            Mail::to($final_to_list)->cc($final_cc_list)
                            ->send(new RequestEscalated($request->request_id, $servicerequest, $customer));
                        }

                        return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
                    } else {
                        return Response::json(['status_code'=>400,'message'=>'No reasons for escalation selected. Please select at least one reason for escalation','data'=>'']);
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


    public function escalate1(Request $request)
    {
        //\Log::info($request->all());
        $service_request = ServiceRequests::findOrFail($request->request_id);
        if($service_request){
            $servicerequest = ServiceRequests::findOrFail($request->request_id);
        }

        $archive_service_request = ArchiveServiceRequests::findOrFail($request->request_id);
        if($archive_service_request){
            $servicerequest = ArchiveServiceRequests::findOrFail($request->request_id);
        }
        //$servicerequest = ServiceRequests::findOrFail($request->request_id);
        if (isset($request->reasons)) {
            $servicerequest->escalation_count = ($servicerequest->escalation_count > 3) ? 4 : $servicerequest->escalation_count+1 ;
            $servicerequest->escalation_reasons = implode(',', json_decode($request->reasons, true));
            $servicerequest->is_escalated = 1;
            $servicerequest->escalation_remarks = $request->remarks;
            // $servicerequest->save();

            // Update escalation assigned in servicerequest table

            $escalation_assigned ='';
            $level_3_emails = '';
            $level_4_emails = '';
            $to_emails = '';
            $cc_emails = '';

            $escalate_count = $servicerequest->escalation_count;

            if ($servicerequest->request_type == 'service') {
                if ($escalate_count == 2) {
                    $level_3_emails = \Config('oly.service_level_3_esc');
                    $escalation_assigned = $level_3_emails;
                } elseif ($escalate_count > 2) {
                    $level_3_emails = \Config('oly.service_level_3_esc');
                    $level_4_emails = \Config('oly.service_level_4_esc');
                    $escalation_assigned = $level_4_emails;
                }
            } else {
                if ($servicerequest->request_type == 'enquiry') {
                    if ($escalate_count == 2) {
                        $level_3_emails = \Config('oly.enq_acad_level_3_esc');
                        $escalation_assigned = $level_3_emails;
                    } elseif ($escalate_count > 2) {
                        $level_3_emails = \Config('oly.enq_acad_level_3_esc');
                        $level_4_emails = \Config('oly.service_level_4_esc');
                        $escalation_assigned = $level_4_emails;
                    }
                } elseif ($servicerequest->request_type == 'academic') {
                    if ($escalate_count > 2) {
                        $level_3_emails = \Config('oly.service_level_4_esc');
                        $escalation_assigned = $level_3_emails;
                    }
                }
            }

            $hospital_state = Hospitals::where('id', $servicerequest->hospital_id)->value('state');
            $dept_name = Departments::where('id', $servicerequest->dept_id)->value('name');
            if ($servicerequest->request_type == 'service' || $servicerequest->request_type == 'academic') {
                $emails = AutoEmails::where("request_type",$servicerequest->request_type)
                    ->where("states","like","%$hospital_state%")
                    ->where("departments","like","%$dept_name%")
                    ->first();
            } else {
                $emails = AutoEmails::where("request_type",$servicerequest->request_type)
                    //Set logic for enquiry requests// ->where("sub_type",)
                    ->where("states","like","%$hospital_state%")
                    ->where("departments","like","%$dept_name%")
                    ->first();
            }
            $cc_emails = implode(",",[$emails['to_emails'],$emails['cc_emails']]);
            $cc_list = array_merge(explode(",",$cc_emails), \Config('oly.escalation_cc'));

            $final_mailerlist= [
                '0' => $cc_emails,
                '1' => $level_3_emails,
                '2' => $level_4_emails,
            ];


            $to_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value($servicerequest->escalation_count);
            // if(empty($to_emails)){ $to_emails = \Config('oly.service_level_4_esc'); }
            $cc_list = [];
            $final_to_list = [];
            $final_cc_list = [];


            foreach ($final_mailerlist as $key => $value) {
                $cc_list = array_merge($cc_list, explode(",", $value));
            }
            foreach (array_filter(array_unique($cc_list)) as $key1 => $value1) {
                array_push($final_cc_list, array('email'=>$value1));
            }

            foreach (explode(',', $to_emails) as $key2 => $value2) {
                array_push($final_to_list, array('email'=>$value2));
            }

            //dd($servicerequest->request_type,$hospital_state,$dept_name,$emails->toArray(),$final_mailerlist,$final_to_list,$final_cc_list);








            $status = new StatusTimeline;
            $status->status ='Escalated';
            $status->customer_id = $servicerequest->customer_id;
            $status->request_id = $servicerequest->id;
            $status ->save();

            $customer = Customers::findOrFail($servicerequest->customer_id);

            $respArr['status_code'] = 200;
            $respArr['cvm_id'] = $servicerequest->cvm_id;
            $respArr['data'] = $servicerequest;

            //send_sms('request_escalated', $customer, $servicerequest, "");
            NotifyCustomer::send_notification('request_escalate', $servicerequest, $customer);

            // $pathToImage = json_decode(file_get_contents(env('APP_URL').'/capture_screenshot_version_two/'.$servicerequest->id.'/escalated'));

            // Mail::to($final_to_list)->cc($final_cc_list)
            // ->send(new RequestEscalated($pathToImage, $servicerequest, $customer));
            if(env('APP_ENV') != "staging"){
                Mail::to($final_to_list)->cc($final_cc_list)
                        ->send(new RequestEscalated($request->request_id, $servicerequest, $customer));
            }
            return response(json_encode($respArr), 200)->header('Content-Type', 'text/plain');
        } else {
            return Response::json(['status_code'=>400,'message'=>'No reasons for escalation selected. Please select at least one reason for escalation','data'=>'']);
        }
    }
    /**
     * Feedback for specific request ID
     *
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function feedback(Request $request)
    {
        $rules = [
            'request_id' => 'required|numeric',
            'response_speed' => 'numeric',
            'quality_of_response' => 'numeric',
            'app_experience' => 'numeric',
            'olympus_staff_performance' => 'numeric',
            'remarks' => 'string',
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(), 'status_code' => 203 ]);
        }else{

            Logger("Submit Feedback");
            Logger($request->all());
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){

                    $chk_service_request = ServiceRequests::where('id', $request->request_id)->first();
                    if(!empty($chk_service_request)){
                        $servicerequest = ServiceRequests::where('id', $request->request_id)->first();
                    }else{
                        $servicerequest = ArchiveServiceRequests::where('id', $request->request_id)->first();
                    }
                    Logger($servicerequest);
                    $hospitals = Hospitals::find($servicerequest->hospital_id);
                    $departments = Departments::find($servicerequest->dept_id);
                    if (isset($servicerequest->feedback_id)||($servicerequest->feedback_id!='')) {
                        $feedback = Feedback::findOrFail($servicerequest->feedback_id);
                    } else {
                        $feedback = new Feedback;
                    }
                    $feedback->request_id = $servicerequest->id;
                    $feedback->response_speed = $request->response_speed;
                    $feedback->quality_of_response = $request->quality_of_response;
                    $feedback->app_experience = $request->app_experience;
                    $feedback->olympus_staff_performance = $request->olympus_staff_performance;
                    $feedback->remarks = $request->remarks;
                    $feedback->save();
                    $servicerequest->feedback_id = $feedback->id;
                    $servicerequest->save();

                    if(env("SFDC_ENABLED") && $servicerequest->request_type == 'service'){
                        $feedback->sfdc_id = $servicerequest->sfdc_id;
                        $SFDCSubmitFeedback = SFDC::submitFeedback($feedback);
                        // if($SFDCSubmitFeedback->success == "true" && isset($SFDCSubmitFeedback->id)){
                        //     $service->sfdc_id = $SFDCSubmitFeedback->id;
                        // }
                    }

                    $customer = Customers::findOrFail($servicerequest->customer_id);

                    //send_sms('feedback_notification', $customer, $servicerequest, "");
                    NotifyCustomer::send_notification('feedback', $servicerequest, $customer);

                    $respArr['status_code'] = 200;
                    $respArr['cvm_id'] = $servicerequest->cvm_id;
                    $respArr['data'] = $servicerequest;


                    Logger($respArr['cvm_id']);
                    Logger($respArr['data']);

                    if (!$servicerequest->is_practice) {
                        // if(env("APP_ENV")  == 'staging'){
                            if ($servicerequest->request_type=='enquiry') {
                                $product_category_arr = explode(',', $servicerequest->product_category);
                                $to_emails = [];
                                $cc_emails = [];
                                for ($i=0; $i < sizeof($product_category_arr); $i++) {
                                    if (trim($product_category_arr[$i])=='Accessory') {
                                        $rules_list = AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'accessory')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                                        $to_emails[$i] = explode(',', $rules_list->to_emails);
                                        $cc_emails[$i] = explode(',', $rules_list->cc_emails);
                                    } elseif (trim($product_category_arr[$i])=='Capital Product') {
                                        $rules_list = AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'capital')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                                        $to_emails[$i] = explode(',', $rules_list->to_emails);
                                        $cc_emails[$i] = explode(',', $rules_list->cc_emails);
                                    } elseif (trim($product_category_arr[$i])=='Other') {
                                        $rules_list = AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'other')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                                        $to_emails[$i] = explode(',', $rules_list->to_emails);
                                        $cc_emails[$i] = explode(',', $rules_list->cc_emails);
                                    }
                                }
                                $to_emails_final['email'] = collect($to_emails)->flatten()->unique()->toArray();
                                $cc_emails_final['email'] = collect($cc_emails)->flatten()->unique()->toArray();
                            } else {
                                $to_emails = [];
                                $cc_emails = [];
                                $rules_list = AutoEmails::where('request_type', $servicerequest->request_type)->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
                                $to_emails_final['email'] = explode(',', $rules_list->to_emails);
                                $cc_emails_final['email'] = explode(',', $rules_list->cc_emails);
                            }
                            if ($servicerequest->request_type!='service') {
                                $cc_emails_final['email'] = array_merge($cc_emails_final,\Config('oly.enq_acad_coordinator_email'));
                            }
                            $users = collect($to_emails_final['email'])->flatten()->toArray();
                            $cc = collect($cc_emails_final['email'])->flatten()->toArray();
                            // $hospital_state = Hospitals::where('id', $servicerequest->hospital_id)->select('state')->get()->toArray();
                            // $hospital_region = find_region($hospital_state[0]['state']);
                            // switch ($hospital_region) {
                            //     case 'north': $cc_list[]=\Config('oly.workshopmanagers_north');break;
                            //     case 'east': $cc_list[]=\Config('oly.workshopmanagers_east');break;
                            //     case 'south': $cc_list[]=\Config('oly.workshopmanagers_south');break;
                            //     case 'west': $cc_list[]=\Config('oly.workshopmanagers_west');break;
                            //     default: break;
                            // }
                            $cc = array_merge($cc, \Config('oly.feedback_cc'));
                            $users_final = [];
                            $cc_final = [];
                            for ($i=0; $i < sizeof($users); $i++) {
                                $users_final[]['email'] = $users[$i];
                            }

                            for ($j=0; $j < sizeof($cc); $j++) {
                                $cc_final[]['email'] = $cc[$j];
                            }

                            $assigned_person = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->first();
                            if($assigned_person){
                                $users_final[]['email'] = $assigned_person->email;
                            }
                        //}
                        // code comment on 13 dec
                        //$users_final = 'sandeep.gupta@lyxellabs.com';
                        //$cc_final = 'sandeep.gupta@lyxellabs.com';
                        // $pathToImage = json_decode(file_get_contents(env('APP_URL').'/capture_screenshot_version_two/'.$servicerequest->id.'/feedback'));

                        // Mail::to($users_final)->cc($cc_final)
                        // ->send(new FeedbackCreated($pathToImage, $servicerequest, $customer));
                        if(env('APP_ENV') != "staging"){
                             Mail::to($users_final)->cc($cc_final)
                            ->send(new FeedbackCreated($servicerequest->id, $servicerequest, $customer));
                        }
                    }
                    Logger(json_encode($respArr));
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

    /*public function get_requests_history_old_24dec(Request $request)
    {

        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'request_type' => 'regex:/^[a-zA-Z\s]*$/'
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(), 'status_code' => 203 ]);
        }else{
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
                    $history = (object)[];
                    //$history->ongoingAry= ServiceRequests::where('customer_id', $request->customer_id)->where('status', '!=', 'Closed')->latest()->get();

                    $ongoingServiceAry = ServiceRequests::where('customer_id', $request->customer_id)->where('status', '!=', 'Closed')->latest()->get();
                    $ongoingArchiveServiceAry = ArchiveServiceRequests::where('customer_id', $request->customer_id)->where('status', '!=', 'Closed')->latest()->get();

                    $history->ongoingAry =  $ongoingServiceAry->merge($ongoingArchiveServiceAry);

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

                        $archive_service_request_escalation_count = ArchiveServiceRequests::where('id', $value->id)->value('escalation_count');
                        if($archive_service_request_escalation_count){
                            $esc_count = ArchiveServiceRequests::where('id', $value->id)->value('escalation_count');
                        }

                        $esc_count = ($esc_count > 4) ? 4 : $esc_count ;



                        if ($value->request_type=='service') {
                            if (!empty($value->employee_code) && !is_null($value->employee_code) && $esc_count > 0) {
                                // Safely fetch employee data
                                $emp_data_record = EmployeeTeam::where('employee_code', $value->employee_code)->first();

                                if ($emp_data_record) {
                                    $emp_data = $emp_data_record->toArray();
                                } else {
                                    $emp_data = []; // fallback to empty array if no record found
                                }

                                // Ensure escalation_detail is an array
                                if (!isset($value->escalation_detail) || !is_array($value->escalation_detail)) {
                                    $value->escalation_detail = [];
                                }

                                for ($repeat_1 = 1; $repeat_1 <= $esc_count; $repeat_1++) {
                                    $emp_mail_key = 'escalation_'.$repeat_1;

                                    if (!empty($emp_data[$emp_mail_key])) {
                                        $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail_key])
                                                        ->select('name', 'email', 'mobile', 'image', 'designation')
                                                        ->first();

                                        if ($esc_detail1) {
                                            // Add extra properties
                                            $esc_detail1->employee_image = config('app.url') . "/storage/" . $esc_detail1->image;
                                            $esc_detail1->escalation_level = $repeat_1;

                                            // Add to escalation_detail array
                                            $value->escalation_detail[] = $esc_detail1;
                                        }
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
                                // $emp_data = EmployeeTeam::where('employee_code', $value->employee_code)->get()->toArray()[0];
                                // for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                                //     $emp_mail = 'escalation_'.$repeat_1;
                                //     $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile', 'image', 'designation')->first();
                                //     if (!is_null($esc_detail1)) {
                                //         $esc_detail1->employee_image = config('app.url')."/storage/".$esc_detail1->image;
                                //         $esc_detail1->escalation_level = $repeat_1;
                                //         $value->escalation_detail = array_merge($value->escalation_detail, array($esc_detail1));
                                //     }
                                // }

                                // Safely fetch employee data
                                $emp_data_record = EmployeeTeam::where('employee_code', $value->employee_code)->first();

                                if ($emp_data_record) {
                                    $emp_data = $emp_data_record->toArray();
                                } else {
                                    $emp_data = []; // fallback to empty array if no record found
                                }

                                // Ensure escalation_detail is an array
                                if (!isset($value->escalation_detail) || !is_array($value->escalation_detail)) {
                                    $value->escalation_detail = [];
                                }

                                for ($repeat_1 = 1; $repeat_1 <= $esc_count; $repeat_1++) {
                                    $emp_mail_key = 'escalation_'.$repeat_1;

                                    if (!empty($emp_data[$emp_mail_key])) {
                                        $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail_key])
                                                        ->select('name', 'email', 'mobile', 'image', 'designation')
                                                        ->first();

                                        if ($esc_detail1) {
                                            // Add extra properties
                                            $esc_detail1->employee_image = config('app.url') . "/storage/" . $esc_detail1->image;
                                            $esc_detail1->escalation_level = $repeat_1;

                                            // Add to escalation_detail array
                                            $value->escalation_detail[] = $esc_detail1;
                                        }
                                    }
                                }

                            }
                            if (!empty($value->employee_code)) {
                                $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                                 if(!empty($value->fseAry[0]->employee_image)){
                                    $value->fseAry[0]->employee_image = config('app.url')."/storage/".$value->fseAry[0]->image;
                                }
                                //$value->fseAry[0]->employee_image = config('app.url')."/storage/".$value->fseAry[0]->image;
                            } else {
                                $value->fseAry = []; // Request Received  , Yet not assigned
                            }
                        }

                        $request_history = StatusTimeline::where('customer_id', $value->customer_id)->where('request_id', $value->id)->get();
                        $reminder_history = RequestReminderHistory::where('customer_id', $value->customer_id)->where('request_id', $value->id)->select('id','customer_id','request_id','status', 'created_at','updated_at')->get();
                        //dd($reminder_history);
                        $mergedData = collect(array_merge($request_history->toArray(), $reminder_history->toArray()));

                        // Sort merged data by `created_at` in descending order
                        $sortedData = $mergedData->sortBy('created_at');
                        $value->timelineAry = $sortedData->values();

                        //$value->timelineAry = StatusTimeline::where('customer_id', $value->customer_id)->where('request_id', $value->id)->get();
                        $value->request_progress = request_progress($value->request_type, $value->status);

                        // $value->timelineAry = StatusTimeline::where('customer_id', $value->customer_id)->where('request_id', $value->id)->get();
                        // $value->request_progress = request_progress($value->request_type, $value->status);
                    }

                    $serviceClosedAry= ServiceRequests::where('customer_id', $request->customer_id)
                    ->where(function ($q) {
                        $q->where([['status','=','Closed'],['request_type','=', 'service']])
                          ->orWhere([['status','=','Closed'],['request_type','=', 'enquiry']])
                          ->orWhere([['status','=','Closed'],['request_type','=', 'academic']]);
                    })
                    ->latest()->get();

                    $archiveServiceClosedAry= ArchiveServiceRequests::where('customer_id', $request->customer_id)
                    ->where(function ($q) {
                        $q->where([['status','=','Closed'],['request_type','=', 'service']])
                          ->orWhere([['status','=','Closed'],['request_type','=', 'enquiry']])
                          ->orWhere([['status','=','Closed'],['request_type','=', 'academic']]);
                    })
                    ->latest()->get();

                    $history->closedAry=  $serviceClosedAry->merge($archiveServiceClosedAry);

                    foreach ($history->closedAry as $key => $value) {
                        $value->hospital_name = Hospitals::where('id', $value->hospital_id)->value('hospital_name');
                        $value->dept_name = Departments::where('dept_id', $value->dept_id)->value('name');

                        $value->escalation_detail = [];
                        $esc_detail1 = [];
                        $esc_count = 0;
                        $service_request_escalation_count_chk = ServiceRequests::where('id', $value->id)->value('escalation_count');
                        if($service_request_escalation_count_chk){
                            $esc_count = ServiceRequests::where('id', $value->id)->value('escalation_count');
                        }

                        $archive_service_request_escalation_count_chk = ArchiveServiceRequests::where('id', $value->id)->value('escalation_count');
                        if($archive_service_request_escalation_count_chk){
                            $esc_count = ArchiveServiceRequests::where('id', $value->id)->value('escalation_count');
                        }

                        $esc_count = ($esc_count > 4) ? 4 : $esc_count ;


                        $value->escalation_detail = [];

                        if (!is_null($value->feedback_id)) {
                            $value->feedback =  Feedback::where('request_id', $value->id)->get();
                        } else {
                            $value->feedback = [];
                        }
                        if ($value->request_type=='service') {
                            if (!empty($value->employee_code) && !is_null($value->employee_code) && $esc_count > 0) {
                                // $emp_data = EmployeeTeam::where('employee_code', $value->employee_code)->get()->toArray()[0];
                                // for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                                //     $emp_mail = 'escalation_'.$repeat_1;
                                //     $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile', 'image', 'designation')->first();
                                //     if (!is_null($esc_detail1)) {
                                //         $esc_detail1->employee_image = config('app.url')."/storage/".$esc_detail1->image;
                                //         $esc_detail1->escalation_level = $repeat_1;
                                //         $value->escalation_detail = array_merge($value->escalation_detail, array($esc_detail1));
                                //     }
                                // }

                                // Safely fetch employee data
                                $emp_data_record = EmployeeTeam::where('employee_code', $value->employee_code)->first();

                                if ($emp_data_record) {
                                    $emp_data = $emp_data_record->toArray();
                                } else {
                                    $emp_data = []; // fallback to empty array if no record found
                                }

                                // Ensure escalation_detail is an array
                                if (!isset($value->escalation_detail) || !is_array($value->escalation_detail)) {
                                    $value->escalation_detail = [];
                                }

                                for ($repeat_1 = 1; $repeat_1 <= $esc_count; $repeat_1++) {
                                    $emp_mail_key = 'escalation_'.$repeat_1;

                                    if (!empty($emp_data[$emp_mail_key])) {
                                        $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail_key])
                                                        ->select('name', 'email', 'mobile', 'image', 'designation')
                                                        ->first();

                                        if ($esc_detail1) {
                                            // Add extra properties
                                            $esc_detail1->employee_image = config('app.url') . "/storage/" . $esc_detail1->image;
                                            $esc_detail1->escalation_level = $repeat_1;

                                            // Add to escalation_detail array
                                            $value->escalation_detail[] = $esc_detail1;
                                        }
                                    }
                                }

                            }

                            if (!empty($value->employee_code)) {
                                $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                                if(!empty($value->fseAry[0]->employee_image)){
                                    $value->fseAry[0]->employee_image = config('app.url')."/storage/".$value->fseAry[0]->image;
                                }
                                //$value->fseAry[0]->employee_image = config('app.url')."/storage/".$value->fseAry[0]->image;
                            } else {
                                $value->fseAry = []; // Request Received  , Yet not assigned
                            }
                            $value->product_info = ProductInfo::where('service_requests_id', $value->id)->get();
                            $value->technical_report = TechnicalReport::where('service_requests_id', $value->id)->get();
                        } else {
                            if (!empty($value->employee_code) && !is_null($value->employee_code) && $esc_count > 0) {
                                // $emp_data = EmployeeTeam::where('employee_code', $value->employee_code)->get()->toArray()[0];
                                // for ($repeat_1=1; $repeat_1 <= $esc_count; $repeat_1++) {
                                //     $emp_mail = 'escalation_'.$repeat_1;
                                //     $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail])->select('name', 'email', 'mobile', 'image', 'designation')->first();
                                //     if (!is_null($esc_detail1)) {
                                //         $esc_detail1->employee_image = config('app.url')."/storage/".$esc_detail1->image;
                                //         $esc_detail1->escalation_level = $repeat_1;
                                //         $value->escalation_detail = array_merge($value->escalation_detail, array($esc_detail1));
                                //     }
                                // }

                                // Safely fetch employee data
                                $emp_data_record = EmployeeTeam::where('employee_code', $value->employee_code)->first();

                                if ($emp_data_record) {
                                    $emp_data = $emp_data_record->toArray();
                                } else {
                                    $emp_data = []; // fallback to empty array if no record found
                                }

                                // Ensure escalation_detail is an array
                                if (!isset($value->escalation_detail) || !is_array($value->escalation_detail)) {
                                    $value->escalation_detail = [];
                                }

                                for ($repeat_1 = 1; $repeat_1 <= $esc_count; $repeat_1++) {
                                    $emp_mail_key = 'escalation_'.$repeat_1;

                                    if (!empty($emp_data[$emp_mail_key])) {
                                        $esc_detail1 = EmployeeTeam::where('email', $emp_data[$emp_mail_key])
                                                        ->select('name', 'email', 'mobile', 'image', 'designation')
                                                        ->first();

                                        if ($esc_detail1) {
                                            // Add extra properties
                                            $esc_detail1->employee_image = config('app.url') . "/storage/" . $esc_detail1->image;
                                            $esc_detail1->escalation_level = $repeat_1;

                                            // Add to escalation_detail array
                                            $value->escalation_detail[] = $esc_detail1;
                                        }
                                    }
                                }

                            }
                            if (!empty($value->employee_code)) {
                                $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();
                                if(!empty($value->fseAry[0]->employee_image)){
                                    $value->fseAry[0]->employee_image = config('app.url')."/storage/".$value->fseAry[0]->image;
                                }
                                //$value->fseAry[0]->employee_image = config('app.url')."/storage/".$value->fseAry[0]->image;
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

                        // $value->timelineAry = StatusTimeline::where('customer_id', $value->customer_id)->where('request_id', $value->id)->get();
                        // $value->request_progress = request_progress($value->request_type, $value->status);
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
    }*/

    public function get_requests_history(Request $request)
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'request_type' => 'regex:/^[a-zA-Z\s]*$/'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status_code' => 203
            ]);
        }

        $user = auth('customer-api')->user();

        if (!$user) {
            return response()->json([
                'status_code' => 400,
                'message' => 'user not found'
            ]);
        }
        if($user->id != $request->customer_id){
            return response()->json([
                'status_code' => 400,
                'message' => 'user not found'
            ]);
        }

        if ($user->is_expired != 0) {
            return response()->json([
                'status_code' => 407,
                'message' => 'password expired',
                'is_expired' => $user->is_expired
            ]);
        }

        $history = (object)[];

        /* ===================== ONGOING REQUESTS ===================== */

        $ongoingServiceAry = ServiceRequests::where('customer_id', $request->customer_id)
            ->where('status', '!=', 'Closed')->latest()->get();

        $ongoingArchiveServiceAry = ArchiveServiceRequests::where('customer_id', $request->customer_id)
            ->where('status', '!=', 'Closed')->latest()->get();

        $history->ongoingAry = $ongoingServiceAry->merge($ongoingArchiveServiceAry);

        foreach ($history->ongoingAry as $value) {

            $value->hospital_name = Hospitals::where('id', $value->hospital_id)->value('hospital_name');
            $value->dept_name = Departments::where('dept_id', $value->dept_id)->value('name');

            /* ---------- Escalation Count ---------- */
            $esc_count = ServiceRequests::where('id', $value->id)->value('escalation_count')
                ?? ArchiveServiceRequests::where('id', $value->id)->value('escalation_count')
                ?? 0;

            $esc_count = min($esc_count, 4);
            $value->escalation_detail = [];

            /* ---------- Escalation Details ---------- */
            if (!empty($value->employee_code) && $esc_count > 0) {

                $emp_data = EmployeeTeam::where('employee_code', $value->employee_code)->first();

                if ($emp_data) {
                    for ($i = 1; $i <= $esc_count; $i++) {

                        $mailColumn = 'escalation_' . $i;

                        if (!empty($emp_data->$mailColumn)) {

                            $esc = EmployeeTeam::where('email', $emp_data->$mailColumn)
                                ->select('name', 'email', 'mobile', 'image', 'designation')
                                ->first();

                            if ($esc) {
                                $esc->employee_image = config('app.url') . "/storage/" . $esc->image;
                                $esc->escalation_level = $i;
                                $value->escalation_detail[] = $esc;
                            }
                        }
                    }
                }
            }

            /* ---------- FSE ---------- */
            if (!empty($value->employee_code)) {
                $value->fseAry = EmployeeTeam::where('employee_code', $value->employee_code)->get();

                if ($value->fseAry->isNotEmpty() && !empty($value->fseAry[0]->image)) {
                    $value->fseAry[0]->employee_image =
                        config('app.url') . "/storage/" . $value->fseAry[0]->image;
                }
            } else {
                $value->fseAry = [];
            }

            /* ---------- Service Extra Data ---------- */
            if ($value->request_type === 'service') {
                $value->product_info = ProductInfo::where('service_requests_id', $value->id)->get();
                $value->technical_report = TechnicalReport::where('service_requests_id', $value->id)->get();
            }

            /* ---------- Timeline ---------- */
            $request_history = StatusTimeline::where('customer_id', $value->customer_id)
                ->where('request_id', $value->id)->get();

            $reminder_history = RequestReminderHistory::where('customer_id', $value->customer_id)
                ->where('request_id', $value->id)
                ->select('id', 'customer_id', 'request_id', 'status', 'created_at', 'updated_at')
                ->get();

            $value->timelineAry = collect(array_merge(
                $request_history->toArray(),
                $reminder_history->toArray()
            ))->sortBy('created_at')->values();

            $value->request_progress = request_progress($value->request_type, $value->status);
        }

        /* ===================== CLOSED REQUESTS ===================== */

        $serviceClosedAry = ServiceRequests::where('customer_id', $request->customer_id)
            ->where('status', 'Closed')->latest()->get();

        $archiveServiceClosedAry = ArchiveServiceRequests::where('customer_id', $request->customer_id)
            ->where('status', 'Closed')->latest()->get();

        $history->closedAry = $serviceClosedAry->merge($archiveServiceClosedAry);

        foreach ($history->closedAry as $value) {

            $value->hospital_name = Hospitals::where('id', $value->hospital_id)->value('hospital_name');
            $value->dept_name = Departments::where('dept_id', $value->dept_id)->value('name');

            $value->feedback = $value->feedback_id
                ? Feedback::where('request_id', $value->id)->get()
                : [];

            $value->request_progress = request_progress($value->request_type, $value->status);
        }

        return response()->json([
            'status_code' => 200,
            'history' => $history
        ]);
    }

    public function findNextWorkingDay(){
        $vacation = array();
        for ($number=1; $number <=7 ; $number++) {
            $WorkingOrOff = Calender::where('date', date('Y-m-d', strtotime("+".$number." day")))->first();
            if (is_null($WorkingOrOff)) {
                $vacation[$number] = false;
            } else {
                $vacation[$number] = true;
            }
        }
        $followup_day = strtotime("Tomorrow"); //Tomorrow
        // Find next working day
        for ($day=1; $day <= 7; $day++) {
            $dayNumber = date('N', $followup_day);
            // echo date('Y-m-d', $followup_day).' '.$dayNumber.'<br>';
            if (($dayNumber==6) || ($dayNumber==7)) {
                $followup_day = strtotime("+1 day", $followup_day);
            } elseif ($vacation[$day] == true) {
                $followup_day = strtotime("+1 day", $followup_day);
            } else { // This date is working day
                break;
            }
        }
        return $followup_day;
    }

    public function testrequestescalatednew($id){
        return view('emails.request_escalated_new', ['id'=>$id]);
    }

    public function customerRequestAcknowledgement(Request $request)
    {
        $rules = [
            'request_id' => 'required|numeric|exists:service_requests,id',
            'acknowledgement_status' => 'required|numeric'
        ];
        $validator = Validator::make( $request->all(), $rules);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(), 'status_code' => 203 ]);
        }else{
            $user = auth('customer-api')->user();
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){
                    $data = ServiceRequests::where('id', $request->request_id)->where('status', 'Dispatched')->first();
                    if($data){
                        if($data->acknowledgement_status == 1){
                            return Response::json(['status_code'=>202, 'message' => 'This request already acknowledged.', 'data'=> ServiceRequests::where('id', $request->request_id)->where('status', 'Dispatched')->first()]);
                        }else{
                            if($request->acknowledgement_status == 1){
                                $acknowledgement_status_key = 'Yes';
                                $message = "Thank you so much for your acknowledgement. I hope we have resolved the issue.";
                            }else{
                                $acknowledgement_status_key = 'No';
                                $message = "Sorry for the inconvenience caused. Our service engineer will get in touch with you soon.";
                            }
                            ServiceRequests::where('id', $request->request_id)->where('status', 'Dispatched')->update([
                                'acknowledgement_status' => $request->acknowledgement_status,
                                'acknowledgement_updated_at' => Carbon::now()
                            ]);
                            //$acknowledgement_status_key = $request->acknowledgement_status;
                            $request_id_key = $data->sfdc_id;
                            if(env("SFDC_ENABLED")){
                                $SFDCCreateRequest = SFDC::acknowledgeRequest($acknowledgement_status_key, $request_id_key);
                                if(isset($SFDCCreateRequest->success)){
                                    Log::info("\n===SFDC acknowledge status success"."\n\n");
                                    Log::info($SFDCCreateRequest);
                                }else{
                                    Log::info("\n===Error SFDC acknowledge status"."\n\n");
                                    Log::info($SFDCCreateRequest);
                                }
                            }
                            return Response::json(['status_code'=>200, 'message' => $message, 'data'=>ServiceRequests::where('id', $request->request_id)->where('status', 'Dispatched')->first()]);
                        }
                    }else{
                        $get_data = ServiceRequests::where('id', $request->request_id)->first();
                        return Response::json(['status_code'=>202, 'message' => 'This request can not be acknowledged.', 'data' => $get_data]);
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
