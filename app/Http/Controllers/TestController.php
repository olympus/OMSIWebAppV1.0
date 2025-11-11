<?php

namespace App\Http\Controllers;
use App\Models\Departments;
use App\Exports\UsersExport;
use App\Mail\ArchiveCustomerDataMail;
use App\Mail\ArchiveRequestDataMail;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\StatusTimeline;
use Auth;
use Carbon\Carbon;
use DB;
use Excel;
use Exception;
use File;
use Illuminate\Http\Request;
use Log;
use Mail;

class TestController extends Controller
{

    public function newServiceRequestOld(Request $request)
    {
        $month = date('m');
        //dd($month);
        if($month >= 4){
            $y = "2017";
            $pt = date('Y', strtotime('0 year'));
            $fy = $y."-04-01".":".$pt."-03-31";

            $start_date = $y."-04-01";
            $end_date = $pt."-03-31";

            //dd($start_date, $end_date);
            //dd($y, $pt, $fy);

            ServiceRequests::query()
            ->where('created_at','>=',$start_date.' 00:00:00')
            ->where('created_at','<=', $end_date.' 23:59:59')
            ->where('status', '=' ,'Closed')
            ->each(function ($oldRecord) {
                $newPost = $oldRecord->replicate();
                $newPost->id = $oldRecord->id;
                $newPost->created_at = $oldRecord->created_at;
                $newPost->updated_at = $oldRecord->updated_at;
                $newPost->setTable('archive_service_requests');
                $newPost->save();
                $oldRecord->delete();
            });

        }else{
            // $y = date('Y', strtotime('-6 year'));
            // $pt = date('Y',strtotime('0 year'));
            // $fy = $y."-04-01".":".$pt."-03-31";

            // //dd($y, $pt, $fy,'r');

            // //dd($fy); die;
            // $start_date = $y."-04-01";
            // $end_date = $pt."-03-31";

            $y = "2017";
            $pt = date('Y', strtotime('-1 year'));
            $fy = $y."-04-01".":".$pt."-03-31";

            $start_date = $y."-04-01";
            $end_date = $pt."-03-31";

            //dd($start_date, $end_date);
            //dd($y, $pt, $fy);

            ServiceRequests::query()
            ->where('created_at','>=',$start_date.' 00:00:00')
            ->where('created_at','<=', $end_date.' 23:59:59')
            ->where('status', '=' ,'Closed')
            ->each(function ($oldRecord) {
                $newPost = $oldRecord->replicate();
                $newPost->id = $oldRecord->id;
                $newPost->created_at = $oldRecord->created_at;
                $newPost->updated_at = $oldRecord->updated_at;
                $newPost->setTable('archive_service_requests');
                $newPost->save();
                $oldRecord->delete();
            });
        }
        return $fy;
    }

    public function newServiceRequest(Request $request)
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', -1);

        $month = date('m');
        //dd($month);
        if($month >= 4){
            $y = "2017";
            $pt = date('Y', strtotime('0 year'));
            $fy = $y."-04-01".":".$pt."-03-31";

            $start_date = $y."-04-01";
            $end_date = $pt."-03-31";

            //dd($start_date, $end_date);
            //dd($y, $pt, $fy);

            ServiceRequests::query()
            ->where('created_at','>=',$start_date.' 00:00:00')
            ->where('created_at','<=', $end_date.' 23:59:59')
            ->where('status', '=' ,'Closed')
            ->each(function ($oldRecord) {
                $newPost = $oldRecord->replicate();
                $newPost->id = $oldRecord->id;
                $newPost->created_at = $oldRecord->created_at;
                $newPost->updated_at = $oldRecord->updated_at;
                $newPost->setTable('archive_service_requests');
                $newPost->save();
                $oldRecord->delete();
            });

        }else{
            // $y = date('Y', strtotime('-6 year'));
            // $pt = date('Y',strtotime('0 year'));
            // $fy = $y."-04-01".":".$pt."-03-31";

            // //dd($y, $pt, $fy,'r');

            // //dd($fy); die;
            // $start_date = $y."-04-01";
            // $end_date = $pt."-03-31";

            $y = "2017";
            $pt = date('Y', strtotime('-1 year'));
            $fy = $y."-04-01".":".$pt."-03-31";

            $start_date = $y."-04-01";
            $end_date = $pt."-03-31";

            //dd($start_date, $end_date);
            //dd($y, $pt, $fy);

            ServiceRequests::query()
            ->where('created_at','>=',$start_date.' 00:00:00')
            ->where('created_at','<=', $end_date.' 23:59:59')
            ->where('status', '=' ,'Closed')
            ->each(function ($oldRecord) {
                $newPost = $oldRecord->replicate();
                $newPost->id = $oldRecord->id;
                $newPost->created_at = $oldRecord->created_at;
                $newPost->updated_at = $oldRecord->updated_at;
                $newPost->setTable('archive_service_requests');
                $newPost->save();
                $oldRecord->delete();
            });
        }
    }

    public function sendMailRequestData(Request $request)
    {

        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        $status = '';
        $request_type = '';
        $sub_type = '';
        $cc_final = [];
        $from_date = $request->from_date.' 00:00:00';
        $to_date = $request->to_date.' 23:59:59';

        $cc_email = $request->cc_email;
        $cc_emails = explode(',', $cc_email);
        foreach($cc_emails as $cc_email_one){
            if($cc_email_one != ""){
                $cc_final[]['email'] = trim($cc_email_one, " ");
            }
        }
        $daterange_from = date("d-M-Y", strtotime($request->from_date));
        $daterange_to = date("d-M-Y", strtotime($request->to_date));
        if($request->request_type == 'all'){
            $request_type = ['service','academic','enquiry'];
        }else{
            $request_type =  [$request->request_type];
        }

        if($request->request_type == 'service'){
            if($request->sub_type == 'all'){
                $sub_type = ['BreakDown Call','Service Support'];
            }else{
                $sub_type =  [$request->sub_type];
            }

            if($request->status == 'all'){
                $status = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
            }else{
                if($request->status == 'sc'){
                    $status =  ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'];
                }elseif($request->status == 'received'){
                    $status =  ['Received'];
                }elseif($request->status == 'closed'){
                    $status =  ['Closed'];
                }elseif($request->status == 'assigned'){
                    $status =  ['Assigned','Re-assigned'];
                }elseif($request->status == 'attended'){
                    $status =  ['Attended'];
                }elseif($request->status == 'rarp'){
                    $status =  ['Received_At_Repair_Center'];
                }elseif($request->status == 'qp'){
                    $status =  ['Quotation_Prepared'];
                }elseif($request->status == 'por'){
                    $status =  ['PO_Received'];
                }elseif($request->status == 'rs'){
                    $status =  ['Repair_Started'];
                }elseif($request->status == 'rc'){
                    $status =  ['Repair_Completed'];
                }elseif($request->status == 'rtd'){
                    $status =  ['Ready_To_Dispatch'];
                }elseif($request->status == 'dispatched'){
                    $status =  ['Dispatched'];
                }
            }

            $practice = [0];
        }elseif($request->request_type == 'academic'){
            if($request->sub_type == 'all'){
                $sub_type = ['Conference','Training','Clinical Info'];
            }else{
                $sub_type =  [$request->sub_type];
            }

            if($request->status == 'all'){
                $status = ['Received','Assigned','Attended','Closed'];
            }else{
                $status =  [$request->status];
            }
            $practice = [0];
        }elseif($request->request_type == 'enquiry'){
            if($request->sub_type == 'all'){
                $sub_type = ['Product Info','Demonstration','Quotations'];
            }else{
                $sub_type =  [$request->sub_type];
            }

            if($request->status == 'all'){
                $status = ['Received','Assigned','Attended','Closed'];
            }else{
                $status =  [$request->status];
            }
            $practice = [0];
        }else{
            $sub_type = ['BreakDown Call','Service Support','Conference','Training','Clinical Info', 'Product Info','Demonstration','Quotations'];
            $status = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
            $practice = [0,1];
        }
        $archive_data = ArchiveServiceRequests::
            whereIn('request_type', $request_type)
            ->whereIn('sub_type', $sub_type)
            ->whereIn('status', $status)
            ->with(['statusTimelineData'])
            ->join('customers', 'customers.id', '=', 'archive_service_requests.customer_id')
            ->join('hospitals', 'hospitals.id', '=', 'archive_service_requests.hospital_id')
            ->join('departments', 'departments.id', '=', 'archive_service_requests.dept_id')
            ->leftJoin('employee_team', 'employee_team.employee_code', '=', 'archive_service_requests.employee_code')
            ->whereBetween('archive_service_requests.created_at', [$from_date, $to_date])
            ->whereIn('archive_service_requests.is_practice', $practice)
            ->orderBy('archive_service_requests.id','asc')
            ->select(
                'archive_service_requests.id',
                'archive_service_requests.cvm_id',
                'archive_service_requests.import_id',
                'archive_service_requests.request_type',
                'archive_service_requests.sub_type',
                'archive_service_requests.customer_id',
                'archive_service_requests.hospital_id',
                'archive_service_requests.dept_id',
                'archive_service_requests.remarks',
                'archive_service_requests.sap_id',
                'archive_service_requests.sfdc_id',
                'archive_service_requests.sfdc_customer_id',
                'archive_service_requests.product_category',
                'archive_service_requests.employee_code',
                'archive_service_requests.last_updated_by',
                'archive_service_requests.status',
                'archive_service_requests.is_escalated',
                'archive_service_requests.escalation_count',
                'archive_service_requests.escalation_assign1',
                'archive_service_requests.escalation_assign2',
                'archive_service_requests.escalation_assign3',
                'archive_service_requests.escalation_assign4',
                'archive_service_requests.escalation_reasons',
                'archive_service_requests.escalation_remarks',
                'archive_service_requests.escalated_at',
                'archive_service_requests.feedback_id',
                'archive_service_requests.feedback_requested',
                'archive_service_requests.is_practice',
                'archive_service_requests.created_at',
                'archive_service_requests.updated_at',
                'archive_service_requests.feedback_requested',
                'archive_service_requests.feedback_requested',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'customers.sap_customer_id',
                'hospitals.hospital_name',
                'hospitals.state',
                'hospitals.responsible_branch',
                'departments.name'
            )
        ->get();
        $current_data = ServiceRequests::
            whereIn('request_type', $request_type)
            ->whereIn('sub_type', $sub_type)
            ->whereIn('status', $status)
            ->with(['statusTimelineData'])
            ->join('customers', 'customers.id', '=', 'service_requests.customer_id')
            ->join('hospitals', 'hospitals.id', '=', 'service_requests.hospital_id')
            ->join('departments', 'departments.id', '=', 'service_requests.dept_id')
            ->leftJoin('employee_team', 'employee_team.employee_code', '=', 'service_requests.employee_code')
            ->whereBetween('service_requests.created_at', [$from_date, $to_date])
            ->whereIn('service_requests.is_practice', $practice)
            ->orderBy('service_requests.id','asc')
            ->select(
                'service_requests.id',
                'service_requests.cvm_id',
                'service_requests.import_id',
                'service_requests.request_type',
                'service_requests.sub_type',
                'service_requests.customer_id',
                'service_requests.hospital_id',
                'service_requests.dept_id',
                'service_requests.remarks',
                'service_requests.sap_id',
                'service_requests.sfdc_id',
                'service_requests.sfdc_customer_id',
                'service_requests.product_category',
                'service_requests.employee_code',
                'service_requests.last_updated_by',
                'service_requests.status',
                'service_requests.is_escalated',
                'service_requests.escalation_count',
                'service_requests.escalation_assign1',
                'service_requests.escalation_assign2',
                'service_requests.escalation_assign3',
                'service_requests.escalation_assign4',
                'service_requests.escalation_reasons',
                'service_requests.escalation_remarks',
                'service_requests.escalated_at',
                'service_requests.feedback_id',
                'service_requests.feedback_requested',
                'service_requests.is_practice',
                'service_requests.created_at',
                'service_requests.updated_at',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'customers.sap_customer_id',
                'hospitals.hospital_name',
                'hospitals.state',
                'hospitals.responsible_branch',
                'departments.name'
            )
        ->get();
        $new_data = $archive_data->merge($current_data);
        $file_name = 'request'.time();
        $file_path = storage_path().'/app/public/request';
        $final_data = [];
        foreach ($new_data as $new_datas) {
            if(!empty($new_datas->remarks)){
                $new_datas->remarks = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->remarks);
            }else{
                $new_datas->remarks = NULL;
            }

            if(!empty($new_datas->escalation_reasons)){
                $new_datas->escalation_reasons = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->escalation_reasons);
            }else{
                $new_datas->escalation_reasons = NULL;
            }

            if(!empty($new_datas->escalation_remarks)){
                $new_datas->escalation_remarks = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->escalation_remarks);
            }else{
                $new_datas->escalation_remarks = NULL;
            }

            // $new_datas->escalation_reasons = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->escalation_reasons);
            // $new_datas->escalation_remarks = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->escalation_remarks);
            $new_datas->Created_At = Carbon::parse($new_datas->Created_At)->format('d/m/Y H:i:s');
            $new_datas->Updated_At = Carbon::parse($new_datas->Updated_At)->format('d/m/Y H:i:s');

            if(!empty($new_datas->statusTimelineData) ||$new_datas->statusTimelineData != null || $new_datas->statusTimelineData !=  ''){
                $received = $new_datas->statusTimelineData->where('status', 'Received')->first();
                if(!empty($received) || $received){
                    $new_datas->received = $received->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->received = '';
                }

                $assigned = $new_datas->statusTimelineData->where('status', 'Assigned')->first();
                if(!empty($assigned) || $assigned){
                    $new_datas->assigned = $assigned->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->assigned = '';
                }

                $attended = $new_datas->statusTimelineData->where('status', 'Attended')->first();
                if(!empty($attended) || $attended){
                    $new_datas->attended = $attended->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->attended = '';
                }

                $quotation_prepared = $new_datas->statusTimelineData->where('status', 'Quotation_Prepared')->first();
                if(!empty($quotation_prepared) || $quotation_prepared){
                    $new_datas->quotation_prepared = $quotation_prepared->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->quotation_prepared = '';
                }

                $po_received = $new_datas->statusTimelineData->where('status', 'PO_Received')->first();
                if(!empty($po_received) || $po_received){
                    $new_datas->po_received = $po_received->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->po_received = '';
                }

                $dispatched = $new_datas->statusTimelineData->where('status', 'Dispatched')->first();
                if(!empty($dispatched) || $dispatched){
                    $new_datas->dispatched = $dispatched->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->dispatched = '';
                }

                $received_at_repair_center = $new_datas->statusTimelineData->where('status', 'Received_At_Repair_Center')->first();
                if(!empty($received_at_repair_center) || $received_at_repair_center){
                    $new_datas->received_at_repair_center = $received_at_repair_center->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->received_at_repair_center = '';
                }

                $repair_started = $new_datas->statusTimelineData->where('status', 'Repair_Started')->first();
                if(!empty($repair_started) || $repair_started){
                    $new_datas->repair_started = $repair_started->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->repair_started = '';
                }

                $repair_completed = $new_datas->statusTimelineData->where('status', 'Repair_Completed')->first();
                if(!empty($repair_completed) || $repair_completed){
                    $new_datas->repair_completed = $repair_completed->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->repair_completed = '';
                }

                $ready_to_dispatch = $new_datas->statusTimelineData->where('status', 'Ready_To_Dispatch')->first();
                if(!empty($ready_to_dispatch) || $ready_to_dispatch){
                    $new_datas->ready_to_dispatch = $ready_to_dispatch->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->ready_to_dispatch = '';
                }

                $closed = $new_datas->statusTimelineData->where('status', 'Closed')->first();
                if(!empty($closed) || $closed){
                    $new_datas->closed = $closed->created_at->format('d-m-y h:i:A');
                }else{
                    $new_datas->closed = '';
                }
            }else{
                $new_datas->received = '';
                $new_datas->assigned = '';
                $new_datas->attended = '';
                $new_datas->quotation_prepared = '';
                $new_datas->po_received = '';
                $new_datas->dispatched = '';
                $new_datas->received_at_repair_center = '';
                $new_datas->repair_started = '';
                $new_datas->repair_completed = '';
                $new_datas->ready_to_dispatch = '';
                $new_datas->closed = '';
            }
            $data = [
                'ID' => $new_datas->id,
                'CVM ID' => $new_datas->cvm_id,
                'Import Id' => $new_datas->import_id,
                'Request Type' => $new_datas->request_type,
                'Sub Type' => $new_datas->sub_type,
                'Customer Id' => $new_datas->customer_id,
                'Hospital Id' => $new_datas->hospital_id,
                'Dept Id' => $new_datas->dept_id,
                'Remarks' => $new_datas->remarks,
                'Sap Id' => $new_datas->sap_id,
                'Sfdc Id' => $new_datas->sfdc_id,
                'Sfdc Customer Id' => $new_datas->sfdc_customer_id,
                'Product Category' => $new_datas->product_category,
                'Employee Code' => $new_datas->employee_code,
                'Last Updated By' => $new_datas->last_updated_by,
                'Status' => $new_datas->status,
                'Is Escalated' => $new_datas->is_escalated,
                'Escalation Count' => $new_datas->escalation_count,
                'Escalation Assign1' => $new_datas->escalation_assign1,
                'Escalation Assign2' => $new_datas->escalation_assign2,
                'Escalation Assign3' => $new_datas->escalation_assign3,
                'Escalation Assign4' => $new_datas->escalation_assign4,
                'Escalation Reasons' => $new_datas->escalation_reasons,
                'Escalation Remarks' => $new_datas->escalation_remarks,
                'Escalated At' => $new_datas->escalated_at,
                'Feedback Id' => $new_datas->feedback_id,
                'Feedback Requested' => $new_datas->feedback_requested,
                'Is Practice' => $new_datas->is_practice,
                'Created At' => $new_datas->created_at,
                'Updated At' => $new_datas->updated_at,
                'First Name' => $new_datas->first_name,
                'Last Name' => $new_datas->last_name,
                'Email' => $new_datas->email,
                'Sap Customer Id' => $new_datas->sap_customer_id,
                'Hospital Name' => $new_datas->hospital_name,
                'State' => $new_datas->state,
                'Responsible Branch' => $new_datas->responsible_branch,
                'Department Name' => $new_datas->name,
                'Received'=>$new_datas->received,
                'Assigned'=>$new_datas->assigned,
                'Attended'=>$new_datas->attended,
                'Received_At_Repair_Center'=>$new_datas->received_at_repair_center,
                'Quotation_Prepared'=>$new_datas->quotation_prepared,
                'PO_Received'=>$new_datas->po_received,
                'Repair_Started'=>$new_datas->repair_started,
                'Repair_Completed'=>$new_datas->repair_completed,
                'Ready_To_Dispatch'=>$new_datas->ready_to_dispatch,
                'Dispatched'=>$new_datas->dispatched,
                'Closed'=>$new_datas->closed,
            ];
            array_push($final_data, $data);
        }
        Excel::create($file_name, function ($excel) use ($final_data) {
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
                    $sheet->setWidth('H', 30);
                    $sheet->getStyle('A1:AM1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->store('xls', storage_path().'/app/public/request');

        $file_size =  storage_path().'/app/public/request/'.$file_name.'.xls';
        $file = File::size($file_size);
        Log::info($file);
        if($file <= 11534336){
            Mail::to($request->email)->cc($cc_final)
            ->send(new ArchiveRequestDataMail($file_name, $file_path,$daterange_from, $daterange_to));
            unlink(storage_path().'/app/public/request/'.$file_name.'.xls');
            return response()->json(['status' => 1, 'message' => 'success']);
        }else{
            unlink(storage_path().'/app/public/request/'.$file_name.'.xls');
            return response()->json(['status' => 2, 'message' => 'Excel size is more than 11 MB so it can not send on mail.']);
        }
    }

    public function sendMailCustomerData(Request $request)
    {

        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        if($request->customer_from_date && $request->customer_to_date){
            $from_date = $request->customer_from_date.' 00:00:00';
            $to_date = $request->customer_to_date.' 23:59:59';
        }else{
            $month = date('m');
            if($month >= 4){
                $y = date('Y');
                $pt = date('Y', strtotime('+1 year'));
            }else{
                $pt = date('Y');
                $y = date('Y', strtotime('-1 year'));
            }
            $from_date = $y."-04-01".' 00:00:00';
            $to_date = $pt."-03-31".' 23:59:59';
        }
        $cc_final = [];
        $cc_email = $request->customer_cc_email;
        $cc_emails = explode(',', $cc_email);
        foreach($cc_emails as $cc_email_one){
            if($cc_email_one != ""){
                $cc_final[]['email'] = trim($cc_email_one, " ");
            }
        }

        //$file_name = 'customer';
        $file_name = 'customer'.time();
        $file_path = storage_path().'/app/public/customer';

        $user = Customers::whereBetween('created_at', [$from_date, $to_date])->select('id', 'sap_customer_id', 'customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
            ->where('email', 'NOT LIKE', '%@olympus.com%')
            ->get();
        foreach ($user as $user_temp) {
            $hospitals = Hospitals::where('customer_id', $user_temp->id)->get();

            $hospitals_name = Hospitals::where('customer_id', $user_temp->id)->pluck('hospital_name')->all();
            $hospital_names = implode(', ', $hospitals_name);
            $city = [];
            $state = [];
            $region = [];
            $branch = [];
            foreach ($hospitals as $hospital) {
                $dept_ids = explode(',', $hospital->dept_id);
                $departments = Departments::whereIn('id', $dept_ids)->pluck('name')->all();
                $depart_names = implode(', ', $departments);
                $region[] = ucfirst(find_region($hospital->state));
                $city[] = $hospital->city;
                $state[] = $hospital->state;
                $branch[] = $hospital->responsible_branch;
            }
            $user_temp->City = implode(',', array_unique($city));
            $user_temp->State = implode(',', array_unique($state));
            $user_temp->Region = implode(',', array_unique($region));
            $user_temp->Branch = implode(',', array_unique($branch));
            $user_temp->hospital_names = $hospital_names;

            $user_temp->departments = $depart_names;

        }
        Excel::create($file_name, function ($excel) use ($user) {
            $excel->sheet('Sheet 1', function ($sheet) use ($user) {
                $sheet->fromArray($user);
            });
        })->store('xls', storage_path().'/app/public/customer');

        $file_size =  storage_path().'/app/public/customer/'.$file_name.'.xls';
        $file = File::size($file_size);
        Log::info($file);

        if($file <= 11534336){
            Mail::cc($cc_final)
            ->send(new ArchiveCustomerDataMail($file_name, $file_path,$from_date, $to_date));
            unlink(storage_path().'/app/public/customer/'.$file_name.'.xls');
            return response()->json(['status' => 1, 'message' => 'success']);
        }else{
            unlink(storage_path().'/app/public/customer/'.$file_name.'.xls');
            return response()->json(['status' => 2, 'message' => 'Excel size is more than 11 MB so it can not send on mail.']);
        }
    }

    public function downloadRequestData(Request $request)
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        try{
            $status = '';
            $request_type = '';
            $sub_type = '';
            $from_date = $request->from_date.' 00:00:00';
            $to_date = $request->to_date.' 23:59:59';

            $daterange_from = date("d-M-Y", strtotime($request->from_date));
            $daterange_to = date("d-M-Y", strtotime($request->to_date));
            if($request->request_type == 'all'){
                $request_type = ['service','academic','enquiry'];
            }else{
                $request_type =  [$request->request_type];
            }

            if($request->request_type == 'service'){
                if($request->sub_type == 'all'){
                    $sub_type = ['BreakDown Call','Service Support'];
                }else{
                    $sub_type =  [$request->sub_type];
                }

                if($request->status == 'all'){
                    $status = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
                }else{
                    if($request->status == 'sc'){
                        $status =  ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'];
                    }elseif($request->status == 'received'){
                        $status =  ['Received'];
                    }elseif($request->status == 'closed'){
                        $status =  ['Closed'];
                    }elseif($request->status == 'assigned'){
                        $status =  ['Assigned','Re-assigned'];
                    }elseif($request->status == 'attended'){
                        $status =  ['Attended'];
                    }elseif($request->status == 'rarp'){
                        $status =  ['Received_At_Repair_Center'];
                    }elseif($request->status == 'qp'){
                        $status =  ['Quotation_Prepared'];
                    }elseif($request->status == 'por'){
                        $status =  ['PO_Received'];
                    }elseif($request->status == 'rs'){
                        $status =  ['Repair_Started'];
                    }elseif($request->status == 'rc'){
                        $status =  ['Repair_Completed'];
                    }elseif($request->status == 'rtd'){
                        $status =  ['Ready_To_Dispatch'];
                    }elseif($request->status == 'dispatched'){
                        $status =  ['Dispatched'];
                    }
                }

                $practice = [0];
            }elseif($request->request_type == 'academic'){
                if($request->sub_type == 'all'){
                    $sub_type = ['Conference','Training','Clinical Info'];
                }else{
                    $sub_type =  [$request->sub_type];
                }

                if($request->status == 'all'){
                    $status = ['Received','Assigned','Attended','Closed'];
                }else{
                    $status =  [$request->status];
                }
                $practice = [0];
            }elseif($request->request_type == 'enquiry'){
                if($request->sub_type == 'all'){
                    $sub_type = ['Product Info','Demonstration','Quotations'];
                }else{
                    $sub_type =  [$request->sub_type];
                }

                if($request->status == 'all'){
                    $status = ['Received','Assigned','Attended','Closed'];
                }else{
                    $status =  [$request->status];
                }
                $practice = [0];
            }else{
                $sub_type = ['BreakDown Call','Service Support','Conference','Training','Clinical Info', 'Product Info','Demonstration','Quotations'];
                $status = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
                $practice = [0,1];
            }
            $archive_data = ArchiveServiceRequests::
                whereIn('request_type', $request_type)
                ->whereIn('sub_type', $sub_type)
                ->whereIn('status', $status)
                ->with(['statusTimelineData'])
                ->join('customers', 'customers.id', '=', 'archive_service_requests.customer_id')
                ->join('hospitals', 'hospitals.id', '=', 'archive_service_requests.hospital_id')
                ->join('departments', 'departments.id', '=', 'archive_service_requests.dept_id')
                ->leftJoin('employee_team', 'employee_team.employee_code', '=', 'archive_service_requests.employee_code')
                ->whereBetween('archive_service_requests.created_at', [$from_date, $to_date])
                ->whereIn('archive_service_requests.is_practice', $practice)
                ->orderBy('archive_service_requests.id','asc')
                ->select(
                    'archive_service_requests.id',
                    'archive_service_requests.cvm_id',
                    'archive_service_requests.import_id',
                    'archive_service_requests.request_type',
                    'archive_service_requests.sub_type',
                    'archive_service_requests.customer_id',
                    'archive_service_requests.hospital_id',
                    'archive_service_requests.dept_id',
                    'archive_service_requests.remarks',
                    'archive_service_requests.sap_id',
                    'archive_service_requests.sfdc_id',
                    'archive_service_requests.sfdc_customer_id',
                    'archive_service_requests.product_category',
                    'archive_service_requests.employee_code',
                    'archive_service_requests.last_updated_by',
                    'archive_service_requests.status',
                    'archive_service_requests.is_escalated',
                    'archive_service_requests.escalation_count',
                    'archive_service_requests.escalation_assign1',
                    'archive_service_requests.escalation_assign2',
                    'archive_service_requests.escalation_assign3',
                    'archive_service_requests.escalation_assign4',
                    'archive_service_requests.escalation_reasons',
                    'archive_service_requests.escalation_remarks',
                    'archive_service_requests.escalated_at',
                    'archive_service_requests.feedback_id',
                    'archive_service_requests.feedback_requested',
                    'archive_service_requests.is_practice',
                    'archive_service_requests.created_at',
                    'archive_service_requests.updated_at',
                    'archive_service_requests.feedback_requested',
                    'archive_service_requests.feedback_requested',
                    'customers.first_name',
                    'customers.last_name',
                    'customers.email',
                    'customers.sap_customer_id',
                    'hospitals.hospital_name',
                    'hospitals.state',
                    'hospitals.responsible_branch',
                    'departments.name'
                )
            ->get();
            $current_data = ServiceRequests::
                whereIn('request_type', $request_type)
                ->whereIn('sub_type', $sub_type)
                ->whereIn('status', $status)
                ->with(['statusTimelineData'])
                ->join('customers', 'customers.id', '=', 'service_requests.customer_id')
                ->join('hospitals', 'hospitals.id', '=', 'service_requests.hospital_id')
                ->join('departments', 'departments.id', '=', 'service_requests.dept_id')
                ->leftJoin('employee_team', 'employee_team.employee_code', '=', 'service_requests.employee_code')
                ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                ->whereIn('service_requests.is_practice', $practice)
                ->orderBy('service_requests.id','asc')
                ->select(
                    'service_requests.id',
                    'service_requests.cvm_id',
                    'service_requests.import_id',
                    'service_requests.request_type',
                    'service_requests.sub_type',
                    'service_requests.customer_id',
                    'service_requests.hospital_id',
                    'service_requests.dept_id',
                    'service_requests.remarks',
                    'service_requests.sap_id',
                    'service_requests.sfdc_id',
                    'service_requests.sfdc_customer_id',
                    'service_requests.product_category',
                    'service_requests.employee_code',
                    'service_requests.last_updated_by',
                    'service_requests.status',
                    'service_requests.is_escalated',
                    'service_requests.escalation_count',
                    'service_requests.escalation_assign1',
                    'service_requests.escalation_assign2',
                    'service_requests.escalation_assign3',
                    'service_requests.escalation_assign4',
                    'service_requests.escalation_reasons',
                    'service_requests.escalation_remarks',
                    'service_requests.escalated_at',
                    'service_requests.feedback_id',
                    'service_requests.feedback_requested',
                    'service_requests.is_practice',
                    'service_requests.created_at',
                    'service_requests.updated_at',
                    'customers.first_name',
                    'customers.last_name',
                    'customers.email',
                    'customers.sap_customer_id',
                    'hospitals.hospital_name',
                    'hospitals.state',
                    'hospitals.responsible_branch',
                    'departments.name'
                )
            ->get();

            $new_data = $archive_data->merge($current_data);
            $file_name = 'request'.time();
            $file_path = storage_path().'/app/public/request';
            $final_data = [];
            foreach ($new_data as $new_datas) {
                // $new_datas->remarks = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->remarks);
                // $new_datas->escalation_reasons = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->escalation_reasons);
                // $new_datas->escalation_remarks = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->escalation_remarks);
                if(!empty($new_datas->remarks)){
                    $new_datas->remarks = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->remarks);
                }else{
                    $new_datas->remarks = '';
                }

                if(!empty($new_datas->escalation_reasons)){
                    $new_datas->escalation_reasons = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->escalation_reasons);
                }else{
                    $new_datas->escalation_reasons = '';
                }

                if(!empty($new_datas->escalation_remarks)){
                    $new_datas->escalation_remarks = preg_replace('/[^A-Za-z0-9\-]/', ' ', $new_datas->escalation_remarks);
                }else{
                    $new_datas->escalation_remarks = '';
                }
                $new_datas->Created_At = Carbon::parse($new_datas->Created_At)->format('d/m/Y H:i:s');
                $new_datas->Updated_At = Carbon::parse($new_datas->Updated_At)->format('d/m/Y H:i:s');

                if(!empty($new_datas->statusTimelineData) ||$new_datas->statusTimelineData != null || $new_datas->statusTimelineData !=  ''){
                    $received = $new_datas->statusTimelineData->where('status', 'Received')->first();
                    if(!empty($received) || $received){
                        $new_datas->received = $received->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->received = '';
                    }

                    $assigned = $new_datas->statusTimelineData->where('status', 'Assigned')->first();
                    if(!empty($assigned) || $assigned){
                        $new_datas->assigned = $assigned->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->assigned = '';
                    }

                    $attended = $new_datas->statusTimelineData->where('status', 'Attended')->first();
                    if(!empty($attended) || $attended){
                        $new_datas->attended = $attended->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->attended = '';
                    }

                    $quotation_prepared = $new_datas->statusTimelineData->where('status', 'Quotation_Prepared')->first();
                    if(!empty($quotation_prepared) || $quotation_prepared){
                        $new_datas->quotation_prepared = $quotation_prepared->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->quotation_prepared = '';
                    }

                    $po_received = $new_datas->statusTimelineData->where('status', 'PO_Received')->first();
                    if(!empty($po_received) || $po_received){
                        $new_datas->po_received = $po_received->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->po_received = '';
                    }

                    $dispatched = $new_datas->statusTimelineData->where('status', 'Dispatched')->first();
                    if(!empty($dispatched) || $dispatched){
                        $new_datas->dispatched = $dispatched->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->dispatched = '';
                    }

                    $received_at_repair_center = $new_datas->statusTimelineData->where('status', 'Received_At_Repair_Center')->first();
                    if(!empty($received_at_repair_center) || $received_at_repair_center){
                        $new_datas->received_at_repair_center = $received_at_repair_center->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->received_at_repair_center = '';
                    }

                    $repair_started = $new_datas->statusTimelineData->where('status', 'Repair_Started')->first();
                    if(!empty($repair_started) || $repair_started){
                        $new_datas->repair_started = $repair_started->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->repair_started = '';
                    }

                    $repair_completed = $new_datas->statusTimelineData->where('status', 'Repair_Completed')->first();
                    if(!empty($repair_completed) || $repair_completed){
                        $new_datas->repair_completed = $repair_completed->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->repair_completed = '';
                    }

                    $ready_to_dispatch = $new_datas->statusTimelineData->where('status', 'Ready_To_Dispatch')->first();
                    if(!empty($ready_to_dispatch) || $ready_to_dispatch){
                        $new_datas->ready_to_dispatch = $ready_to_dispatch->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->ready_to_dispatch = '';
                    }

                    $closed = $new_datas->statusTimelineData->where('status', 'Closed')->first();
                    if(!empty($closed) || $closed){
                        $new_datas->closed = $closed->created_at->format('d-m-y h:i:A');
                    }else{
                        $new_datas->closed = '';
                    }
                }else{
                    $new_datas->received = '';
                    $new_datas->assigned = '';
                    $new_datas->attended = '';
                    $new_datas->quotation_prepared = '';
                    $new_datas->po_received = '';
                    $new_datas->dispatched = '';
                    $new_datas->received_at_repair_center = '';
                    $new_datas->repair_started = '';
                    $new_datas->repair_completed = '';
                    $new_datas->ready_to_dispatch = '';
                    $new_datas->closed = '';
                }
                $data = [
                    'ID' => $new_datas->id,
                    'CVM ID' => $new_datas->cvm_id,
                    'Import Id' => $new_datas->import_id,
                    'Request Type' => $new_datas->request_type,
                    'Sub Type' => $new_datas->sub_type,
                    'Customer Id' => $new_datas->customer_id,
                    'Hospital Id' => $new_datas->hospital_id,
                    'Dept Id' => $new_datas->dept_id,
                    'Remarks' => $new_datas->remarks,
                    'Sap Id' => $new_datas->sap_id,
                    'Sfdc Id' => $new_datas->sfdc_id,
                    'Sfdc Customer Id' => $new_datas->sfdc_customer_id,
                    'Product Category' => $new_datas->product_category,
                    'Employee Code' => $new_datas->employee_code,
                    'Last Updated By' => $new_datas->last_updated_by,
                    'Status' => $new_datas->status,
                    'Is Escalated' => $new_datas->is_escalated,
                    'Escalation Count' => $new_datas->escalation_count,
                    'Escalation Assign1' => $new_datas->escalation_assign1,
                    'Escalation Assign2' => $new_datas->escalation_assign2,
                    'Escalation Assign3' => $new_datas->escalation_assign3,
                    'Escalation Assign4' => $new_datas->escalation_assign4,
                    'Escalation Reasons' => $new_datas->escalation_reasons,
                    'Escalation Remarks' => $new_datas->escalation_remarks,
                    'Escalated At' => $new_datas->escalated_at,
                    'Feedback Id' => $new_datas->feedback_id,
                    'Feedback Requested' => $new_datas->feedback_requested,
                    'Is Practice' => $new_datas->is_practice,
                    'Created At' => $new_datas->created_at,
                    'Updated At' => $new_datas->updated_at,
                    'First Name' => $new_datas->first_name,
                    'Last Name' => $new_datas->last_name,
                    'Email' => $new_datas->email,
                    'Sap Customer Id' => $new_datas->sap_customer_id,
                    'Hospital Name' => $new_datas->hospital_name,
                    'State' => $new_datas->state,
                    'Responsible Branch' => $new_datas->responsible_branch,
                    'Department Name' => $new_datas->name,
                    'Received'=>$new_datas->received,
                    'Assigned'=>$new_datas->assigned,
                    'Attended'=>$new_datas->attended,
                    'Received_At_Repair_Center'=>$new_datas->received_at_repair_center,
                    'Quotation_Prepared'=>$new_datas->quotation_prepared,
                    'PO_Received'=>$new_datas->po_received,
                    'Repair_Started'=>$new_datas->repair_started,
                    'Repair_Completed'=>$new_datas->repair_completed,
                    'Ready_To_Dispatch'=>$new_datas->ready_to_dispatch,
                    'Dispatched'=>$new_datas->dispatched,
                    'Closed'=>$new_datas->closed,
                ];
                array_push($final_data, $data);
            }

            Excel::create($file_name, function ($excel) use ($final_data) {
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
                        $sheet->setWidth('H', 30);
                        $sheet->getStyle('A1:AM1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                    } else {
                        $sheet->setCellValue('A1', 'No requests to display');
                        $sheet->row(1, function ($row) {
                            $row->setBackground('#4f81bd');
                        });
                        $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                    }
                });
            })->export('xls');
            return back();
        }catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getRequestStatus(Request $request,$id){
        $status = StatusTimeline::where('request_id', $id)->select('status','created_at','updated_at','request_id','id')->get();
        foreach($status as $data){
         $data->created_at_time =  date('j M Y h:i A',strtotime($data->created_at));
         $data->updated_at_time =  date('j M Y h:i A',strtotime($data->updated_at));
        }
        return response()->json(['status' => $status]);
        //dd($id);
    }
}
