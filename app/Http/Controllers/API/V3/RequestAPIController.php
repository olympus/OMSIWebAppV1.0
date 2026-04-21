<?php

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Calender;
use App\CustomerShowPromailer;
use App\Models\Departments; 
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
use App\Models\HappyCodeHistory;
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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use JWTAuth;
use Log;
use Response;
use Spatie\Browsershot\Browsershot;
use Validator;

class RequestAPIController extends Controller
{   
    /*
       ALL REQUEST LIST API
    */

    public function getRequestHistory(Request $request)
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

        $validator = \Validator::make($request->all(), [
            'year' => 'nullable|array',
            'year.*' => 'digits:4',
            'status' => 'nullable|array',
            'status.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $customerId = $user->id;
        //$customerId = 7064;

        /* ================= OPTIONAL FILTERS ================= */

        /* ================= OPTIONAL FILTERS ================= */

            $years = $request->year ?? [];
            $statuses = $request->status ?? [];

        /* ================= FETCH REQUESTS ================= */

            $serviceQuery = ServiceRequests::where('customer_id', $customerId);
            $archiveQuery = ArchiveServiceRequests::where('customer_id', $customerId);

            $serviceStatuses = $serviceQuery->distinct()->pluck('status');

            $uniqueStatuses = $serviceStatuses
            ->reject(function ($status) {
                return strtolower($status) == 'closed';
            })
            ->unique()
            ->values();

            $years_list =  array_reverse(range(2018, Carbon::now()->year)); 

            if (!empty($years)) {
                $serviceQuery->whereIn(\DB::raw('YEAR(created_at)'), $years);
                $archiveQuery->whereIn(\DB::raw('YEAR(created_at)'), $years);
            }

            if (!empty($statuses)) {
                $serviceQuery->whereIn('status', $statuses);
                $archiveQuery->whereIn('status', $statuses);
            }

            // $serviceStatuses  = $serviceQuery->distinct()->pluck('status');
            // $uniqueStatuses = $serviceStatuses ->unique()->values();
            
             
            
            $serviceRequests = $serviceQuery->orderBy('id', 'desc')->get();
            $archiveRequests = $archiveQuery->orderBy('id', 'desc')->get();

            $requests = $serviceRequests->merge($archiveRequests);
            
            if ($requests->isEmpty()) {
                return response()->json([
                    'status_code' => 200,
                    'message' => "Data not found",
                    'years_list' => $years_list,
                    'status_list' => $uniqueStatuses,
                    'history' => [
                        'ongoingAry' => [],
                        'closedAry' => []
                    ]
                ]);
            }

        /* ================= COLLECT IDS ================= */

        $hospitalIds = $requests->pluck('hospital_id')->filter()->unique();
        $deptIds = $requests->pluck('dept_id')->filter()->unique();
        $employeeCodes = $requests->pluck('employee_code')->filter()->unique();
        $requestIds = $requests->pluck('id')->unique();

        /* ================= BULK LOAD ================= */

        $hospitals = Hospitals::whereIn('id', $hospitalIds)
            ->pluck('hospital_name', 'id');

        $departments = Departments::whereIn('dept_id', $deptIds)
            ->pluck('name', 'dept_id');

        $employees = EmployeeTeam::whereIn('employee_code', $employeeCodes)
            ->get();

        $employeesByCode = $employees->keyBy('employee_code');
        $employeesByEmail = $employees->keyBy('email');

        $feedbacks = Feedback::whereIn('request_id', $requestIds)
            ->get()
            ->groupBy('request_id');

        $timelines = StatusTimeline::where('customer_id', $customerId)
            ->whereIn('request_id', $requestIds)
            ->get()
            ->groupBy('request_id');

        $reminders = RequestReminderHistory::where('customer_id', $customerId)
            ->whereIn('request_id', $requestIds)
            ->get()
            ->groupBy('request_id');

        /* ================= PROCESS ================= */

        $ongoing = [];
        $closed = [];

        foreach ($requests as $req) {

            $item = clone $req; // avoid modifying original model

            $item->hospital_name = $hospitals[$req->hospital_id] ?? null;
            $item->dept_name = $departments[$req->dept_id] ?? null;

            /* -------- Escalation -------- */

            $escalationDetail = [];
            $escCount = min($req->escalation_count ?? 0, 4);

            if ($escCount > 0 && isset($employeesByCode[$req->employee_code])) {

                $emp = $employeesByCode[$req->employee_code];

                for ($i = 1; $i <= $escCount; $i++) {

                    $column = 'escalation_' . $i;

                    if (!empty($emp->$column) && isset($employeesByEmail[$emp->$column])) {

                        $escEmp = clone $employeesByEmail[$emp->$column];

                        $escEmp->employee_image = $escEmp->image
                            ? config('app.url') . "/storage/" . $escEmp->image
                            : null;

                        $escEmp->escalation_level = $i;

                        $escalationDetail[] = $escEmp;
                    }
                }
            }

            $item->escalation_detail = $escalationDetail;

            /* -------- Timeline -------- */

            $timelineData = collect();

            if (isset($timelines[$req->id])) {
                $timelineData = $timelineData->merge($timelines[$req->id]);
            }

            if (isset($reminders[$req->id])) {
                $timelineData = $timelineData->merge($reminders[$req->id]);
            }

            $item->timelineAry = $timelineData
                ->sortBy('created_at')
                ->values();

            /* -------- Feedback -------- */

            $item->feedback = $feedbacks[$req->id] ?? [];

            /* -------- Progress -------- */

            $item->request_progress = request_progress($req->request_type, $req->status);

            /* -------- Separate Ongoing & Closed -------- */

            if ($req->status === 'Closed') {
                $closed[] = $item;
            } else {
                $ongoing[] = $item;
            }
        }

         

        return response()->json([
            'status_code' => 200,
            'message' => "Data found successfully",
            'years_list' => $years_list,
            'status_list' => $uniqueStatuses,
            'history' => [
                'ongoingAry' => $ongoing,
                'closedAry' => $closed
            ]
        ]);
    }

    /*
        REQUEST DETAIL API
    */

    public function getRequestHistoryDetail(Request $request)
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

        /* ================= VALIDATION ================= */

        $validator = \Validator::make($request->all(), [
            'request_id' => 'required|integer'
        ], [
            'request_id.required' => 'Request id is required.',
            'request_id.integer'  => 'Request id must be a valid number.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $customerId = $user->id;
        $requestId = $request->request_id;

        /* ================= FIND REQUEST ================= */

        $req = ServiceRequests::where('id', $requestId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$req) {
            $req = ArchiveServiceRequests::where('id', $requestId)
                ->where('customer_id', $customerId)
                ->first();
        }

        if (!$req) {
            return response()->json([
                'status_code' => 404,
                'message' => 'Request not found'
            ], 404);
        }

        /* ================= RELATED DATA ================= */

        $hospitalName = Hospitals::where('id', $req->hospital_id)
            ->value('hospital_name');

        $deptName = Departments::where('dept_id', $req->dept_id)
            ->value('name');

        $item = clone $req;

        $item->hospital_name = $hospitalName ?? null;
        $item->dept_name = $deptName ?? null;

        /* ================= ESCALATION ================= */

        $escalationDetail = [];
        $escCount = min($req->escalation_count ?? 0, 4);

        if (!empty($req->employee_code) && $escCount > 0) {

            $employee = EmployeeTeam::where('employee_code', $req->employee_code)->first();

            if ($employee) {

                $emails = collect([
                    $employee->escalation_1,
                    $employee->escalation_2,
                    $employee->escalation_3,
                    $employee->escalation_4
                ])->filter();

                $escalationEmployees = EmployeeTeam::whereIn('email', $emails)
                    ->get()
                    ->keyBy('email');

                for ($i = 1; $i <= $escCount; $i++) {

                    $column = 'escalation_' . $i;

                    if (!empty($employee->$column) && isset($escalationEmployees[$employee->$column])) {

                        $escEmp = clone $escalationEmployees[$employee->$column];

                        $escEmp->employee_image = $escEmp->image
                            ? config('app.url') . "/storage/" . $escEmp->image
                            : null;

                        $escEmp->escalation_level = $i;

                        $escalationDetail[] = $escEmp;
                    }
                }
            }
        }

        $item->escalation_detail = $escalationDetail;

        /* ================= TIMELINE ================= */

        $timeline = StatusTimeline::where('customer_id', $customerId)
            ->where('request_id', $requestId)
            ->get();

        $reminder = RequestReminderHistory::where('customer_id', $customerId)
            ->where('request_id', $requestId)
            ->get();

        $item->timelineAry = $timeline
            ->merge($reminder)
            ->sortBy('created_at')
            ->values();

        /* ================= FEEDBACK ================= */

        $item->feedback = Feedback::where('request_id', $requestId)->get();

        /* ================= PROGRESS ================= */

        $item->request_progress = request_progress($req->request_type, $req->status);

        $item->office_address = "Olympus Medical Systems India Ground Floor, Tower- C, SAS Tower, The Medicity Complex Sector - 38, Gurgaon - 122001, Haryana, India";

        $item->office_contact_number = "1800 102 3654";
        return response()->json([
            'status_code' => 200,
            'message' => 'Request Details',
            'data' => $item
        ]);
    } 


    /*
       CREATE REQUEST API
    */
       
    public function createRequestTicket(Request $request)
    {
        Logger('Service Store Api Request Payload');
        Logger($request->all());

        $user = auth('customer-api')->user();
        //dd($user);
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

        // Support single OR multiple request
        $requests = isset($request[0]) ? $request->all() : [$request->all()];
        $finalResponse = [];

        foreach ($requests as $reqData) {

            $validator = Validator::make($reqData, [
                'request_type' => 'required|in:enquiry,academic,service|regex:/^[a-zA-Z\s]*$/',
                'customer_id'  => 'required|exists:customers,id',
                'hospital_id'  => 'required|numeric',
                'dept_id'      => 'required|numeric',
                'remarks'      => 'required|string',
                'sub_type'     => 'required',
            ], [
                'hospital_id.required' => 'hospital field is required',
                'dept_id.required'     => 'department field is required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()->first()
                ], 422);
            } 

            try {

                $hospitals = Hospitals::find($reqData['hospital_id']);
                if (!isset($hospitals)) {
                    return response()->json([
                        'status_code' => 401,
                        'msg' => 'The hospital does not exist !'
                    ], 401);
                }

                $departments = Departments::find($reqData['dept_id']);
                if (!isset($departments)) {
                    return response()->json([
                        'status_code' => 401,
                        'msg' => 'The department does not exist !'
                    ], 401);
                }

                $service = new ServiceRequests;
                $service->request_type = $reqData['request_type'];
                $service->sub_type     = $reqData['sub_type'];
                $service->customer_id  = $reqData['customer_id'];
                $service->hospital_id  = $reqData['hospital_id'];
                $service->dept_id      = $reqData['dept_id'];
                $service->remarks      = $reqData['remarks'];
                $service->status       = 'Received';

                if ($reqData['request_type'] == 'enquiry' && isset($reqData['product_category'])) {
                    $service->product_category = rtrim($reqData['product_category'], ',');
                }

                $service->save();

                // Keep your original CVM logic
                $service->cvm_id = sprintf('%08d', $service->id);
                $service->save();

                $status = new StatusTimeline;
                $status->status = 'Received';
                $status->customer_id = $service->customer_id;
                $status->request_id = $service->id;
                $status->save();

                $customer = Customers::findOrFail($service->customer_id);

                // ================= SFDC (Only Production) =================
                if (env("SFDC_ENABLED") && $reqData['request_type'] == 'service') {
                //if (env("APP_ENV") == "production" && env("SFDC_ENABLED") && $reqData['request_type'] == 'service') {

                    $SFDCCreateRequest = SFDC::createRequest($service, $customer, $hospitals, "");

                    if (isset($SFDCCreateRequest->success)) {
                        if ($SFDCCreateRequest->success == "true" && isset($SFDCCreateRequest->id)) {
                            $service->sfdc_id = $SFDCCreateRequest->id;
                            $service->save();
                        } else {
                            Log::info("===Error SFDC Create Request new_request");
                            Log::info($SFDCCreateRequest);
                        }
                    } else {
                        Log::info("===Error SFDC Create Request new_request");
                        Log::info($SFDCCreateRequest);
                    }
                }

                if (strpos($customer->email, '@olympus.com') !== false) {
                    $service->is_practice = true;
                    $service->save();
                }

                // ================= Your Response Logic (UNCHANGED) =================

                $respArr['status_code'] = 200;
                $respArr['cvm_id'] = $service->cvm_id;
                $respArr['message_top'] = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.",\n\nWe have received your request with the following details:";

                $working_day_today = Calender::where('date', date('Y-m-d'))->first();

                if ((date('H')>0) && (date('H')<5) && (is_null($working_day_today))) {
                    $filter_text = "today";
                } elseif ((date('H')>5) && (date('H')<16) && (is_null($working_day_today))) {
                    $filter_text = "shortly";
                } else {
                    $followup_day = $this->findNextWorkingDay();
                    $filter_text = "on ".ucfirst(date('l', $followup_day))." (".date('d-m-Y', $followup_day).")";
                }

                $text = ($reqData['request_type']=='service') ? "Our engineer" : "Our executive";
                $respArr['message_bottom'] = $text.' will reach out to you '.$filter_text.".\n\nThank you very much.\nOlympus India";

                $servicerequest = ServiceRequests::find($service->id);

                NotifyCustomer::send_new_notification('request_create', $servicerequest, $customer);

                // ================= MAIL SECTION (YOUR FULL LOGIC KEPT) =================
                try {
                    if ((!$servicerequest->is_practice)) {

                        if(env('APP_ENV') != "staging"){

                            // LOCAL → override mail only
                            if(env('APP_ENV') == "local"){
                                $users_final = [['email' => 'ritik.bansal@lyxelandflamingo.com']];
                                $cc_final = [];
                            } else {

                                // ==== YOUR ORIGINAL AUTO EMAIL LOGIC STARTS ====
                                $to_emails = [];
                                $cc_emails = [];

                                if ($servicerequest->request_type=='enquiry') {
                                    $product_category_arr = explode(',', $servicerequest->product_category);

                                    for ($i=0; $i < sizeof($product_category_arr); $i++) {

                                        $subType = strtolower(trim($product_category_arr[$i])) == 'capital product' ? 'capital'
                                            : strtolower(trim($product_category_arr[$i]));

                                        $rules_list = AutoEmails::where('request_type', 'enquiry')
                                            ->where('sub_type', $subType)
                                            ->whereRaw("find_in_set('".$hospitals->state."',states)")
                                            ->whereRaw("find_in_set('".$departments->name."',departments)")
                                            ->first();

                                        if($rules_list){
                                            $to_emails[$i] = explode(',', $rules_list->to_emails);
                                            $cc_emails[$i] = explode(',', $rules_list->cc_emails);
                                        }
                                    }

                                    $to_emails_final['email'] = collect($to_emails)->flatten()->unique()->toArray();
                                    $cc_emails_final['email'] = collect($cc_emails)->flatten()->unique()->toArray();

                                } else {

                                    $rules_list = AutoEmails::where('request_type', $servicerequest->request_type)
                                        ->whereRaw("find_in_set('".$hospitals->state."',states)")
                                        ->whereRaw("find_in_set('".$departments->name."',departments)")
                                        ->first();

                                    $to_emails_final['email'] = explode(',', $rules_list->to_emails ?? '');
                                    $cc_emails_final['email'] = explode(',', $rules_list->cc_emails ?? '');
                                }

                                $users_final = collect($to_emails_final['email'])
                                    ->filter()
                                    ->map(fn($email) => ['email' => trim($email)])
                                    ->toArray();

                                $cc_final = collect($cc_emails_final['email'])
                                    ->filter()
                                    ->map(fn($email) => ['email' => trim($email)])
                                    ->toArray();
                                // ==== YOUR ORIGINAL AUTO EMAIL LOGIC ENDS ====
                            }

                            Mail::to($users_final)
                                ->cc($cc_final ?? [])
                                ->send(new RequestCreated($service->id, $servicerequest, $customer, ""));
                        }
                    }
                } catch (\Exception $mailException) {
                    // Log error but DO NOT break request
                    Log::error('Mail sending failed for Service ID: '.$service->id);
                    Log::error($mailException);
                    // Continue execution without breaking API
                }
 
                $finalResponse[] = $respArr;

            } catch (\Exception $e) { 
                Log::error($e);
                return response()->json([
                    'status_code' => 200,
                    'message' => 'success'
                ], 200);
            }
        }
        return response()->json([
            'status_code' => 200,
            'message' => 'success',
            'data' => $finalResponse
        ], 200);

        //return response($finalResponse, 200)->header('Content-Type', 'text/plain');
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


    /*
       SEND FEEDBACK API
    */

    public function submitRequestFeedback(Request $request)
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
            'request_id' => 'required|integer',
            'response_speed' => 'nullable|integer|min:1|max:5',
            'quality_of_response' => 'nullable|integer|min:1|max:5',
            'app_experience' => 'nullable|integer|min:1|max:5',
            'olympus_staff_performance' => 'nullable|integer|min:1|max:5',
            'remarks' => 'nullable|string|max:1000',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        Logger("Submit Feedback");
        Logger($request->all());

        $requestId = $request->request_id;

        $servicerequest = ServiceRequests::find($requestId)
            ?? ArchiveServiceRequests::find($requestId);

        if (!$servicerequest) {
            return response()->json([
                'status_code' => 404,
                'message' => 'Service request not found'
            ], 404);
        }

        Logger($servicerequest);

        // -------------------------
        // Create / Update Feedback
        // -------------------------
        $feedback = Feedback::updateOrCreate(
            ['id' => $servicerequest->feedback_id],
            [
                'request_id' => $servicerequest->id,
                'response_speed' => $request->response_speed,
                'quality_of_response' => $request->quality_of_response,
                'app_experience' => $request->app_experience,
                'olympus_staff_performance' => $request->olympus_staff_performance,
                'remarks' => $request->remarks,
            ]
        );

        $servicerequest->feedback_id = $feedback->id;
        $servicerequest->save();

        // -------------------------
        // Optional SFDC
        // -------------------------
        
        if (env("SFDC_ENABLED") && $servicerequest->request_type === 'service') {
            try {
                $feedback->update(['sfdc_id' => $servicerequest->sfdc_id]);
                SFDC::submitFeedback($feedback);
            } catch (\Exception $e) {
                \Log::error('SFDC Failed: '.$e->getMessage());
            }
        }

        // -------------------------
        // Notify Customer
        // -------------------------
        try {
            $customer = Customers::find($servicerequest->customer_id);
            if ($customer) {
                NotifyCustomer::send_new_notification('feedback', $servicerequest, $customer);
            }
        } catch (\Exception $e) {
            \Log::error('Notification failed: '.$e->getMessage());
        }

        // -------------------------
        // Mail Section
        // -------------------------
        try {
            if (!$servicerequest->is_practice) {

                // LOCAL → override mail only
                if(env('APP_ENV') !== "production"){
                    $users_final = [['email' => 'ritik.bansal@lyxelandflamingo.com']];
                    $cc_final = [];
                } else { 

                    $hospital = Hospitals::find($servicerequest->hospital_id);
                    $department = Departments::find($servicerequest->dept_id);

                    if (!$hospital || !$department) {
                        throw new \Exception('Hospital or Department not found');
                    }

                    $to_emails = [];
                    $cc_emails = [];

                    if ($servicerequest->request_type == 'enquiry') {

                        $product_category_arr = explode(',', $servicerequest->product_category);

                        foreach ($product_category_arr as $index => $category) {

                            $category = trim($category);
                            $subType = null;

                            if ($category == 'Accessory') $subType = 'accessory';
                            if ($category == 'Capital Product') $subType = 'capital';
                            if ($category == 'Other') $subType = 'other';

                            if ($subType) {
                                $rules_list = AutoEmails::where('request_type', 'enquiry')
                                    ->where('sub_type', $subType)
                                    ->whereRaw("find_in_set(?, states)", [$hospital->state])
                                    ->whereRaw("find_in_set(?, departments)", [$department->name])
                                    ->first();

                                if ($rules_list) {
                                    $to_emails[] = explode(',', $rules_list->to_emails ?? '');
                                    $cc_emails[] = explode(',', $rules_list->cc_emails ?? '');
                                }
                            }
                        }

                        $to_emails = collect($to_emails)->flatten()->unique()->toArray();
                        $cc_emails = collect($cc_emails)->flatten()->unique()->toArray();

                    } else {

                        $rules_list = AutoEmails::where('request_type', $servicerequest->request_type)
                            ->whereRaw("find_in_set(?, states)", [$hospital->state])
                            ->whereRaw("find_in_set(?, departments)", [$department->name])
                            ->first();

                        if ($rules_list) {
                            $to_emails = explode(',', $rules_list->to_emails ?? '');
                            $cc_emails = explode(',', $rules_list->cc_emails ?? '');
                        }
                    }

                    if ($servicerequest->request_type != 'service') {
                        $cc_emails = array_merge($cc_emails, config('oly.enq_acad_coordinator_email', []));
                    }

                    $cc_emails = array_merge($cc_emails, config('oly.feedback_cc', []));

                    $users_final = collect($to_emails)
                        ->filter()
                        ->unique()
                        ->map(fn($email) => ['email' => trim($email)])
                        ->values()
                        ->toArray();

                    $cc_final = collect($cc_emails)
                        ->filter()
                        ->unique()
                        ->map(fn($email) => ['email' => trim($email)])
                        ->values()
                        ->toArray();

                    $assigned_person = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->first();
                    if ($assigned_person && $assigned_person->email) {
                        $users_final[] = ['email' => $assigned_person->email];
                    }
                }

                if (!empty($users_final)) { 
                    Mail::to($users_final)
                        ->cc($cc_final)
                        ->send(new FeedbackCreated($servicerequest->id, $servicerequest, $customer ?? null));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: '.$e->getMessage());

            return response()->json([
                'status_code' => 200,
                'message' => 'Feedback submitted successfully'
            ], 200);
        }

        return response()->json([
            'status_code' => 200,
            'message' => 'Feedback submitted successfully',
            'cvm_id' => $servicerequest->cvm_id,
            'data' => $servicerequest
        ], 200);
    }

    /*
       GET OPEN SERVICE REQUEST DATA API
    */

    public function getOpenServiceRequestList(Request $request)
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

        // -----------------------------
        // Fetch All Open Service Requests
        // -----------------------------
        $services = ServiceRequests::where('request_type', 'service')
            ->where('customer_id', $user->id)
            ->where('status', '!=', 'Closed')
            ->latest()
            ->get();

        if ($services->isEmpty()) {
            return response()->json([
                'message' => "Open service request list",
                'status_code' => 200,
                'history' => (object)['ongoingAry' => []]
            ]);
        }

        // -----------------------------
        // Preload Required Data (Bulk)
        // -----------------------------

        $hospitalIds = $services->pluck('hospital_id')->unique();
        $deptIds = $services->pluck('dept_id')->unique();
        $employeeCodes = $services->pluck('employee_code')->filter()->unique();
        $requestIds = $services->pluck('id');
        $customerId = $user->id;

        $hospitals = Hospitals::whereIn('id', $hospitalIds)
            ->pluck('hospital_name', 'id');

        $departments = Departments::whereIn('dept_id', $deptIds)
            ->pluck('name', 'dept_id');

        $employees = EmployeeTeam::whereIn('employee_code', $employeeCodes)
            ->get()
            ->keyBy('employee_code');

        $productInfo = ProductInfo::whereIn('service_requests_id', $requestIds)
            ->get()
            ->groupBy('service_requests_id');

        $technicalReports = TechnicalReport::whereIn('service_requests_id', $requestIds)
            ->get()
            ->groupBy('service_requests_id');

        $statusTimeline = StatusTimeline::where('customer_id', $customerId)
            ->whereIn('request_id', $requestIds)
            ->get()
            ->groupBy('request_id');

        $reminderHistory = RequestReminderHistory::where('customer_id', $customerId)
            ->whereIn('request_id', $requestIds)
            ->select('id','customer_id','request_id','status','created_at','updated_at')
            ->get()
            ->groupBy('request_id');

        // -----------------------------
        // Process Each Service
        // -----------------------------

        foreach ($services as $service) {

            // Hospital & Department
            $service->hospital_name = $hospitals[$service->hospital_id] ?? '-';
            $service->dept_name = $departments[$service->dept_id] ?? '-';

            // ---------------- Escalation ----------------
            $service->escalation_detail = [];
            $escalationArr = [];

            $esc_count = min($service->escalation_count ?? 0, 4);

            if (!empty($service->employee_code) && $esc_count > 0) {

                $emp_data = $employees[$service->employee_code] ?? null;

                if ($emp_data) {

                    for ($i = 1; $i <= $esc_count; $i++) {

                        $field = 'escalation_'.$i;

                        if (!empty($emp_data->$field)) {

                            $esc_detail = EmployeeTeam::where('email', $emp_data->$field)
                                ->select('name', 'email', 'mobile', 'image', 'designation')
                                ->first();

                            if ($esc_detail) {
                                $esc_detail->employee_image = $esc_detail->image
                                    ? config('app.url')."/storage/".$esc_detail->image
                                    : null;

                                $esc_detail->escalation_level = $i;

                                $escalationArr[] = $esc_detail; // ✅ push in normal array
                                //$service->escalation_detail[] = $esc_detail;
                            }
                        }
                    }
                }
            }

            // ✅ finally assign once
            //$service->escalation_detail = $escalationArr;
            $service->escalation_detail = collect($escalationArr);

            // ---------------- FSE Info ----------------
            $service->fseAry = collect();
            if (!empty($service->employee_code)) {

                $fse = $employees[$service->employee_code] ?? null;

                if ($fse) {
                    if (!empty($fse->image)) {
                        $fse->employee_image = config('app.url')."/storage/".$fse->image;
                    }
                    $service->fseAry = collect([$fse]);
                }
            }

            // ---------------- Product & Technical ----------------
            $service->product_info = $productInfo[$service->id] ?? collect();
            $service->technical_report = $technicalReports[$service->id] ?? collect();

            // ---------------- Timeline ----------------
            $timeline1 = $statusTimeline[$service->id] ?? collect();
            $timeline2 = $reminderHistory[$service->id] ?? collect();

            $service->timelineAry = collect(
                array_merge($timeline1->toArray(), $timeline2->toArray())
            )->sortByDesc('created_at')->values();

            // ---------------- Request Progress ----------------
            $service->request_progress = request_progress(
                $service->request_type,
                $service->status
            );
        }

        return response()->json([
            'message' => "Open service request list",
            'status_code' => 200,
            'history' => (object)[
                'ongoingAry' => $services
            ]
        ]);
    }

    /*
       CUSTOMER SUBMIT SERVICE REQUEST REMINDER API
    */

    public function customerSubmitServiceRequestReminder(Request $request){
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
            'request_id' => 'required|numeric|exists:service_requests,id',
            'message' => 'required|string|max:255'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        else{ 
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
        }
    }

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

    public function customerRequestAcknowledgement(Request $request)
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
            'request_id' => 'required|numeric|exists:service_requests,id',
            'acknowledgement_status' => 'required|numeric'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        else{ 
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
                 
        }
    }

    public function verifyRequestAcknowledgementHappyCode(Request $request){

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

        /* ================= VALIDATION ================= */

        $rules = [
            'happy_code' => 'required|numeric',
            'request_id' => 'required|numeric',
        ];

        $messages = [
            "happy_code.required"=>"happy code is required",
            "happy_code.numeric"=>"happy code will be integer",
            "request_id.required"=>"Request Id is required",
            "request_id.numeric"=>"Request Id is will be integer",
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }else{
            $request_id = $request->request_id;
            $happy_code = $request->happy_code; 
            
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
                 
        }
    }

    public function requestEscalate(Request $request)
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

        // ✅ Safe decode
        $reasonsInput = $request->reasons;

        $new_reason = $request->reasons;
        $reasons = json_decode($request->reasons);

        if (is_string($reasonsInput)) {
            $decoded = json_decode($reasonsInput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status_code' => 422,
                    'message' => 'reason must be an array'
                ], 422);
            }

            $request->merge(['reasons' => $decoded]);
        }

        if (!is_array($request->reasons)) {
            return response()->json([
                'status_code' => 422,
                'message' => 'reason must be an array'
            ], 422); 
        }

        $rules = [
            'request_id' => 'required|numeric',
            'reasons' => 'required|array',
            'remarks' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        else{
             
            $servicerequest = ServiceRequests::findOrFail($request->request_id); 

            // if(!empty($chk_service_request)){
            //     $servicerequest = ServiceRequests::findOrFail($request->request_id);
            // }else{
            //     $servicerequest = ArchiveServiceRequests::findOrFail($request->request_id);
            // } 

            /*if($servicerequest->request_type == "service"){
                if (isset($request->reasons)) {
                    $reasons = implode(',', json_decode($new_reason, true));
                    
                    $servicerequest->escalation_count = ($servicerequest->escalation_count > 3) ? 4 : $servicerequest->escalation_count + 1; 
                    $servicerequest->escalation_reasons = $reasons;
                    $servicerequest->is_escalated = 1;
                    $servicerequest->escalation_remarks = $request->remarks;

                    $servicerequest->save();

                    if(env("SFDC_ENABLED") && $servicerequest->request_type == 'service'){
                    
                        $SFDCCreateEscalation = SFDC::createEscalation($servicerequest);
                    }

                    //if(env("APP_ENV")  == 'staging'){

                        $to_emails = [];
                        $cc_emails = [];
                    
                        $final_to_list = [];
                        $final_cc_list = [];
                    
                        $escalation_count = $servicerequest->escalation_count;

                        if($escalation_count == 1){
                            //Escalation Count Person email
                            $to_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_1');
                            
                            //Assigned Person email
                            $cc_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('email');
                            
                            //Service Cordinator Email
                            $cc_emails = array_merge($cc_emails, explode(",", \Config('oly.service_coordinator_email')));

                            //Assigned Person Reporting Member Mail 
                            $reporting_person_of_assigned_employee = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('reporting_manager');
                            
                            $cc_emails = array_merge($cc_emails, explode(",", $reporting_person_of_assigned_employee));

                            //Escalation CC Email
                            $cc_emails = array_merge($cc_emails, \Config('oly.escalation_cc'));

                        }elseif($escalation_count == 2){
                            $to_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_2');
                            
                            $escalation_one_member_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_1');
                            
                            //Assigned Person email
                            $cc_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('email');
                            
                            //Service Cordinator Email
                            $cc_emails = array_merge($cc_emails, explode(",", \Config('oly.service_coordinator_email')));

                            //Assigned Person Reporting Member Mail 
                            $reporting_person_of_assigned_employee = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('reporting_manager');
                            
                            $cc_emails = array_merge($cc_emails, explode(",", $reporting_person_of_assigned_employee));

                            //Escalation CC Email
                            $cc_emails = array_merge($cc_emails, \Config('oly.escalation_cc'));
                            
                            $cc_emails = array_merge($cc_emails, $escalation_one_member_emails);


                        }elseif($escalation_count == 3){
                            $to_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_3');
                            

                            $escalation_one_member_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_1');


                            $escalation_two_member_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_2');

                            //Assigned Person email
                            $cc_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('email');
                            
                            //Service Cordinator Email
                            $cc_emails = array_merge($cc_emails, explode(",", \Config('oly.service_coordinator_email')));

                            //Assigned Person Reporting Member Mail 
                            $reporting_person_of_assigned_employee = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('reporting_manager');
                            
                            $cc_emails = array_merge($cc_emails, explode(",", $reporting_person_of_assigned_employee));

                            //Escalation CC Email
                            $cc_emails = array_merge($cc_emails, \Config('oly.escalation_cc'));

                            $cc_emails = array_merge($cc_emails, $escalation_one_member_emails);

                            $cc_emails = array_merge($cc_emails, $escalation_two_member_emails);


                            //Service Level Escalation 3 Email
                            $cc_emails = array_merge($cc_emails, explode(",", \Config('oly.service_level_3_esc')));

                        }elseif($escalation_count == 4){
                            $to_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_4');
                            
                            $escalation_one_member_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_1');


                            $escalation_two_member_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('escalation_2');

                            //Assigned Person email
                            $cc_emails = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('email');
                            
                            //Service Cordinator Email
                            $cc_emails = array_merge($cc_emails, explode(",", \Config('oly.service_coordinator_email')));

                            //Assigned Person Reporting Member Mail 
                            $reporting_person_of_assigned_employee = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->value('reporting_manager');
                            
                            $cc_emails = array_merge($cc_emails, explode(",", $reporting_person_of_assigned_employee));

                            //Escalation CC Email
                            $cc_emails = array_merge($cc_emails, \Config('oly.escalation_cc'));

                            $cc_emails = array_merge($cc_emails, $escalation_one_member_emails);

                            $cc_emails = array_merge($cc_emails, $escalation_two_member_emails);


                            //Service Level Escalation 3 Email
                            $cc_emails = array_merge($cc_emails, explode(",", \Config('oly.service_level_3_esc')));

                        }
         
                    //}

                      
                    $servicerequest->save();
                    
                    $status = new StatusTimeline;
                    $status->status ='Escalated';
                    $status->customer_id = $servicerequest->customer_id;
                    $status->request_id = $servicerequest->id;
                    $status ->save();

                    $customer = Customers::findOrFail($servicerequest->customer_id);

                    $respArr['status_code'] = 200;
                    $respArr['cvm_id'] = $servicerequest->cvm_id;
                    $respArr['data'] = $servicerequest;
                    
                    NotifyCustomer::send_new_notification('request_escalate', $servicerequest, $customer);
                     
                    if(env('APP_ENV') == "production"){

                        Mail::to($final_to_list)->cc($final_cc_list)
                            ->send(new RequestEscalated($request->request_id, $servicerequest, $customer));

                    }else{

                        Mail::to('ritik.bansal@lyxelandflamingo.com')
                            ->send(new RequestEscalated($request->request_id, $servicerequest, $customer));

                    }

                    return response()->json([
                        'status_code' => 200,
                        'message' => 'Request escalated successfully'
                    ], 200);  

                } else {
                    return response()->json([
                        'status_code' => 422,
                        'message' => 'No reasons for escalation selected. Please select at least one reason for escalation'
                    ], 422); 
                }
            }*/
            if($servicerequest->request_type == "service"){
                if (isset($request->reasons)) {

                    // Convert reasons array into comma separated string
                    $reasons = implode(',', json_decode($new_reason, true));

                    // Increase escalation count but maximum allowed is 4
                    $servicerequest->escalation_count =
                        ($servicerequest->escalation_count >= 4)
                        ? 4
                        : $servicerequest->escalation_count + 1;

                    $servicerequest->escalation_reasons = $reasons;
                    $servicerequest->is_escalated = 1;
                    $servicerequest->escalation_remarks = $request->remarks;

                    $servicerequest->save();


                    // ===============================
                    // SFDC escalation
                    // ===============================
                    if (env("SFDC_ENABLED") && $servicerequest->request_type == 'service') {

                        SFDC::createEscalation($servicerequest);

                    }

                    // ===============================
                    // Initialize arrays
                    // ===============================
                    $to_emails = [];
                    $cc_emails = [];

                    $escalation_count = $servicerequest->escalation_count;

                    // ===============================
                    // Get employee data
                    // ===============================

                    $employee = EmployeeTeam::where(
                        'employee_code',
                        $servicerequest->employee_code
                    )->first();

                    if ($employee) {

                        // ===============================
                        // ESCALATION LEVEL 1
                        // ===============================
                        if ($escalation_count == 1) {

                            // escalation person
                            $to_emails = makeEmailArray($employee->escalation_1);


                            // assigned person
                            $cc_emails = makeEmailArray($employee->email);


                            // service coordinator
                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.service_coordinator_email'))
                            );


                            // reporting manager
                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->reporting_manager)
                            );


                            // escalation cc config
                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.escalation_cc'))
                            );

                            $servicerequest->escalation_assign1 = $employee->escalation_1;
                            $servicerequest->save();

                        }

                        // ===============================
                        // ESCALATION LEVEL 2
                        // ===============================
                        elseif ($escalation_count == 2) {

                            $to_emails = makeEmailArray($employee->escalation_2);

                            $cc_emails = makeEmailArray($employee->email);

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.service_coordinator_email'))
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->reporting_manager)
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.escalation_cc'))
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->escalation_1)
                            );


                            $servicerequest->escalation_assign2 = $employee->escalation_2;
                            $servicerequest->save();

                        }

                        // ===============================
                        // ESCALATION LEVEL 3
                        // ===============================
                        elseif ($escalation_count == 3) {

                            $to_emails = makeEmailArray($employee->escalation_3);

                            $cc_emails = makeEmailArray($employee->email);

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.service_coordinator_email'))
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->reporting_manager)
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.escalation_cc'))
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->escalation_1)
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->escalation_2)
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.service_level_3_esc'))
                            );


                            $servicerequest->escalation_assign3 = $employee->escalation_3;
                            $servicerequest->save();

                        }

                        // ===============================
                        // ESCALATION LEVEL 4
                        // ===============================
                        elseif ($escalation_count == 4) {

                            $to_emails = makeEmailArray($employee->escalation_4);

                            $cc_emails = makeEmailArray($employee->email);

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.service_coordinator_email'))
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->reporting_manager)
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.escalation_cc'))
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->escalation_1)
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray($employee->escalation_2)
                            );

                            $cc_emails = array_merge(
                                $cc_emails,
                                makeEmailArray(config('oly.service_level_3_esc'))
                            );


                            $servicerequest->escalation_assign4 = $employee->escalation_4;
                            $servicerequest->save();
                        }
                    } 

                    // ===============================
                    // Remove duplicate and empty emails
                    // ===============================
                    $final_to_list = array_unique(array_filter($to_emails));

                    $final_cc_list = array_unique(array_filter($cc_emails));
 

                    // ===============================
                    // Save status timeline
                    // ===============================
 
                    $status = new StatusTimeline;
                    $status->status = 'Escalated';
                    $status->customer_id = $servicerequest->customer_id;
                    $status->request_id = $servicerequest->id;
                    $status->save(); 

                    // ===============================
                    // Customer notification
                    // ===============================
                    $customer = Customers::findOrFail($servicerequest->customer_id);

                    NotifyCustomer::send_new_notification( 'request_escalate', $servicerequest, $customer); 

                    // ===============================
                    // MAIL SEND LOGIC
                    // ===============================
                    // production → actual emails
                    // local/staging → ritik email
                    // ===============================

                    //Logger($final_to_list, $final_cc_list);
                    
                    if(env('APP_ENV') == "production"){
                        Mail::to($final_to_list)
                            ->cc($final_cc_list)
                            ->send(
                                new RequestEscalated(
                                    $request->request_id,
                                    $servicerequest,
                                    $customer
                                )
                            );
                    } else {
                        Mail::to('ritik.bansal@lyxelandflamingo.com')
                            ->send(
                                new RequestEscalated(
                                    $request->request_id,
                                    $servicerequest,
                                    $customer
                                )
                            );
                    }

                    return response()->json([
                        'status_code' => 200,
                        'message' => 'Request escalated successfully'
                    ], 200);

                } else {
                    
                    return response()->json([
                        'status_code' => 422,
                        'message' => 'No reasons for escalation selected. Please select at least one reason for escalation'
                    ], 422);
                
                }
            }else{
                if (isset($request->reasons)) {
                    $reasons = implode(',', json_decode($new_reason, true));
                    
                    $servicerequest->escalation_count = ($servicerequest->escalation_count > 3) ? 4 : $servicerequest->escalation_count + 1; 
                    $servicerequest->escalation_reasons = $reasons;
                    $servicerequest->is_escalated = 1;
                    $servicerequest->escalation_remarks = $request->remarks;

                    $servicerequest->save();

                    // if(env("SFDC_ENABLED") && $servicerequest->request_type == 'service'){
                    
                    //     $SFDCCreateEscalation = SFDC::createEscalation($servicerequest);
                    // }

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
                        
                        // convert to_emails safely to array (handles string, array, null)
                        if (is_array($to_emails)) {

                            // already array → use as it is
                            $to_emails = $to_emails;

                        } elseif (is_string($to_emails) && !empty($to_emails)) {

                            // string → convert to array
                            $to_emails = explode(",", $to_emails);

                        } else {

                            // null or empty
                            $to_emails = [];

                        }

                        //$to_emails = explode(",", $to_emails);
                    //}

                   
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
                    
                    $status = new StatusTimeline;
                    $status->status ='Escalated';
                    $status->customer_id = $servicerequest->customer_id;
                    $status->request_id = $servicerequest->id;
                    $status ->save();

                    $customer = Customers::findOrFail($servicerequest->customer_id);

                    $respArr['status_code'] = 200;
                    $respArr['cvm_id'] = $servicerequest->cvm_id;
                    $respArr['data'] = $servicerequest;
                    
                    NotifyCustomer::send_new_notification('request_escalate', $servicerequest, $customer);
                    Logger($final_to_list, $final_cc_list);
                    
                    if(env('APP_ENV') == "production"){

                        Mail::to($final_to_list)->cc($final_cc_list)
                            ->send(new RequestEscalated($request->request_id, $servicerequest, $customer));

                    }else{

                        Mail::to('ritik.bansal@lyxelandflamingo.com')
                            ->send(new RequestEscalated($request->request_id, $servicerequest, $customer));

                    }

                    return response()->json([
                        'status_code' => 200,
                        'message' => 'Request escalated successfully'
                    ], 200);  

                } else {
                    return response()->json([
                        'status_code' => 422,
                        'message' => 'No reasons for escalation selected. Please select at least one reason for escalation'
                    ], 422); 
                }
            }     
        }
    }

}
