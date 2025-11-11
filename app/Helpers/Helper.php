<?php

use App\Models\EmployeeTeam;
use App\Models\ServiceRequests;

function send_sms($template_name, $customer, $servicerequest, $filter_text_msg)
{
    if(!env('SMS_ENABLED')){
        return true; //DO NOT SEND SMS TO CUSTOMER
    } 


    if ($template_name == "status_update") {
        $sms_text = "From=OMSIND&TemplateName=status_update".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1=".$customer->title.
        "&VAR2=".$customer->first_name.
        "&VAR3=".$customer->last_name.
        "&VAR4=".sprintf('%08d', $servicerequest->id).
        "&VAR5=".ucfirst($servicerequest->status);
    }
    elseif ($template_name == "service_notifcation" || $template_name == "enquiry_notification") {
        $sms_text = "From=OMSIND&TemplateName=covid19_alert".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1=".$customer->title." ".$customer->first_name." ".$customer->last_name.
        "&VAR2=".sprintf('%08d', $servicerequest->id);
    } 
    elseif ($template_name == "feedback_notification") {
        $sms_text = "From=OMSIND&TemplateName=feedback_notification".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1=".$customer->title.
        "&VAR2=".$customer->first_name.
        "&VAR3=".$customer->last_name.
        "&VAR4=".sprintf('%08d', $servicerequest->id);
    } elseif ($template_name == "request_created") {
        $sms_text = "From=OMSIND&TemplateName=request_created".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1=".$customer->otp_code;
    } elseif ($template_name == "request_escalated") {
        $sms_text = "From=OMSIND&TemplateName=request_escalated".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1=".$customer->title.
        "&VAR2=".$customer->first_name.
        "&VAR3=".$customer->last_name.
        "&VAR4=".sprintf('%08d', $servicerequest->id);
    } elseif ($template_name == "request_type_changed") {
        $sms_text = "From=OMSIND&TemplateName=request_type_changed".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1=".$customer->title.
        "&VAR2=".$customer->first_name.
        "&VAR3=".$customer->last_name.
        "&VAR4=".sprintf('%08d', $servicerequest->id).
        "&VAR5=".ucfirst($filter_text_msg);
        "&VAR6=".ucfirst($servicerequest->status);
    } elseif ($template_name == "send_otp") {
        $sms_text = "From=OMSIND&TemplateName=send_otp".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1=".$customer->otp_code.
        "&VAR2="."2 hours";
    }elseif ($template_name == "fse_assigned") {
        $sms_text = "From=OMSIND&TemplateName=fse_assigned".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1="."Engineer".
        "&VAR2="."Service ".
        "&VAR3="." contact $customer->first_name $customer->first_name at $customer->mobile_number for MyVoice ID $servicerequest->id";
    }elseif ($template_name == "blank_raw") {
        $sms_text = "From=OMSIND&TemplateName=blank_raw".
        "&To=".urlencode($customer->mobile_number).
        "&VAR1=".$customer->title." ".$customer->first_name." ".$customer->last_name.
        "&VAR2=".$filter_text_msg.
        "&VAR3="."With Best Regards\nTeam Olympus";
    }
    //2factor SMS code

    $curl = curl_init();

    curl_setopt_array($curl, array( 
        CURLOPT_URL => "http://2factor.in/API/V1/6c32b91e-a374-11e8-a895-0200cd936042/SMS/".$customer->mobile_number."/".$customer->otp_code."/send otp",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST", 
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
            "Postman-Token: eccee244-f7e5-42fc-9427-5c7914037e15",
            "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
} 

function send_sms_request_acknowledged($customer, $servicerequest)
{
    Logger('Send Acknowledgement Happy Code');
    if(!env('SMS_ENABLED')){
        return true; //DO NOT SEND SMS TO CUSTOMER
    }

    $apiKey = "6c32b91e-a374-11e8-a895-0200cd936042"; // Replace with your actual 2Factor API Key
    $otp = $servicerequest->happy_code;
    $validity = "5 days";
    $serviceRequestId = $servicerequest->id;
    $phoneNumber = $customer->mobile_number;
    $day = "5 days";
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://2factor.in/API/V1/6c32b91e-a374-11e8-a895-0200cd936042/SMS/'.$phoneNumber.'/'.$otp.'/Acknowledgement-Happy-Code?var1='.$serviceRequestId.'&var2=5%20days',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $response = json_decode($response, true);
    curl_close($curl);
    return $response;
}


function request_progress($request_type, $current_status){
    $current_status = ($current_status == "Re-assigned") ? "Assigned" : $current_status ;
    if($request_type == "service"){
        if(in_array($current_status, ["Received_At_Repair_Center","Quotation_Prepared","PO_Received","Repair_Started","Repair_Completed","Ready_To_Dispatch","Dispatched"])){
            $request_type = "service_repair";
        }
    }
    $request_data = \Config('oly.requests_statuses')[$request_type];
    $current_status_pos = array_search($current_status,array_keys($request_data))+1;
    $pending_statuses = array_slice($request_data,$current_status_pos);
    $pending_statuses_arr = [];
    foreach ($pending_statuses as $key => $value) {
        array_push($pending_statuses_arr, [
            "statusName"=>$key,
            "percentage"=>$value
        ]);
    }
    $complete_percent = $request_data[$current_status];
    return [
        "complete_percent"=>$complete_percent,
        "pending_statuses"=>$pending_statuses_arr
    ];
}

function find_region($state)
{
    $customer_region = '';
    $indian_all_states  = \Config('oly.indian_all_states');

    if (in_array($state, $indian_all_states['north'])) {
        $customer_region =  "north";
    } elseif (in_array($state, $indian_all_states['east'])) {
        $customer_region = "east";
    } elseif (in_array($state, $indian_all_states['south'])) {
        $customer_region =  "south";
    } elseif (in_array($state, $indian_all_states['west'])) {
        $customer_region = "west";
    }
    return $customer_region;
}

function calculate_ratio($data)
{
    $count = (count(array_filter($data)) != 0 ? count(array_filter($data)) : 0);
    if ($count == 0) {
        return 0;
    } else {
        return round(array_sum($data)/$count, 2);
    }
}

function get_enq_type($prod_category){
    $product_types = \Config('oly.enquiry_prod_categories');
    foreach($product_types as $product_type=>$prod_arr){
        if(in_array($prod_category, $prod_arr)){
            return $product_type;
        }
    }
    return "other";
} 

function exportExcel($file_name,$request_type, $statuses)
{
    if(!is_array($request_type)){
        $request_type = [$request_type];
    }
    $export_query = ServiceRequests::join('customers', 'customers.id', '=', 'service_requests.customer_id')
        ->join('hospitals', 'hospitals.id', '=', 'service_requests.hospital_id')
        ->join('departments', 'departments.id', '=', 'service_requests.dept_id')
        ->select(
            'service_requests.*',
            'customers.first_name',
            'customers.last_name',
            'customers.email',
            'customers.sap_customer_id',
            'hospitals.hospital_name',
            'hospitals.state',
            'hospitals.responsible_branch',
            'departments.name as dept_name'
        )
        ->orderBy('created_at', 'DESC')
        ->where('is_practice', false)
        ->whereIn('request_type', $request_type)
        ->with(['timelines' => function($query) {
            $query->orderBy('id','ASC')->where('status', 'Assigned');
        }])
    ->get()->toArray();
    //dd(count($export_query));
    $requests = [];
    foreach ($export_query as $request) {
        $assigned_date = empty($request['timelines']) ? '-' : $request['timelines'][0]['created_at'];
        $request['assigned_date'] = $assigned_date;
        unset($request['timelines']);
        unset($request['remarks']);
       array_push($requests, $request);
    }

    Excel::create($file_name, function ($excel) use ($requests) {
        $excel->sheet('Sheet1', function ($sheet) use ($requests) {
            if (count($requests) != 0) {
                $sheet->fromArray($requests);

                foreach ($requests as $key => $value) {
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
                $sheet->setWidth('H', 30);
                $sheet->getStyle('A1:AN1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
            } else {
                $sheet->setCellValue('A1', 'No requests to display');
                $sheet->row(1, function ($row) {
                    $row->setBackground('#4f81bd');
                });
                $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
            }
        });
    })->export('csv');
}

function addEmployeeName($requests)
{
    foreach ($requests as $request) {
        if(is_null($request->employee_code)){
            $request->employee_name = '-';
        }else{
            $request->employee_name = EmployeeTeam::where('employee_code', $request->employee_code)->value('name');
        }
    }
    return $requests;
}


function toMailKey($emails)
{
    $emails = array_unique($emails);
    $emails_final = [];
    foreach($emails as $email){
        $emails_final[]['email'] = $email;
    }
    return $emails_final;
}
