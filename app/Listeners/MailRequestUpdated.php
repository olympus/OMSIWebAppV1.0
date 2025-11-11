<?php

namespace App\Listeners;

use App\Models\Departments;
use App\Events\RequestStatusUpdated;
use App\Mail\RequestUpdated;
use App\Models\AutoEmails;
use App\Models\EmployeeTeam;
use App\Models\Hospitals;
use DB;
use Mail;


class MailRequestUpdated
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RequestStatusUpdated  $event
     * @return void
     */
    public function handle(RequestStatusUpdated $event)
    {
        $servicerequest = $event->servicerequest;
        $oldData = $event->oldData;
        $customer = $event->customer;
        $oldData_employee_code = null;
        session()->forget('oldData_employee_code');
        if ($oldData->employee_code != $servicerequest->employee_code) {
            session(['oldData_employee_code' => $oldData->employee_code]);
            $oldData_employee_code = $oldData->employee_code;
        }

        // \Log::info('TEST',['data'=>$event->servicerequest]);

        $hospital_state = Hospitals::where('id',$servicerequest->hospital_id)->value('state');
        $department_name = Departments::where('id',$servicerequest->dept_id)->value('name');

        if ($servicerequest->request_type=='enquiry') {
            $product_category_arr = explode(',', rtrim($servicerequest->product_category, ','));
            for ($i=0; $i < sizeof($product_category_arr); $i++) {
                $subtype = get_enq_type($product_category_arr[$i]);

                $rules_list = AutoEmails::where('request_type', $servicerequest->request_type)
                    ->where('sub_type', $subtype)
                    ->where("states","like","%$hospital_state%")
                    ->where("departments","like","%$department_name%")
                    ->first();

                $to_emails[$i] = explode(',', $rules_list->to_emails);
                $cc_emails[$i] = explode(',', $rules_list->cc_emails);
            }
            $to_emails_final = collect($to_emails)->flatten()->toArray();
            $cc_emails_final = collect($cc_emails)->flatten()->toArray();
        } else {
            $subtype = "";
            $rules_list = AutoEmails::where('request_type', $servicerequest->request_type)
                    ->where("states","like","%$hospital_state%")
                    ->where("departments","like","%$department_name%")
                    ->first();

            if (!is_null($rules_list)) {
                $to_emails_final = explode(',', $rules_list->to_emails);
                $cc_emails_final = explode(',', $rules_list->cc_emails);
            } else {
                $to_emails_final = [];
                $cc_emails_final = [];
            }
        }
        if ($servicerequest->request_type!='service') {
            $cc_emails_final = array_merge($cc_emails_final,\Config('oly.enq_acad_coordinator_email'));
        }
        $to = toMailKey($to_emails_final);
        $cc = toMailKey($cc_emails_final);

        if ($oldData->employee_code != $servicerequest->employee_code) {
            $to[]['email'] = EmployeeTeam::where('employee_code', $oldData->employee_code)->first()->email;
        }
        $assigned_person = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->first();
        if($assigned_person){
            $to[]['email'] = $assigned_person->email;
        }

        //$pathToImage = json_decode(file_get_contents(env('APP_URL').'/capture_screenshot/'.$servicerequest->id.'/updated?oldData_employee_code='.$oldData_employee_code));
        if(env('APP_ENV') != "staging"){
            Mail::to($to)->cc($cc)
            ->send(new RequestUpdated($servicerequest->id, $oldData_employee_code, $servicerequest, $customer));
        }
    }
}
