<?php
namespace App;

use App\Models\EmployeeTeam;
use App\Services\FCMService;
use FCM;
use GuzzleHttp\Exception\RequestException;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Log;
use Carbon\Carbon;

class NotifyCustomer
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public static function send_notification_old($type, $servicerequest, $customer, $args = [])
    {
        if(!env('NOTIFICATION_ENABLED')){
            return true; //DO NOT SEND NOTIFICATION TO CUSTOMER
        }

        // Default settings for iOS
        if(!empty($servicerequest)){
            $created_at = $servicerequest->created_at->toDateTimeString();
            $assigned_employee = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->first();

            if(is_null($assigned_employee)){
                $assigned_employee = (object)[];
                $assigned_employee->name = "Dummy";
                $assigned_employee->image = config('app.url')."/storage/shared/employee_image.jpg";
            }
        }
        // if(!empty($customer)){
        //     $customer = Customers::find($servicerequest->customer_id);
        // }


        switch ($type) {
            case 'request_create':
                $text_etype = ($servicerequest->request_type=='service') ? "Our Engineer" : "Our Executive" ;
                $nt_title = "Request Created";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', We have received your request with the request ID: '.sprintf('%08d', $servicerequest->id).'. '.$text_etype.' will reach out to you shortly';
                $nt_type = "request_status";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => $servicerequest->id,
                        'request_type'=>$servicerequest->request_type,
                        'remarks'=>$servicerequest->remarks,
                        'status'=>$servicerequest->status,
                    ];
                }else{
                    $data = [
                        'notification_type' => $nt_type,
                        'request_id' => $servicerequest->id,
                        'request_type'=>$servicerequest->request_type,
                        'remarks'=>$servicerequest->remarks,
                        'status'=>$servicerequest->status,
                    ];
                }
                break;

            case 'request_update':
                $nt_title = "Request Updated";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', The status of your request ID: '.sprintf('%08d', $servicerequest->id).' has been updated to "'.$servicerequest->status.'".';
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => $servicerequest->id,
                    'request_type'=>$servicerequest->request_type,
                    'sub_type'=>$servicerequest->sub_type,
                    'remarks'=>$servicerequest->remarks,
                    'status'=>$servicerequest->status,
                    'feedback_flag'=>$servicerequest->feedback_id,
                    'employee_name'=>$assigned_employee->name,
                    'assigned_image'=>config('app.url')."/storage/".$assigned_employee->image,
                    'created_at'=>$created_at
                ];
                break;

            case 'request_escalate':
                $nt_title = "Request Escalated";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', We are extremely sorry that you had to escalate the request ID: '.sprintf('%08d', $servicerequest->id).'. We assure you that we are committed to providing you a top-notch experience. Please allow us some time to investigate the issue. Thank you';
                $nt_type = "request_status";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => $servicerequest->id,
                        'request_type'=>$servicerequest->request_type,
                        'remarks'=>$servicerequest->remarks,
                        'status'=>$servicerequest->status,
                    ];
                }else{
                    $data = [
                        'notification_type' => $nt_type,
                        'request_id' => $servicerequest->id,
                        'request_type'=>$servicerequest->request_type,
                        'remarks'=>$servicerequest->remarks,
                        'status'=>$servicerequest->status,
                    ];
                }
                break;

            case 'request_technical_report':
                $nt_title = "Technical Report Available";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', The technical report of your request ID: '.sprintf('%08d', $servicerequest->id).' has been uploaded.';
                $nt_type = "request_status";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => $servicerequest->id,
                        'request_type'=>$servicerequest->request_type,
                        'remarks'=>$servicerequest->remarks,
                        'status'=>$servicerequest->status,
                    ];
                }else{
                    $data = [
                        'notification_type' => $nt_type,
                        'request_id' => $servicerequest->id,
                        'request_type'=>$servicerequest->request_type,
                        'remarks'=>$servicerequest->remarks,
                        'status'=>$servicerequest->status,
                    ];
                }
                break;

            case 'feedback_pending':
                $nt_title = "Please Share Feedback";
                $nt_body = 'How was your experience with Olympus? Please share your feedback for request number '.$servicerequest->id.'.';
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => $servicerequest->id,
                    'request_type'=>$servicerequest->request_type,
                    'sub_type'=>$servicerequest->sub_type,
                    'remarks'=>$servicerequest->remarks,
                    'status'=>$servicerequest->status,
                    'feedback_flag'=>$servicerequest->feedback_id,
                    'created_at'=>$created_at
                ];
                break;

            case 'promailer_publish':
                $nt_title = $servicerequest->nt_title;
                $nt_body = $servicerequest->nt_body;
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => 0,
                    'request_type'=>null,
                    'sub_type'=>null,
                    'remarks'=>null,
                    'status'=>"Created",
                    'feedback_flag'=>null,
                    'employee_name'=>null,
                    'assigned_image'=>"shared/employee_image.jpg",
                    'created_at'=>now()
                ];
                break;

            case 'video_publish':
                //$nt_title = "My Voice App- A New Feature Released";
                $nt_title = $args->nt_title;
                $nt_body = $args->nt_description;
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => 0,
                    'request_type'=>null,
                    'sub_type'=>null,
                    'remarks'=>null,
                    'status'=>"Created",
                    'feedback_flag'=>null,
                    'employee_name'=>null,
                    'assigned_image'=>"shared/employee_image.jpg",
                    'created_at'=>now()
                ];
                break;

            case 'app_update_available':
                $nt_title = "New Update Available";
                $nt_body = 'New app update is available, please update your App to enjoy latest features. Your existing data will not be lost.';
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => 0,
                    'request_type'=>null,
                    'sub_type'=>null,
                    'remarks'=>null,
                    'status'=>"Created",
                    'feedback_flag'=>null,
                    'employee_name'=>null,
                    'assigned_image'=>"shared/employee_image.jpg",
                    'created_at'=>now()
                ];
                break;

            case 'request_type_changed':
                $nt_title = "Request Type Changed";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', Thanks for your request. Based on your remarks, type of your call has been changed.Kindly check your further details in History Tab in Ongoing Calls Section. ';
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => $servicerequest->id,
                    'request_type'=>$servicerequest->request_type,
                    'sub_type'=>$servicerequest->sub_type,
                    'remarks'=>$servicerequest->remarks,
                    'status'=>$servicerequest->status,
                    'feedback_flag'=>$servicerequest->feedback_id,
                    'employee_name'=>$assigned_employee->name,
                    'assigned_image'=>config('app.url')."/storage/".$assigned_employee->image,
                    'created_at'=>$created_at
                ];
                break;

            case 'feedback':
                $nt_title = "Feedback Submitted";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', Thank you for sharing your feedback for the request ID: '.sprintf('%08d', $servicerequest->id).'. Your feedback is valuable to us.';
                $nt_type = "feedback";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => $servicerequest->id,
                        'request_type'=>$servicerequest->request_type,
                        'remarks'=>$servicerequest->remarks,
                    ];
                }else{
                    $data = [
                        'notification_type' => $nt_type,
                        'request_id' => $servicerequest->id,
                        'request_type'=>$servicerequest->request_type,
                    ];
                }
                break;

            case 'account_update':
                $nt_title = "Profile Updated";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', Your Olympus My Voice profile has been updated.';
                $nt_type = "account_update";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => 0,
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'customer_id' => $customer->id,
                        'notification_type' => $nt_type
                    ];
                }
                break;

            case 'password_expired':
                $nt_title = "Password Expired";
                $nt_body = 'You have not changed your password since 90 or more days. Please reset your password now.';
                $nt_type = "password_expired";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => 0,
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
                break;

            case 'remind_password_expired':
                $nt_title = "Password Expired Reminder";
                $nt_body = 'Your password will expired with in 7 days. Please change your password before password expiration.';
                $nt_type = "remind_password_expired";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => 0,
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
                break;

            case 'remind_password_expired_before_10_days':
                $nt_title = "Password Expired Reminder";
                $nt_body = 'Your password will expired with in 10 days. Please change your password before password expiration.';
                $nt_type = "remind_password_expired_before_10_days";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => 0,
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
                break;

            case 'remind_password_expired_before_3_days':
                $nt_title = "Password Expired Reminder";
                $nt_body = 'Your password will expired with in 3 days. Please change your password before password expiration.';
                $nt_type = "remind_password_expired_before_3_days";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => 0,
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
                break;


            case 'send_app_update_notification':
                $nt_title = "New update available";
                $nt_body = "Dear customer! We've enhanced our app's security for your data's safety. Please install the latest version of the app.";
                $nt_type = "send_app_update_notification";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => 0,
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
                break;

        }
        if (isset($customer->device_token)) {

            $notificationBuilder = new PayloadNotificationBuilder($nt_title);
            $notificationBuilder->setBody($nt_body)
                ->setSound('default');
            $notification = $notificationBuilder->build();
            if ($customer->platform =='android') {
                $data1 = $data;
                $data = array();
                $data['data'] = $data1;
                $notification = null;
            }
            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60*20);
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData($data);
            $data = $dataBuilder->build();
            $option = $optionBuilder->build();
            $token = $customer->device_token;
            $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
            $downstreamResponse->numberSuccess();
            $downstreamResponse->numberFailure();
            $downstreamResponse->numberModification();
            $downstreamResponse->tokensToDelete();
            $downstreamResponse->tokensToModify();
            $downstreamResponse->tokensToRetry();
        }
        return true;
    }

    public static function send_notification($type, $servicerequest, $customer, $args = [])
    {
        if(!env('NOTIFICATION_ENABLED')){
            return true; //DO NOT SEND NOTIFICATION TO CUSTOMER
        }

        // Default settings for iOS
        if(!empty($servicerequest)){
           $created_at = $servicerequest->created_at instanceof \Carbon\Carbon ? $servicerequest->created_at->toDateTimeString(): Carbon::parse($servicerequest->created_at)->toDateTimeString();
           $assigned_employee = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->first();

            if(is_null($assigned_employee)){
                $assigned_employee = (object)[];
                $assigned_employee->name = "Dummy";
                $assigned_employee->image = config('app.url')."/storage/shared/employee_image.jpg";
            }
        }

        //---------------------------------------------------------

            if($type == 'request_create'){
                $data = [];
                $text_etype = ($servicerequest->request_type=='service') ? "Our Engineer" : "Our Executive" ;
                $nt_title = "Request Created";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', We have received your request with the request ID: '.sprintf('%08d', $servicerequest->id).'. '.$text_etype.' will reach out to you shortly';
                $nt_type = "request_status";
                if ($customer->platform =='android') {
                    $data = [
                        'noti_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => (string)$servicerequest->id,
                        'request_type'=> (string)$servicerequest->request_type,
                        'remarks'=>(string)$servicerequest->remarks,
                        'status'=>(string)$servicerequest->status,
                    ];
                }else{
                    $data = [
                        'noti_type' => $nt_type,
                        'request_id' => (string)$servicerequest->id,
                        'request_type'=>(string)$servicerequest->request_type,
                        'remarks'=>(string)$servicerequest->remarks,
                        'status'=>(string)$servicerequest->status,
                    ];
                }
            }elseif($type == 'request_update'){
                $nt_title = "Request Updated";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', The status of your request ID: '.sprintf('%08d', $servicerequest->id).' has been updated to "'.$servicerequest->status.'".';
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => (string)$servicerequest->id,
                    'request_type'=>(string)$servicerequest->request_type,
                    'sub_type'=>(string)$servicerequest->sub_type,
                    'remarks'=>(string)$servicerequest->remarks,
                    'status'=>(string)$servicerequest->status,
                    'feedback_flag'=>(string)$servicerequest->feedback_id,
                    'employee_name'=>(string)$assigned_employee->name,
                    'assigned_image'=>config('app.url')."/storage/".$assigned_employee->image,
                    'created_at'=>$created_at
                ];
            }elseif($type == 'request_escalate'){
                $nt_title = "Request Escalated";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', We are extremely sorry that you had to escalate the request ID: '.sprintf('%08d', $servicerequest->id).'. We assure you that we are committed to providing you a top-notch experience. Please allow us some time to investigate the issue. Thank you';
                $nt_type = "request_status";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => (string)$servicerequest->id,
                        'request_type'=>(string)$servicerequest->request_type,
                        'remarks'=>(string)$servicerequest->remarks,
                        'status'=>(string)$servicerequest->status,
                    ];
                }else{
                    $data = [
                        'notification_type' => $nt_type,
                        'request_id' => (string)$servicerequest->id,
                        'request_type'=>(string)$servicerequest->request_type,
                        'remarks'=>(string)$servicerequest->remarks,
                        'status'=>(string)$servicerequest->status,
                    ];
                }
            }elseif($type == 'request_technical_report'){
                $nt_title = "Technical Report Available";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', The technical report of your request ID: '.sprintf('%08d', $servicerequest->id).' has been uploaded.';
                $nt_type = "request_status";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => (string)$servicerequest->id,
                        'request_type'=>(string)$servicerequest->request_type,
                        'remarks'=>(string)$servicerequest->remarks,
                        'status'=>(string)$servicerequest->status,
                    ];
                }else{
                    $data = [
                        'notification_type' => $nt_type,
                        'request_id' => (string)$servicerequest->id,
                        'request_type'=>(string)$servicerequest->request_type,
                        'remarks'=>(string)$servicerequest->remarks,
                        'status'=>(string)$servicerequest->status,
                    ];
                }
            }elseif($type == 'feedback_pending'){
                $nt_title = "Please Share Feedback";
                $nt_body = 'How was your experience with Olympus? Please share your feedback for request number '.$servicerequest->id.'.';
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => (string)$servicerequest->id,
                    'request_type'=>(string)$servicerequest->request_type,
                    'sub_type'=>(string)$servicerequest->sub_type,
                    'remarks'=>(string)$servicerequest->remarks,
                    'status'=>(string)$servicerequest->status,
                    'feedback_flag'=>(string)$servicerequest->feedback_id,
                    'created_at'=>$created_at
                ];
            }elseif($type == 'promailer_publish_old'){
                $nt_title = $servicerequest->nt_title;
                $nt_body = $servicerequest->nt_body;
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => "0",
                    'request_type'=>null,
                    'sub_type'=>null,
                    'remarks'=>null,
                    'status'=>"Created",
                    'feedback_flag'=>null,
                    'employee_name'=>null,
                    'assigned_image'=>"shared/employee_image.jpg",
                    'created_at'=>now()
                ];
            }elseif($type == 'video_publish'){
                $nt_title = $args->nt_title;
                $nt_body = $args->nt_description;
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => "0",
                    'request_type'=>null,
                    'sub_type'=>null,
                    'remarks'=>null,
                    'status'=>"Created",
                    'feedback_flag'=>null,
                    'employee_name'=>null,
                    'assigned_image'=>"shared/employee_image.jpg",
                    'created_at'=>now()
                ];
            }elseif($type == 'app_update_available'){
                $nt_title = "New Update Available";
                $nt_body = 'New app update is available, please update your App to enjoy latest features. Your existing data will not be lost.';
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => "0",
                    'request_type'=>null,
                    'sub_type'=>null,
                    'remarks'=>null,
                    'status'=>"Created",
                    'feedback_flag'=>null,
                    'employee_name'=>null,
                    'assigned_image'=>"shared/employee_image.jpg",
                    'created_at'=>now()
                ];
            }elseif($type == 'request_type_changed'){
                $nt_title = "Request Type Changed";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', Thanks for your request. Based on your remarks, type of your call has been changed.Kindly check your further details in History Tab in Ongoing Calls Section. ';
                $nt_type = "feedback";
                $data = [
                    'notification_type' => $nt_type,
                    'title'=>$nt_title,
                    'message'=>$nt_body,
                    'request_id' => (string)$servicerequest->id,
                    'request_type'=>(string)$servicerequest->request_type,
                    'sub_type'=>(string)$servicerequest->sub_type,
                    'remarks'=>(string)$servicerequest->remarks,
                    'status'=>(string)$servicerequest->status,
                    'feedback_flag'=>(string)$servicerequest->feedback_id,
                    'employee_name'=>(string)$assigned_employee->name,
                    'assigned_image'=>config('app.url')."/storage/".$assigned_employee->image,
                    'created_at'=>$created_at
                ];
            }elseif($type == 'feedback'){
                $nt_title = "Feedback Submitted";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', Thank you for sharing your feedback for the request ID: '.sprintf('%08d', $servicerequest->id).'. Your feedback is valuable to us.';
                $nt_type = "feedback";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' =>(string)$servicerequest->id,
                        'request_type'=>(string)$servicerequest->request_type,
                        'remarks'=>(string)$servicerequest->remarks,
                    ];
                }else{
                    $data = [
                        'notification_type' => $nt_type,
                        'request_id' => (string)$servicerequest->id,
                        'request_type'=>(string)$servicerequest->request_type,
                    ];
                }
            }elseif($type =='account_update'){
                $nt_title = "Profile Updated";
                $nt_body = 'Dear '.$customer->title.' '.$customer->first_name.' '.$customer->last_name.', Your Olympus My Voice profile has been updated.';
                $nt_type = "account_update";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'customer_id' => (string)$customer->id,
                        'notification_type' => $nt_type
                    ];
                }
            }elseif($type =='password_expired'){
                $nt_title = "Password Expired";
                $nt_body = 'You have not changed your password since 90 or more days. Please reset your password now.';
                $nt_type = "password_expired";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
            }elseif($type =='remind_password_expired'){
                $nt_title = "Password Expired Reminder";
                $nt_body = 'Your password will expired with in 7 days. Please change your password before password expiration.';
                $nt_type = "remind_password_expired";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
            }elseif($type =='remind_password_expired_before_10_days'){
                $nt_title = "Password Expired Reminder";
                $nt_body = 'Your password will expired with in 10 days. Please change your password before password expiration.';
                $nt_type = "remind_password_expired_before_10_days";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
            }elseif($type =='remind_password_expired_before_3_days'){
                $nt_title = "Password Expired Reminder";
                $nt_body = 'Your password will expired with in 3 days. Please change your password before password expiration.';
                $nt_type = "remind_password_expired_before_3_days";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
            }elseif($type =='send_app_update_notification'){
                $nt_title = "New Feature Alert!";
                $nt_body = "Post-repair delivery acknowledgement is now available! Update your app to access this feature and stay on top of your requests effortlessly. Update now!";
                $nt_type = "send_app_update_notification";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
            }elseif($type =='promailer_publish'){
                $nt_title = "iTind Connect";
                $nt_body = "iTind Connect.";
                $nt_type = "promailer_publish";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                        'assigned_image'=>"shared/employee_image.jpg",
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type,
                        'assigned_image'=>"shared/employee_image.jpg",
                    ];
                }
            }elseif($type =='request_acknowledgement_after_3_days'){
                $nt_title = "Request Acknowledgement Reminder";
                $nt_body = 'Dear Customer, Gentle reminder, Repaired equipment has been delivered to you. Please provide the Post Delivery Acknowledgment Code to close the Service Request ID No.' .$servicerequest->id. '. Thank you!.';
                $nt_type = "request_acknowledgement_after_3_days";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
            }elseif($type =='request_acknowledgement_after_5_days'){
                $nt_title = "Request Acknowledgement Reminder";
                $nt_body = 'Dear Customer, The repaired equipment has been delivered to you. Kindly provide the Post Delivery Acknowledgment Code to close the Service Request ID No.' .$servicerequest->id. '. If no response is received by EOD, the call will be auto-closed, considering it as your acknowledgment. Thank you!.';
                $nt_type = "request_acknowledgement_after_5_days";
                if ($customer->platform =='android') {
                    $data = [
                        'notification_type' => $nt_type,
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_id' => "0",
                        'request_type'=>null,
                        'sub_type'=>null,
                        'remarks'=>null,
                        'status'=>null,
                        'feedback_flag'=>null,
                    ];
                }else{
                    $data = [
                        'title'=>$nt_title,
                        'message'=>$nt_body,
                        'request_type' => $nt_type
                    ];
                }
            }

        //---------------------------------------------------------

        if (isset($customer->device_token)) {
            $deviceToken = $customer->device_token;

            //$deviceToken ='cmPPuNxGJKM:APA91bGS0RZIAWnoS1CicnDWVWlKZXevhFezgNRB3yRWDvaW522KXPSsDVStP_xGhki6x2LVkq2umzYmT7gzqmHgvLhDGqquGf6X2Yk5BGf_35F_RQJ8274OFenCxnHotLlkUwNeTb77';

            $notification = array(
                'title' => $nt_title,
                'body' => $nt_body,
            );

            $fcmService = new FCMService();

            $responses = $fcmService->sendMessage($deviceToken, $notification, $data);
            foreach ($responses as $response) {
                /*
                if ($response['state'] === 'fulfilled') {
                    Log::channel('single')->info('Message sent successfully: ' . $response['value']->getBody() . PHP_EOL);
                } else {
                    $reason = $response['reason'];
                    if ($reason instanceof RequestException) {
                        Log::channel('single')->info('Request failed: ' . $reason->getMessage() . PHP_EOL);
                        if ($reason->hasResponse()) {
                            Log::channel('single')->info('Response: ' . $reason->getResponse()->getBody() . PHP_EOL);
                        }
                    } else {
                        Log::channel('single')->info('Failed to send message: ' . $reason->getMessage() . PHP_EOL);
                    }
                }*/
            }
            return true;
        }
    }
}


