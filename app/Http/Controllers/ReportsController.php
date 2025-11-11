<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Mail\DailyPendingWeekLate;
use App\Mail\DailyReceivedRequests;
use App\Mail\MonthlyCustomerList;
use App\Mail\MonthlyFeedBackReport;
use App\Mail\WeeklyEscalationReport;
use App\Mail\WeeklyMis;
use App\Models\Customers;
use App\Models\EmployeeTeam;
use App\Models\Feedback;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\Reportsetting;
use App\StatusTimeline;
use Carbon\Carbon;
use Excel;
use Mail;
use Validator;

class ReportsController extends Controller
{
    public function employee_requests(){
        $employees = EmployeeTeam::select('id', 'name', 'email','employee_code', 'escalation_1')
            ->whereNotIn('id', [93,253])
            ->with(['service_requests' => function($query){
                $query->whereNotIn('status', ['Received', 'Closed'])
                    ->whereIn('request_type', ['academic','enquiry'])
                    ->select('id','employee_code', 'request_type', 'customer_id');
            }])
            // ->withCount('service_requests')
            ->groupBy('employee_code')
            ->get();
        $count = 1;
        echo '<style>table {  font-family: arial, sans-serif;  border-collapse: collapse;  width: 100%;}td, th {  border: 1px solid #dddddd;  text-align: left;  padding: 8px;}tr:nth-child(even) {    text-align: top;  background-color: #dddddd;}</style>';
        echo '<table><tr><th>No.</th><th>Employee Code</th><th>Name</th><th>Email</th><th>Requests</th><th>Request IDs</th></tr>';
        foreach($employees as $employee){
            if($employee->service_requests()->exists()){
                $data = "";
                $data .= "<tr><td>$count</td>"
                    ."<td>$employee->employee_code</td>"
                    ."<td>$employee->name</td>"
                    ."<td>$employee->email</td>"
                    ."<td><b>".$employee->service_requests()->count()."</b></td>"
                    ."<td>";
                    foreach ($employee->service_requests()->pluck('id') as $request_id) {
                        $data .= $request_id."<br>";
                    }
                $data .= "</td></tr>";
                echo $data;
                $count++;
                // dd($request->toArray());
            }
        }
        echo '</table>';
        // dd(count($employees), $employees[0]);
    }

    // public function test1()
    // {
    //     // $customers = \App\Customers::where('id','<',10)->get();
    //     $customers = \App\Customers::all();
    //     $final_data = [];
    //     foreach ($customers as $customer) {
    //         $hospitals = explode(',', $customer->hospital_id);
    //         $hospital_data = [];
    //         foreach ($hospitals as $hospital_id) {
    //             $hospital = \App\Hospitals::whereId($hospital_id)->firstOrFail();
    //             // array_push($hospital_data, $hospital->toArray());
    //             // $customer->{'hospital_'.$hospital_id} = $hospital;
    //             $customer->hospital_id = $hospital['id'];
    //             $customer->hospital_hospital_name = $hospital['hospital_name'];
    //             $customer->hospital_address = $hospital['address'];
    //             $customer->hospital_city = $hospital['city'];
    //             $customer->hospital_state = $hospital['state'];
    //             $customer->hospital_zip = $hospital['zip'];
    //             $customer->hospital_responsible_branch = $hospital['responsible_branch'];
    //             array_push($final_data, $customer->toArray());
    //         }
    //         // $customer->hospitals = $hospital_data;
    //         // array_push($final_data, $customer->toArray());
    //     }
    //     // return response()->json($final_data);
    //     Excel::create('aa', function ($excel) use ($final_data) {
    //         $excel->sheet('Sheet1', function ($sheet) use ($final_data) {
    //                 $sheet->fromArray($final_data);

    //                 $sheet->row(1, function ($row) {
    //                     $row->setBackground('#4f81bd');
    //                 });
    //                 $sheet->setAutoSize(true);
    //                 $sheet->setWidth('J', 30);
    //                 $sheet->getStyle('A1:O1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
    //         });
    //     })->export('xls');
    // }



    // public function aa()
    // {
    //     $srs = ServiceRequests::all();
    //     $requests = [];
    //     foreach ($srs as $sr) {
    //         if(!$sr->is_practice && $sr->escalation_count){
    //             $data = [
    //                 'MyVoice_Request_Id' => $sr->id,
    //                'Escalation_Count' => $sr->escalation_count,
    //                'Escalation_Reason' => \implode(";",  preg_split("/\s*,\s*/", trim($sr->escalation_reasons), -1, PREG_SPLIT_NO_EMPTY) ),
    //                'Escalation_Remarks' => $sr->escalation_remarks,
    //             ];
    //             array_push($requests, $data);
    //         }
    //     }
    //     return response()->json($requests);
    // }

    public function aa()
    {
        // $ids = [10209,10210,10211,10212,10415,9203];
        $ids  = [ 1625 ];
        $srs = ServiceRequests::orderBy('created_at', 'DESC')
            ->whereIn('id', $ids)->get();
        // $srs
        $final_data = [];
        foreach ($srs as $sr) {
            // $sr = ServiceRequests::find($id);
            $hospital = Hospitals::find($sr->hospital_id);
            $hospital = Hospitals::find($sr->hospital_id);
            $customer = Customers::find($sr->customer_id);

            $data = [
                'SheetID' => $sr->id,
                'MyVoice_Request_Id__c' => $sr->id,
                'Myvoice_Customer_ID__c' => $sr->customer_id,
                'Customer_Id__c' => $sr->customer_id,
                'Hospital_Name__c' => $hospital->hospital_name,
                'Hospital_responsible_branch__c' => $hospital->responsible_branch,
                'Hospital_Address__c' => $hospital->address,
                'Hospital_city__c' => $hospital->city,
                'Hospital_country__c' => $hospital->country,
                'Hospital_Dept_ID__c' => \implode(";",  preg_split("/\s*,\s*/", trim($hospital->dept_id), -1, PREG_SPLIT_NO_EMPTY) ),
                'Hospital_State__c' => $hospital->state,
                'Hospital_Zip__c' => $hospital->zip,
                'Title__c' => $customer->title,
                'First_Name__c' => $customer->first_name,
                'Middle_Name__c' => $customer->middle_name,
                'Last_Name__c' => $customer->last_name,
                'Customer_Email__c' => $customer->email,
                'Mobile_Number__c' => $customer->mobile_number,
                'Status' => $sr->status,
                'Request type' => $sr->request_type,
                'Sub Type' => $sr->sub_type,
                'Department__c' => $sr->dept_id,
                'Remarks' => $sr->remarks
            ];
            array_push($final_data, $data);
        }
        // return response()->json($final_data);
        Excel::create('requests', function ($excel) use ($final_data) {
            $excel->sheet('Sheet1', function ($sheet) use ($final_data) {
                    $sheet->fromArray($final_data);

                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    // $sheet->setWidth('J', 30);
                    $sheet->getStyle('A1:Z1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
            });
        })->export('xls');
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

        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }

        Excel::create($excelpath, function ($excel) use ($final_data) {
            $excel->sheet('Sheet1', function ($sheet) use ($final_data) {
                if (count($final_data) != 0) {
                    $sheet->fromArray($final_data);

                    foreach ($final_data as $key => $value) {
                        if ($key % 2 == 0) {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#b8cce4');
                            });
                        } else {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#dbe5f1');
                            });
                        }
                    }
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    $sheet->setWidth('J', 30);
                    $sheet->getStyle('A1:O1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path('/exports/'));
        // })->export('xls');

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

        Mail::to($to_final)->cc($cc_final)
            ->send(new WeeklyMis($regionname, $excelname, $excelpath, $daterange_from, $daterange_to));
        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
    }


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
        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }
        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'11.xls');
        }

        Excel::create($excelpath, function ($excel) use ($final_data) {
            $excel->sheet('Sheet1', function ($sheet) use ($final_data) {
                if (count($final_data) != 0) {
                    $sheet->setStyle(array('font' => array('size' => 9)));
                    $sheet->fromArray($final_data);
                    foreach ($final_data as $key => $value) {
                        if ($key % 2 == 0) {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#b8cce4');
                            });
                        } else {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#dbe5f1');
                            });
                        }
                    }
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    $sheet->setWidth('D', 30);
                    $sheet->getStyle('A1:Q1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path('/exports/'));
        // })->export('xls');

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
        Mail::to($to_final)->cc($cc_final)
            ->send(new MonthlyFeedBackReport($regionname, $excelname, $excelpath, $daterange_from, $daterange_to));
        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
    }


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

        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }

        Excel::create($excelpath, function ($excel) use ($final_data) {
            $excel->sheet('Sheet1', function ($sheet) use ($final_data) {
                if (count($final_data) != 0) {
                    $sheet->fromArray($final_data);

                    foreach ($final_data as $key => $value) {
                        if ($key % 2 == 0) {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#b8cce4');
                            });
                        } else {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#dbe5f1');
                            });
                        }
                    }
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    $sheet->setWidth('J', 30);
                    $sheet->getStyle('A1:M1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path('/exports/'));
        // })->export('xls');

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

        Mail::to($to_final)->cc($cc_final)
            ->send(new MonthlyCustomerList($regionname, $excelname, $excelpath, $daterange_to));
        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
    }

    //Pending Requests Report
    public function pending_requests_report_old()
    {
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
            'service_requests.sfdc_id as SFDC_Id',
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
        ->orderBy('created_at', 'DESC')
        ->get()->toArray();

        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }

        Excel::create($excelpath, function ($excel) use ($export_query) {
            $excel->sheet('Sheet1', function ($sheet) use ($export_query) {
                if (count($export_query) != 0) {
                    $sheet->fromArray($export_query);

                    foreach ($export_query as $key => $value) {
                        if ($key % 2 == 0) {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#b8cce4');
                            });
                        } else {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#dbe5f1');
                            });
                        }
                    }
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    $sheet->setWidth('E', 30);
                    $sheet->getStyle('A1:N1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path('exports/'));
        // })->export('xls');

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
        Mail::to($to_final)->cc($cc_final)
            ->send(new DailyReceivedRequests($excelname, $excelpath, $date_today));
        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
    }

    //Pending Requests Report
    public function pending_weeklate_report_old()
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

        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }

        Excel::create($excelpath, function ($excel) use ($export_query,$final_data,$s_centers,$s_statuses) {
            // $excel->sheet('Summary', function($sheet) use ($final_data,$s_centers,$s_statuses) {
            //     $sheet->loadView('reports.pendingweeklate', ['data'=>$final_data,'centers'=>$s_centers,'statuses'=>$s_statuses]);

            //     $sheet->fromArray($final_data, null, 'B4', true);
            //     $sheet->setWidth(array('A'=>17,'B'=>12,'C'=>12,'D'=>12,'E'=>12,'F'=>12,'G'=>12,'H'=>12,'I'=>12,'J'=>12,'K'=>12,'L'=>12,'M'=>12));
            //     $sheet->getRowDimension(1)->setRowHeight(16);
            //     $sheet->getRowDimension(2)->setRowHeight(100);
            //     $sheet->getRowDimension(3)->setRowHeight(30);
            //     $sheet->getRowDimension(4)->setRowHeight(16);
            //     $sheet->getRowDimension(5)->setRowHeight(16);
            //     $sheet->getRowDimension(6)->setRowHeight(16);
            //     $sheet->getRowDimension(7)->setRowHeight(16);
            //     $sheet->getRowDimension(8)->setRowHeight(16);
            //     $sheet->getRowDimension(9)->setRowHeight(16);
            //     $sheet->getRowDimension(10)->setRowHeight(16);
            //     $sheet->getRowDimension(11)->setRowHeight(16);
            //     $sheet->getRowDimension(12)->setRowHeight(16);
            //     $sheet->getRowDimension(13)->setRowHeight(15);

            //     $sheet->getStyle('B2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('C2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('D2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('E2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('F2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('G2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('H2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('I2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('J2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('K2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('L2')->getAlignment()->setWrapText(true);

            //     $sheet->getStyle('B3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('C3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('D3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('E3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('F3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('G3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('H3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('I3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('J3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('K3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('L3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('M3')->getAlignment()->setWrapText(true);
            // });
            $excel->sheet('Sheet2', function ($sheet) use ($export_query) {
                if (count($export_query) != 0) {
                    $sheet->fromArray($export_query);

                    foreach ($export_query as $key => $value) {
                        if ($key % 2 == 0) {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#b8cce4');
                            });
                        } else {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#dbe5f1');
                            });
                        }
                    }
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    $sheet->setWidth('F', 30);
                    $sheet->getStyle('A1:O1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path('exports/'));
        // })->export('xls');

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
        Mail::to($to_final)->cc($cc_final)
            ->send(new DailyPendingWeekLate($excelname, $excelpath, $date_today));
        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
    }

    //Pending Requests Report
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
        foreach($excel_array as $excel_arrays){
            //$excel_arrays['Remarks'] = str_replace(' ', ' ', $excel_arrays['Remarks']); // Replaces all spaces with hyphens.

            $excel_arrays['Remarks'] = preg_replace('/[^A-Za-z0-9\-]/', ' ', $excel_arrays['Remarks']);
            //Logger($excel_arrays['Id'].'-'.($excel_arrays['Remarks']));
            $export_query[] = $excel_arrays;
        }
        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }

        Excel::create($excelpath, function ($excel) use ($export_query) {
            $excel->sheet('Sheet1', function ($sheet) use ($export_query) {
                if (count($export_query) != 0) {
                    $sheet->fromArray($export_query);

                    foreach ($export_query as $key => $value) {
                        if ($key % 2 == 0) {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#b8cce4');
                            });
                        } else {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#dbe5f1');
                            });
                        }
                    }
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    $sheet->setWidth('E', 30);
                    $sheet->getStyle('A1:N1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path('exports/'));
        // })->export('xls');

        $to_emails = Reportsetting::where('name', 'report_pendingrequests')->value('to_emails');
        $to_emails = explode(',', $to_emails);
        for ($i=0; $i < sizeof($to_emails); $i++) {
            $to_final[]['email'] = trim($to_emails[$i], " ");
        }

        $cc_emails = Reportsetting::where('name', 'report_pendingrequests')->value('cc_emails');
        $cc_emails = explode(',', $cc_emails);
        $cc_emails[2] = "ritik.bansal@lyxelandflamingo.com";
        //$cc_emails[3] = "ryo.nakadegawa@olympus.com";
        for ($i=0; $i < sizeof($cc_emails); $i++) {
            $cc_final[]['email'] = trim($cc_emails[$i], " ");
        }
        Mail::to($to_final)->cc($cc_final)
            ->send(new DailyReceivedRequests($excelname, $excelpath, $date_today));
        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
    }

    //Pending Requests Report
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

        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }

        Excel::create($excelpath, function ($excel) use ($export_query,$final_data,$s_centers,$s_statuses) {
            // $excel->sheet('Summary', function($sheet) use ($final_data,$s_centers,$s_statuses) {
            //     $sheet->loadView('reports.pendingweeklate', ['data'=>$final_data,'centers'=>$s_centers,'statuses'=>$s_statuses]);

            //     $sheet->fromArray($final_data, null, 'B4', true);
            //     $sheet->setWidth(array('A'=>17,'B'=>12,'C'=>12,'D'=>12,'E'=>12,'F'=>12,'G'=>12,'H'=>12,'I'=>12,'J'=>12,'K'=>12,'L'=>12,'M'=>12));
            //     $sheet->getRowDimension(1)->setRowHeight(16);
            //     $sheet->getRowDimension(2)->setRowHeight(100);
            //     $sheet->getRowDimension(3)->setRowHeight(30);
            //     $sheet->getRowDimension(4)->setRowHeight(16);
            //     $sheet->getRowDimension(5)->setRowHeight(16);
            //     $sheet->getRowDimension(6)->setRowHeight(16);
            //     $sheet->getRowDimension(7)->setRowHeight(16);
            //     $sheet->getRowDimension(8)->setRowHeight(16);
            //     $sheet->getRowDimension(9)->setRowHeight(16);
            //     $sheet->getRowDimension(10)->setRowHeight(16);
            //     $sheet->getRowDimension(11)->setRowHeight(16);
            //     $sheet->getRowDimension(12)->setRowHeight(16);
            //     $sheet->getRowDimension(13)->setRowHeight(15);

            //     $sheet->getStyle('B2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('C2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('D2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('E2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('F2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('G2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('H2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('I2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('J2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('K2')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('L2')->getAlignment()->setWrapText(true);

            //     $sheet->getStyle('B3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('C3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('D3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('E3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('F3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('G3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('H3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('I3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('J3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('K3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('L3')->getAlignment()->setWrapText(true);
            //     $sheet->getStyle('M3')->getAlignment()->setWrapText(true);
            // });
            $excel->sheet('Sheet2', function ($sheet) use ($export_query) {
                if (count($export_query) != 0) {
                    $sheet->fromArray($export_query);

                    foreach ($export_query as $key => $value) {
                        if ($key % 2 == 0) {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#b8cce4');
                            });
                        } else {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#dbe5f1');
                            });
                        }
                    }
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    $sheet->setWidth('F', 30);
                    $sheet->getStyle('A1:O1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path('exports/'));
        // })->export('xls');

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
        Mail::to($to_final)->cc($cc_final)
            ->send(new DailyPendingWeekLate($excelname, $excelpath, $date_today));
        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
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

            // $hospital_state = \App\Hospitals::where('id',$key->Hospital_Id)->value('state');
            // $assigned_employee = (is_null($key->Assigned_Employee_Code)) ? '--NA--' : \App\EmployeeTeam::where('employee_code',$key->Assigned_Employee_Code)->value('name');
            // $service_region = find_region($hospital_state);
            // unset($key->Hospital_Id);
            // unset($key->Assigned_Employee_Code);

            // $afterIndex = 7;
            // $key11 = array_merge(array_slice($key->toArray(),0,$afterIndex+1),   array('Region' => ucfirst($service_region))   ,array_slice($key->toArray(),$afterIndex+1));
            // $key11 = array_merge($key11, array('Assigned_Employee_Name' => $assigned_employee));

            // if (in_array($service_region, $region)) {
            array_push($final_data, $key->toArray());
            // array_push($final_data, $key11);
            // }
        }

        // $final_data = array();
        // foreach ($user as $user_temp) {
        //     $hospitals_ids = explode(',',$user_temp->Hospital_Id);
        //     $hospitals = \App\Hospitals::where('customer_id',$user_temp->Id)->get();
        //     $hospitals_name = \App\Hospitals::where('customer_id',$user_temp->Id)->pluck('hospital_name')->all();
        //     $hospital_names = implode(', ', $hospitals_name);
        //     $count = 1;
        //     $hospital_state = \App\Hospitals::where('id',$user_temp->Hospital_Id)->value('state');

        //     $service_region = find_region($hospital_state);
        //     // print_r($user_temp->Id.' - '. $hospitals[0]->id.' - '. $service_region.'<br>');


        //     $city = [];
        //     $state = [];
        //     foreach($hospitals as $hospital){

        //         $dept_ids = explode(',',$hospital->dept_id);
        //         $departments = \App\Departments::whereIn('id',$dept_ids)->pluck('name')->all();
        //         $depart_names = implode(', ', $departments);
        //         $city[] = $hospital->city;
        //         $state[] = $hospital->state;
        //     }
        //     $user_temp->Hospital_Names = $hospital_names;
        //     $user_temp->Departments = $depart_names;
        //     $user_temp->City_Names = implode(',',array_unique($city));
        //     $user_temp->State_Sames = implode(',',array_unique($state));
        //     unset($user_temp->Hospital_Id);

        //     $afterIndex = 9;
        //     $key11 = array_merge(array_slice($user_temp->toArray(),0,$afterIndex+1),   array('Region' => ucfirst($service_region))   ,array_slice($user_temp->toArray(),$afterIndex+1));
        //     // $key11 = array_merge($key11, array('Assigned_Employee_Name' => $assigned_employee));

        //     if (in_array($service_region, $region)) {
        //         array_push($final_data, $key11);
        //     }
        //     // array_push($final_data, $user_temp->toArray());
        // }
        // // dd($final_data);

        if (file_exists(storage_path('exports/').$excelpath.'.xls')) {
            unlink(storage_path('exports/').$excelpath.'.xls');
        }

        Excel::create($excelpath, function ($excel) use ($final_data) {
            $excel->sheet('Sheet1', function ($sheet) use ($final_data) {
                if (count($final_data) != 0) {
                    $sheet->fromArray($final_data);

                    foreach ($final_data as $key => $value) {
                        if ($key % 2 == 0) {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#b8cce4');
                            });
                        } else {
                            $sheet->row($key+2, function ($row) {
                                $row->setBackground('#dbe5f1');
                            });
                        }
                    }
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->setAutoSize(true);
                    $sheet->setWidth('G', 30);
                    $sheet->setWidth('J', 30);
                    $sheet->getStyle('A1:N1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path('/exports/'));
        // })->export('xls');

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
        Mail::to($to_final)->cc($cc_final)
            ->send(new WeeklyEscalationReport($excelname, $excelpath, $daterange_from, $daterange_to));
        unlink(storage_path('exports/').$excelpath.'.xls');

        echo 'success';
    }
}
