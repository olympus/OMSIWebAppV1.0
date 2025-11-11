<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use App\Exports\PendingRequestsExport;
use App\Exports\PendingWeekLateExport;
use App\Exports\CustomerListExport;
use App\Exports\FeedbackReportExport;
use App\Exports\EscalationReportExport;
use App\Exports\VoiceOfCustomerExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReceivedRequests;
use App\Mail\DailyPendingWeekLate;
use App\Mail\MonthlyCustomerList;
use App\Mail\MonthlyFeedBackReport;
use App\Models\ServiceRequests;
use App\Models\Reportsetting;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\Departments;
use App\Models\Feedback;
use App\Models\EmployeeTeam;
use App\Models\StatusTimeline;
use App\Mail\WeeklyEscalationReport;
use App\Mail\WeeklyMis;

class NewReportsController extends Controller
{
    //Daily Pending Requests Report AT 5 PM

    public function pending_requests_report()
    {
        $excel_array = [];
        $date_today=  date('d-m-Y', strtotime('today', time()));
        $excelname = 'Pending Requests_'.$date_today.'.xls';
        $excelpath = 'PendingRequests_'.$date_today;

        $export_query = ServiceRequests::join('customers', 'customers.id', '=', 'service_requests.customer_id')
        ->join('hospitals', 'hospitals.id', '=', 'service_requests.hospital_id')
        ->join('departments', 'departments.id', '=', 'service_requests.dept_id')
        ->where("service_requests.status", "Received")
        ->select(
            'service_requests.id as Id',
            'service_requests.cvm_id as CVM_Id',
            'service_requests.request_type as Request_Type',
            'service_requests.status as Status',
            'service_requests.created_at as Created_At',
            'service_requests.updated_at as Updated_At',
            'customers.first_name as First_Name',
            'customers.last_name as Last_Name',
            'hospitals.hospital_name as Hospital_Names',
            'hospitals.state as State',
            'departments.name as Departments',
            'hospitals.responsible_branch as Responsible_Branch',
            'service_requests.sfdc_id as SFDC_Id',
            'service_requests.remarks as Remarks'
        )
        ->where('service_requests.is_practice',FALSE)
        ->orderBy('created_at', 'DESC')
        ->get()->toArray();

        foreach($export_query as &$excel_arrays){
            //$excel_arrays['Remarks'] = str_replace(' ', ' ', $excel_arrays['Remarks']); // Replaces all spaces with hyphens.

            $excel_arrays['Remarks'] = preg_replace('/[^A-Za-z0-9\-]/', ' ', $excel_arrays['Remarks']);
            //Logger($excel_arrays['Id'].'-'.($excel_arrays['Remarks']));
        }

        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }

        Excel::store(new PendingRequestsExport($export_query), $excelpath . '.xls', 'exports');

        /*
            $to_emails = Reportsetting::where('name', 'report_pendingrequests')->value('to_emails');
            $to_emails = explode(',', $to_emails);

            for ($i=0; $i < sizeof($to_emails); $i++) {
                $to_final[]['email'] = trim($to_emails[$i], " ");
            }

            $cc_emails = Reportsetting::where('name', 'report_pendingrequests')->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);
            $cc_emails[2] = "ritik.bansal@lyxelandflamingo.com";

            for ($i=0; $i < sizeof($cc_emails); $i++) {
                $cc_final[]['email'] = trim($cc_emails[$i], " ");
            }
            Mail::to($to_final)->cc($cc_final)->send(new DailyReceivedRequests($excelname, $excelpath, $date_today));
        */

        $to_final = [];
        $cc_final = [];

        if (App::environment('local')) {

            $to_final[]['email'] = 'ritik.bansal@lyxelandflamingo.com';

        } else {

            // Production environment — use actual report settings
            $to_emails = Reportsetting::where('name', 'report_pendingrequests')->value('to_emails');
            $to_emails = explode(',', $to_emails);

            foreach ($to_emails as $email) {
                $to_final[]['email'] = trim($email);
            }

            $cc_emails = Reportsetting::where('name', 'report_pendingrequests')->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);

            // Add Ritik's email to CC in production
            $cc_emails[] = "ritik.bansal@lyxelandflamingo.com";

            foreach ($cc_emails as $email) {
                $cc_final[]['email'] = trim($email);
            }
        }
        Mail::to($to_final)->cc($cc_final ?? [])->send(new DailyReceivedRequests($excelname, $excelpath, $date_today));

        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
    }

    //Daily Pending Requests Report AT 5 PM

    public function pending_weeklate_report()
    {
        $date_today=  date('d-m-Y', strtotime('today', time()));
        $date_week_ago=  date('Y-m-d', strtotime('-6 days', time())).' 00:00:00';
        $excelname = 'No Status Change More Than One Week Summary_'.$date_today.'.xls';
        $excelpath = 'NoStatusChangeMoreThanOneWeekSummary'.$date_today;

        $export_query = ServiceRequests::join('customers', 'customers.id', '=', 'service_requests.customer_id')
        ->join('hospitals', 'hospitals.id', '=', 'service_requests.hospital_id')
        ->join('departments', 'departments.id', '=', 'service_requests.dept_id')
        ->where("service_requests.status","!=", "Closed")
        ->where("service_requests.updated_at","<", $date_week_ago)
        ->select(
            'service_requests.id as Id',
            'service_requests.cvm_id as CVM_Id',
            'service_requests.sap_id as SAP_Id',
            'service_requests.sfdc_id as SFDC_Id',
            'service_requests.request_type as Request_Type',
            'service_requests.remarks as Remarks',
            'service_requests.status as Status',
            'service_requests.created_at as Created_At',
            'service_requests.updated_at as Updated_At',
            'customers.first_name as First_Name',
            'customers.last_name as Last_Name',
            'hospitals.hospital_name as Hospital_Names',
            'hospitals.state as State',
            'departments.name as Departments',
            'hospitals.responsible_branch as Responsible_Branch'
        )
        ->where('service_requests.is_practice',FALSE)
        ->orderBy('service_requests.updated_at', 'DESC')
        ->get();
        foreach($export_query as $query){
            $query->Remarks = preg_replace('/[^A-Za-z0-9\-]/', ' ', $query->Remarks);
            $query->Created_At = Carbon::parse($query->Created_At)->format('d/m/Y H:i:s');
            $query->Updated_At = Carbon::parse($query->Updated_At)->format('d/m/Y H:i:s');
        }

        $new_data = $export_query->groupBy('Responsible_Branch');
        $f_data = [];
        foreach ($new_data as $location => $data) {
            ${$location}[] = $data->groupBy('Status')->toArray();
            if(!empty(${$location})){
                $center_ar = array_values(${$location})[0];
                $statuses = [];
                foreach ($center_ar as $key => $value) {
                    $statuses[$key] = count($value);
                }
                $f_data[$location] = $statuses;
            }
        }
        $final_data = [];
        $s_centers = ["OMSI-GURGAON","OMSI-HYDERABAD","OMSI-MUMBAI","OMSI-BANGALORE","OMSI-AHEMDABAD","OMSI-COCHIN","OMSI-LUCKNOW","OMSI-KOLKATA","OMSI-CHENNAI"];
        $s_statuses = ["Received","Attended","Re-assigned","Assigned","Received_At_Repair_Center","Quotation_Prepared","PO_Received","Repair_Started","Repair_Completed","Ready_To_Dispatch","Dispatched"];
        foreach ($s_statuses as $status) {
            foreach ($s_centers as $center) {
                if(isset($f_data[$center][$status])){
                    $final_data[$center][$status] = $f_data[$center][$status];
                }else{
                    $final_data[$center][$status] = 0;
                }
            }
        }

        if (Storage::disk('exports')->exists($excelpath . '.xls')) {
            Storage::disk('exports')->delete($excelpath . '.xls');
        }

        // Create a new export class for pending week late report
        Excel::store(new PendingWeekLateExport($export_query), $excelpath . '.xls', 'exports');

        /*
            $to_emails = Reportsetting::where('name', 'report_pendingrequests')->value('to_emails');
            $to_emails = explode(',', $to_emails);
            for ($i=0; $i < sizeof($to_emails); $i++) {
                $to_final[]['email'] = trim($to_emails[$i], " ");
            }

            $cc_emails = Reportsetting::where('name', 'report_pendingrequests')->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);
            $cc_emails[2] = "ritik.bansal@lyxelandflamingo.com";
            $cc_emails[3] = "mayank.sachdeva@lyxelandflamingo.com";

            for ($i=0; $i < sizeof($cc_emails); $i++) {
                $cc_final[]['email'] = trim($cc_emails[$i], " ");
            }

            Mail::to($to_final)->cc($cc_final)->send(new DailyPendingWeekLate($excelname, $excelpath, $date_today));
        */

        $to_final = [];
        $cc_final = [];

        if (App::environment('local')) {

            $to_final[]['email'] = 'ritik.bansal@lyxelandflamingo.com';

        } else {

            // Production environment — use actual report settings
            $to_emails = Reportsetting::where('name', 'report_pendingrequests')->value('to_emails');
            $to_emails = explode(',', $to_emails);

            foreach ($to_emails as $email) {
                $to_final[]['email'] = trim($email);
            }

            $cc_emails = Reportsetting::where('name', 'report_pendingrequests')->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);

            // Add Ritik's email to CC in production
            $cc_emails[] = "ritik.bansal@lyxelandflamingo.com";

            foreach ($cc_emails as $email) {
                $cc_final[]['email'] = trim($email);
            }
        }
        Mail::to($to_final)->cc($cc_final ?? [])->send(new DailyPendingWeekLate($excelname, $excelpath, $date_today));


        Storage::disk('exports')->delete($excelpath . '.xls');

        echo 'success';
    }


    // Monthly Customer List
    public function monthly_customer_all()
    {
        $this->generate_customer_list_excel('All India');
    }

    public function monthly_customer_region($region)
    {
        $validator = Validator::make(
          [
            'region' => $region,
          ],[
            'region' => 'required|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $this->generate_customer_list_excel($region);
    }

    public function generate_customer_list_excel($region='')
    {
        $date_to=  date('Y-m-d', strtotime('last day of last month', time()));
        $daterange_to = date("d-M-Y", strtotime($date_to));
        $excelname = 'My Voice App Customer List ('.ucfirst($region).') - '.$daterange_to.'.xls';
        $excelpath = 'CustomerList_'.$region."_".$date_to;

        if ($region == 'All India') {
            $region = ['north','east','south','west'];
            $regionname = 'panindia';
        } else {
            $regionname = $region;
            $region = array($region);
        }

        $user = Customers::select(
            'id as Id',
            'customer_id as Customer_Id',
            'title as Title',
            'first_name as First_Name',
            'last_name as Last_Name',
            'mobile_number as Mobile_Number',
            'email as Email',
            'platform as Platform',
            'app_version as App_Version',
            'created_at as Created_At',
            'hospital_id as Hospital_Id'
        )
        ->where('email', 'NOT LIKE', '%@olympus.com%')
        ->orderBy('created_at', 'DESC')
        ->get();

        $final_data = array();
        foreach ($user as $user_temp) {
            $hospitals_ids = explode(',', $user_temp->Hospital_Id);
            $hospitals = Hospitals::where('customer_id', $user_temp->Id)->get();
            $hospitals_name = Hospitals::where('customer_id', $user_temp->Id)->pluck('hospital_name')->all();
            $hospital_names = implode(', ', $hospitals_name);
            $count = 1;
            $hospital_state = Hospitals::where('id', $user_temp->Hospital_Id)->value('state');

            $service_region = find_region($hospital_state);

            $city = [];
            $state = [];
            foreach ($hospitals as $hospital) {
                $dept_ids = explode(',', $hospital->dept_id);
                $departments = Departments::whereIn('id', $dept_ids)->pluck('name')->all();
                $depart_names = implode(', ', $departments);
                $city[] = $hospital->city;
                $state[] = $hospital->state;
            }
            $user_temp->Hospital_Names = $hospital_names;
            $user_temp->Departments = $depart_names;
            $user_temp->City_Names = implode(',', array_unique($city));
            $user_temp->State_Sames = implode(',', array_unique($state));
            unset($user_temp->Hospital_Id);

            $afterIndex = 9;
            $key11 = array_merge(array_slice($user_temp->toArray(), 0, $afterIndex+1), array('Region' => ucfirst($service_region)), array_slice($user_temp->toArray(), $afterIndex+1));

            if (in_array($service_region, $region)) {
                array_push($final_data, $key11);
            }
        }

        if (Storage::disk('exports')->exists($excelpath . '.xls')) {
            Storage::disk('exports')->delete($excelpath . '.xls');
        }

        Excel::store(new CustomerListExport(collect($final_data)), $excelpath . '.xls', 'exports');

        /*
            $to_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('to_emails');
            $to_emails = explode(',', $to_emails);
            for ($i=0; $i < sizeof($to_emails); $i++) {
                $to_final[]['email'] = $to_emails[$i];
            }

            $cc_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);
            for ($i=0; $i < sizeof($cc_emails); $i++) {
                $cc_final[]['email'] = $cc_emails[$i];
            }

            Mail::to($to_final)->cc($cc_final)->send(new MonthlyCustomerList($regionname, $excelname, $excelpath, $daterange_to));
        */

        $to_final = [];
        $cc_final = [];

        if (App::environment('local')) {

            $to_final[]['email'] = 'ritik.bansal@lyxelandflamingo.com';

        } else {

            // Production environment — use actual report settings
            $to_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('to_emails');
            $to_emails = explode(',', $to_emails);

            foreach ($to_emails as $email) {
                $to_final[]['email'] = trim($email);
            }

            $cc_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);

            // Add Ritik's email to CC in production
            $cc_emails[] = "ritik.bansal@lyxelandflamingo.com";

            foreach ($cc_emails as $email) {
                $cc_final[]['email'] = trim($email);
            }
        }

        Mail::to($to_final)->cc($cc_final)->send(new MonthlyCustomerList($regionname, $excelname, $excelpath, $daterange_to));

        Storage::disk('exports')->delete($excelpath . '.xls');

        echo 'success';
    }


    // Monthly Feedback Report
    public function monthly_report_feedback()
    {
        $this->generate_feedback_excel('All India');
    }

    public function monthly_report_feedback_regional($region)
    {
        $validator = Validator::make(
          [
            'region' => $region,
          ],[
            'region' => 'required|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $this->generate_feedback_excel($region);
    }

    public function generate_feedback_excel($region='')
    {
        $date_from= date('Y-m-d', strtotime('first day of last month', time()));
        $date_to=  date('Y-m-d', strtotime('last day of last month', time()));
        $daterange_from = date("d-M", strtotime($date_from));
        $daterange_to = date("d-M-Y", strtotime($date_to));

        $excelname = 'Feedback Report ('.ucfirst($region).') - '.$daterange_from."_".$daterange_to.'.xls';
        $excelpath = 'FeedbackReport_'.$region.'-'.$date_from."_".$date_to;

        if ($region == 'All India') {
            $region = ['north','east','south','west'];
            $regionname = 'panindia';
        } else {
            $regionname = $region;
            $region = array($region);
        }

        $export_query = Feedback::select(
            'id as Id',
            'request_id as Request_Id',
            'response_speed as Response_Speed',
            'quality_of_response as Quality_Of_Response',
            'app_experience as App_Experience',
            'olympus_staff_performance as Olympus_Staff_Performance',
            'remarks as Remarks',
            'created_at as Created_At'
        )
        ->orderBy('created_at', 'DESC')
        ->whereBetween('created_at', [$date_from, $date_to])
        ->get();

        $final_data = array();
        foreach ($export_query as $feedback) {
            $key = (object)[];
            $user_temp = ServiceRequests::where('feedback_id', $feedback->Id)->first();
            $hospital = Hospitals::where('id', $user_temp->hospital_id)->first();
            $department = Departments::where('id', $user_temp->dept_id)->first();
            $cust_details = Customers::where('id', $user_temp->customer_id)->first();
            $assigned_employee = EmployeeTeam::where('employee_code', $user_temp->employee_code)->value('name');

            $key->Request_Id = $user_temp->id;
            $key->Created_At = $feedback->Created_At;
            $key->Region = ucfirst(find_region($hospital->state));
            $key->Hospital = $hospital->hospital_name;
            $key->Department = $department->name;
            $key->City = $hospital->city;
            $key->State = $hospital->state;
            $key->First_Name = $cust_details->first_name;
            $key->Last_Name = $cust_details->last_name;
            $key->Assigned_Engineer = $assigned_employee;
            $key->Response_Speed = $this->number2star((int)$feedback->Response_Speed);
            $key->Quality_Of_Response = $this->number2star((int)$feedback->Quality_Of_Response);
            $key->App_Experience = $this->number2star((int)$feedback->App_Experience);
            $key->Olympus_Staff_Performance = $this->number2star((int)$feedback->Olympus_Staff_Performance);
            $key->Request_Type = $user_temp->request_type;
            $key->Sub_Type = $user_temp->sub_type;
            $key->Responsible_Branch = $hospital->responsible_branch;

            if (in_array(strtolower($key->Region), $region)) {
                array_push($final_data, (array) $key);
            }
        }
        if (Storage::disk('exports')->exists($excelpath . '.xls')) {
            Storage::disk('exports')->delete($excelpath . '.xls');
        }

        Excel::store(new FeedbackReportExport(collect($final_data)), $excelpath . '.xls', 'exports');

        /*
            $to_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('to_emails');
            $to_emails = explode(',', $to_emails);
            for ($i=0; $i < sizeof($to_emails); $i++) {
                $to_final[]['email'] = $to_emails[$i];
            }

            $cc_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);
            for ($i=0; $i < sizeof($cc_emails); $i++) {
                $cc_final[]['email'] = $cc_emails[$i];
            }

            Mail::to($to_final)->cc($cc_final)->send(new MonthlyFeedBackReport($regionname, $excelname, $excelpath, $daterange_from, $daterange_to));
        */

        $to_final = [];
        $cc_final = [];

        if (App::environment('local')) {

            $to_final[]['email'] = 'ritik.bansal@lyxelandflamingo.com';

        } else {

            // Production environment — use actual report settings
            $to_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('to_emails');
            $to_emails = explode(',', $to_emails);

            foreach ($to_emails as $email) {
                $to_final[]['email'] = trim($email);
            }

            $cc_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);

            // Add Ritik's email to CC in production
            $cc_emails[] = "ritik.bansal@lyxelandflamingo.com";

            foreach ($cc_emails as $email) {
                $cc_final[]['email'] = trim($email);
            }
        }

        Mail::to($to_final)->cc($cc_final)->send(new MonthlyFeedBackReport($regionname, $excelname, $excelpath, $daterange_from, $daterange_to));

        Storage::disk('exports')->delete($excelpath . '.xls');

        echo 'success';
    }

    public function number2star($number)
    {

        $validator = Validator::make(
          [
            'number' => $number,
          ],[
            'number' => 'required|numeric',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }

        if ($number == 1) { return "★"; }
        if ($number == 2) { return "★★"; }
        if ($number == 3) { return "★★★"; }
        if ($number == 4) { return "★★★★"; }
        if ($number == 5) { return "★★★★★"; }
    }


    //Weekly Escalations Report
    public function weekly_escalations_report()
    {
        $previous_week = strtotime("-1 week +1 day");
        $start_week = strtotime("last sunday midnight", $previous_week);
        $end_week = strtotime("next saturday +1 day", $start_week);
        $date_from = date("Y-m-d", $start_week);
        $date_to = date("Y-m-d", $end_week);
        $daterange_from = date("d-M", strtotime($date_from));
        $daterange_to = date("d-M-Y", strtotime($date_to));

        $excelname = 'My Voice App Escalation Summary - '.$daterange_from."_".$daterange_to.'.xls';
        $excelpath = 'EscalationSummary_'.$date_from."-".$date_to;

        $staus_timeline = StatusTimeline::where('status', 'Escalated')
        ->whereBetween('created_at', [$date_from, $date_to])
        ->get()->pluck('request_id');

        $export_query = ServiceRequests::join('customers', 'customers.id', '=', 'service_requests.customer_id')
        // ->join('employee_team','employee_team.employee_code','=','service_requests.employee_code')
        ->join('hospitals', 'hospitals.id', '=', 'service_requests.hospital_id')
        ->join('departments', 'departments.id', '=', 'service_requests.dept_id')
        ->whereIn('service_requests.id', $staus_timeline)
        ->select(
            'service_requests.id as Id',
            'service_requests.cvm_id as CVM_Id',
            'service_requests.created_at as Created_At',
            'service_requests.request_type as Request_Type',
            'service_requests.sub_type as Sub_Type',
            'service_requests.employee_code as Assigned_Employee_Code',
            'service_requests.remarks as Remarks',
            'service_requests.escalation_count as Escalation_Count',
            'service_requests.escalation_reasons as Escalation_Reasons',
            'service_requests.escalation_remarks as Escalation_Remarks',
            'service_requests.status as Current_Status',
            'customers.first_name as Customer_First_Name',
            'customers.last_name as Customer_Last_Name',
            'hospitals.hospital_name as Hospital_Name',
            'departments.name as Department_Name',
            'hospitals.state as State',
            'hospitals.responsible_branch as Responsible_Branch'
          )
        ->where('service_requests.escalation_count', ">", "0")
        ->orderBy('created_at', 'DESC')
        //->whereBetween('service_requests.created_at', [$date_from, $date_to])
        ->where('is_practice', false)
        ->get();

        $final_data = array();
        foreach ($export_query as $key) {
            array_push($final_data, $key->toArray());
        }
        if (Storage::disk('exports')->exists($excelpath . '.xls')) {
            Storage::disk('exports')->delete($excelpath . '.xls');
        }

        Excel::store(new EscalationReportExport(collect($final_data)), $excelpath . '.xls', 'exports');

        /*
            $to_emails = Reportsetting::where('name', 'report_receivedrequests')->value('to_emails');
            $to_emails = explode(',', $to_emails);
            for ($i=0; $i < sizeof($to_emails); $i++) {
                $to_final[]['email'] = $to_emails[$i];
            }

            $cc_emails = Reportsetting::where('name', 'report_receivedrequests')->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);
            $cc_emails[2] = "ritik.bansal@lyxelandflamingo.com";
            $cc_emails[3] = "komal.sen@olympus.com";
            for ($i=0; $i < sizeof($cc_emails); $i++) {
                $cc_final[]['email'] = $cc_emails[$i];
            }
            Mail::to($to_final)->cc($cc_final)->send(new WeeklyEscalationReport($excelname, $excelpath, $daterange_from, $daterange_to));
        */

        $to_final = [];
        $cc_final = [];

        if (App::environment('local')) {

            $to_final[]['email'] = 'ritik.bansal@lyxelandflamingo.com';

        } else {

            // Production environment — use actual report settings
            $to_emails = Reportsetting::where('name', 'report_receivedrequests')->value('to_emails');
            $to_emails = explode(',', $to_emails);

            foreach ($to_emails as $email) {
                $to_final[]['email'] = trim($email);
            }

            $cc_emails = Reportsetting::where('name', 'report_receivedrequests')->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);

            // Add additional CC emails in production
            $cc_emails[] = "ritik.bansal@lyxelandflamingo.com";
            $cc_emails[] = "komal.sen@olympus.com";

            foreach ($cc_emails as $email) {
                $cc_final[]['email'] = trim($email);
            }
        }
        Mail::to($to_final)->cc($cc_final)->send(new WeeklyEscalationReport($excelname, $excelpath, $daterange_from, $daterange_to));

        Storage::disk('exports')->delete($excelpath . '.xls');

        echo 'success';
    }

    public function weekly_report_all()
    {
        $this->generate_excel('All India');
    }

    public function weekly_report_region($region)
    {

        $validator = Validator::make(
          [
            'region' => $region,
          ],[
            'region' => 'required|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }

        $this->generate_excel($region);

    }

    public function generate_excel($region='')
    {
        $previous_week = strtotime("-1 week +1 day");
        $start_week = strtotime("last sunday midnight", $previous_week);
        $end_week = strtotime("next saturday +1 day", $start_week);
        $date_from = date("Y-m-d", $start_week);
        $date_to = date("Y-m-d", $end_week);

        $daterange_from = date("d-M", strtotime($date_from));
        $daterange_to = date("d-M-Y", strtotime($date_to));
        $excelname = 'Voice of Customer ('.ucfirst($region).') - '.$daterange_from."_".$daterange_to.'.xls';
        $excelpath = 'VoiceOfCustomer_'.$region.'-'.$date_from."_".$date_to;

        if ($region == 'All India') {
            $region = ['north','east','south','west'];
            $regionname = 'panindia';
        } else {
            $regionname = $region;
            $region = array($region);
        }

        $export_query = ServiceRequests::join('customers', 'customers.id', '=', 'service_requests.customer_id')
        ->join('hospitals', 'hospitals.id', '=', 'service_requests.hospital_id')
        ->join('departments', 'departments.id', '=', 'service_requests.dept_id')
        ->select(
            'service_requests.id as Id',
            'service_requests.cvm_id as MyVoiceId',
            'service_requests.created_at as Created_At',
            'service_requests.request_type as Request_Type',
            'service_requests.sub_type as Sub_Type',
            'service_requests.status as Current_Status',
            'hospitals.hospital_name as Hospital_Name',
            'departments.name as Department_Name',
            'hospitals.state as State',
            'service_requests.remarks as Remarks',
            'customers.first_name as Customer_First_Name',
            'customers.last_name as Customer_Last_Name',
            'service_requests.employee_code as Assigned_Employee_Code',
            'service_requests.hospital_id as Hospital_Id',
            'hospitals.responsible_branch as Responsible_Branch'
          )
        ->orderBy('created_at', 'DESC')
        ->whereBetween('service_requests.created_at', [$date_from, $date_to])
        ->where('is_practice', false)
        ->get();

        $final_data = array();
        foreach ($export_query as $key) {
            $hospital_state = Hospitals::where('id', $key->Hospital_Id)->value('state');
            $assigned_employee = (is_null($key->Assigned_Employee_Code)) ? '--NA--' : EmployeeTeam::where('employee_code', $key->Assigned_Employee_Code)->value('name');
            $service_region = find_region($hospital_state);
            unset($key->Hospital_Id);
            unset($key->Assigned_Employee_Code);

            $afterIndex = 7;
            $key11 = array_merge(array_slice($key->toArray(), 0, $afterIndex+1), array('Region' => ucfirst($service_region)), array_slice($key->toArray(), $afterIndex+1));
            $key11 = array_merge($key11, array('Assigned_Employee_Name' => $assigned_employee));

            if (in_array($service_region, $region)) {
                array_push($final_data, $key11);
            }
        }

        if (Storage::disk('exports')->exists($excelpath . '.xls')) {
            Storage::disk('exports')->delete($excelpath . '.xls');
        }

        Excel::store(new VoiceOfCustomerExport(collect($final_data)), $excelpath . '.xls', 'exports');

        /*
            $to_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('to_emails');
            $to_emails = explode(',', $to_emails);
            for ($i=0; $i < sizeof($to_emails); $i++) {
                $to_final[]['email'] = $to_emails[$i];
            }

            $cc_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);
            for ($i=0; $i < sizeof($cc_emails); $i++) {
                $cc_final[]['email'] = $cc_emails[$i];
            }

            Mail::to($to_final)->cc($cc_final)->send(new WeeklyMis($regionname, $excelname, $excelpath, $daterange_from, $daterange_to));
        */

        $to_final = [];
        $cc_final = [];

        if (App::environment('local')) {

            $to_final[]['email'] = 'ritik.bansal@lyxelandflamingo.com';

        } else {

            // Production environment — use actual report settings
            $to_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('to_emails');
            $to_emails = explode(',', $to_emails);

            foreach ($to_emails as $email) {
                $to_final[]['email'] = trim($email);
            }

            $cc_emails = Reportsetting::where('name', 'autoemail_'.$regionname)->value('cc_emails');
            $cc_emails = explode(',', $cc_emails);

            // Add Ritik's email to CC in production
            $cc_emails[] = "ritik.bansal@lyxelandflamingo.com";

            foreach ($cc_emails as $email) {
                $cc_final[]['email'] = trim($email);
            }
        }

        Mail::to($to_final)->cc($cc_final)->send(new WeeklyMis($regionname, $excelname, $excelpath, $daterange_from, $daterange_to));
        //Storage::disk('exports')->delete($excelpath . '.xls');

        echo 'success';
    }
}


