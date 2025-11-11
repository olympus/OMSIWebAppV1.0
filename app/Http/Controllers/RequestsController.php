<?php

namespace App\Http\Controllers;

use App\DataTables\RequestsDataTable;
use App\DownloadExcelMail;
use App\Events\RequestStatusUpdated;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
use App\Models\Feedback;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\NotifyCustomer;
use App\Models\ProductInfo;
use App\SFDC;
use App\StatusTimeline;
use Auth;
use DB;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Validator;

class RequestsController extends Controller
{
    public function index(RequestsDataTable $dataTable)
    {
        return $dataTable->render('requests.index');
    }

    public function archiveDataFilter(Request $request)
    {
        $data['mail'] = DownloadExcelMail::where('status', 1)->get();
        return view('requests.archive_data_filter', $data);
    }

    public function index1()
    {
        return view('requests.index');
        //return $dataTable->render('feedback.index');
    }

    public function requestList(Request $request)
    {
        if($request->from_date && $request->to_date && $request->data_type == 'archive_data'){
            $from_date = $request->from_date.' 00:00:00';
            $to_date = $request->to_date.' 23:59:59';
            //in this step data is filter for archive or current financial year.
            try {
                $columns = array(
                    0 => 'cvm_id',
                    1 => 'first_name',
                    2 => 'last_name',
                    3 => 'hospital_name',
                    4 => 'name',
                    5 => 'city',
                    6 => 'state',
                    7 => 'employee_name',
                    8 => 'request_type',
                    9 => 'sub_type',
                    10 => 'status',
                    11 => 'remarks',
                    12 => 'last_updated_by',
                    13 => 'created_at',
                    14 => 'updated_at'
                );

                $totalArchiveData = ArchiveServiceRequests::
                    with('hospital','departmentData','employeeData','customer')
                    ->whereBetween('archive_service_requests.created_at', [$from_date, $to_date])
                    //->where('archive_service_requests.is_practice', false)
                    ->count();
                $totalCurrentData = ServiceRequests::
                    with('hospital','departmentData','employeeData','customer')
                    ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                    //->where('service_requests.is_practice', false)
                    ->count();

                $totalData = $totalArchiveData + $totalCurrentData;
                $totalFiltered = $totalData;
                $limit = $request->input('length');
                $start = $request->input('start');
                $dir = "desc";

                if(empty($request->input('search.value'))){  //filter for archive or current financial year. data without search
                    $archive_data = ArchiveServiceRequests::
                        with('hospital','departmentData','employeeData','customer')
                        //->where('archive_service_requests.is_practice', false)
                        ->whereBetween('archive_service_requests.created_at', [$from_date, $to_date])
                        ->orderBy('archive_service_requests.id',$dir)
                        ->get();
                    $current_data = ServiceRequests::
                        with('hospital','departmentData','employeeData','customer')
                        //->where('service_requests.is_practice', false)
                        ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                        ->orderBy('service_requests.id',$dir)
                        ->get();
                        $posts =   $archive_data->merge($current_data);
                        $posts = $posts->slice($start, $limit);
                    //$posts =   $archive_data;
                }else{ //filter for archive or current financial year. data with search
                    $search = $request->input('search.value');
                    $archive_data = ArchiveServiceRequests::
                        with('hospital','departmentData','employeeData','customer')
                        //->where('archive_service_requests.is_practice', false)
                        ->whereBetween('archive_service_requests.created_at', [$from_date, $to_date])
                        ->where('archive_service_requests.cvm_id', 'LIKE',"%{$search}%")
                        // ->orWhere('archive_service_requests.remarks', 'LIKE',"%{$search}%")
                        // ->orWhere('archive_service_requests.request_type', 'LIKE',"%{$search}%")
                        // ->orWhere('archive_service_requests.employee_code', 'LIKE',"%{$search}%")
                        // ->orWhere('archive_service_requests.sub_type', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.first_name', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.last_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.hospital_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.state', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.city', 'LIKE',"%{$search}%")
                        ->orderBy('archive_service_requests.id',$dir)
                        ->get();
                    $current_data = ServiceRequests::
                        with('hospital','departmentData','employeeData','customer')
                        //->where('service_requests.is_practice', false)
                        ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                        ->where('service_requests.cvm_id', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.remarks', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.request_type', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.employee_code', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.sub_type', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.first_name', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.last_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.hospital_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.state', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.city', 'LIKE',"%{$search}%")
                        ->orderBy('service_requests.id',$dir)
                        ->get();
                    $posts =   $archive_data->merge($current_data);
                    $posts = $posts->slice($start, $limit);
                    $totalFiltered = count($posts);
                    //$posts =   $archive_data;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }elseif($request->from_date && $request->to_date && $request->data_type == 'current_data'){
            $from_date = $request->from_date.' 00:00:00';
            $to_date = $request->to_date.' 23:59:59';
            //in this step data is filter for current financial year or open tickets of old year data
            try {
                $columns = array(
                    0 => 'cvm_id',
                    1 => 'first_name',
                    2 => 'last_name',
                    3 => 'hospital_name',
                    4 => 'name',
                    5 => 'city',
                    6 => 'state',
                    7 => 'employee_name',
                    8 => 'request_type',
                    9 => 'sub_type',
                    10 => 'status',
                    11 => 'remarks',
                    12 => 'last_updated_by',
                    13 => 'created_at',
                    14 => 'updated_at'
                );

                $totalData = ServiceRequests::
                    with('hospital','departmentData','employeeData','customer')
                    //->where('service_requests.is_practice', false)
                    ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                    ->count();
                $totalFiltered = $totalData;
                $limit = $request->input('length');
                $start = $request->input('start');
                $dir = "desc";

                if(empty($request->input('search.value'))){
                    //filter current financial year or open tickets of old year data without search
                    $posts = ServiceRequests::offset($start)
                        ->limit($limit)
                        ->with('hospital','departmentData','employeeData','customer')
                        //->where('service_requests.is_practice', false)
                        ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                        ->orderBy('id',$dir)
                        ->get();
                        //dd($posts);
                }else{
                    //filter current financial year or open tickets of old year data with search functionality
                    $search = $request->input('search.value');
                    $posts =  ServiceRequests::offset($start)
                        ->limit($limit)
                        ->with('hospital','departmentData','employeeData','customer')
                        //->where('service_requests.is_practice', false)
                        ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                        ->where('service_requests.cvm_id', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.remarks', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.request_type', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.employee_code', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.sub_type', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.first_name', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.last_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.hospital_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.state', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.city', 'LIKE',"%{$search}%")
                        ->orderBy('id',$dir)
                        ->get();
                    $totalFiltered = count($posts);
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }else{
            $from_date = $request->from_date.' 00:00:00';
            $to_date = $request->to_date.' 23:59:59';
            //in this step data is filter for current financial year or open tickets of old year data
            try {
                $columns = array(
                    0 => 'cvm_id',
                    1 => 'first_name',
                    2 => 'last_name',
                    3 => 'hospital_name',
                    4 => 'name',
                    5 => 'city',
                    6 => 'state',
                    7 => 'employee_name',
                    8 => 'request_type',
                    9 => 'sub_type',
                    10 => 'status',
                    11 => 'remarks',
                    12 => 'last_updated_by',
                    13 => 'created_at',
                    14 => 'updated_at'
                );

                $totalData = ServiceRequests::
                    with('hospital','departmentData','employeeData','customer')
                    //->where('service_requests.is_practice', false)
                    ->count();
                $totalFiltered = $totalData;
                $limit = $request->input('length');
                $start = $request->input('start');
                $dir = "desc";

                if(empty($request->input('search.value'))){
                    //filter current financial year or open tickets of old year data without search
                    $posts = ServiceRequests::offset($start)
                        ->limit($limit)
                        ->with('hospital','departmentData','employeeData','customer')
                        //->where('service_requests.is_practice', false)
                        ->orderBy('id',$dir)
                        ->get();
                }else{
                    //filter current financial year or open tickets of old year data with search functionality
                    $search = $request->input('search.value');
                    $posts =  ServiceRequests::offset($start)
                        ->limit($limit)
                        ->with('hospital','departmentData','employeeData','customer')
                        //->where('service_requests.is_practice', false)
                        ->where('service_requests.cvm_id', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.remarks', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.request_type', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.employee_code', 'LIKE',"%{$search}%")
                        // ->orWhere('service_requests.sub_type', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.first_name', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.last_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.hospital_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.state', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.city', 'LIKE',"%{$search}%")
                        ->orderBy('id',$dir)
                        ->get();
                    $totalFiltered = count($posts);
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
        try{ //in this step data is pass for a datatable
            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $current_user) {
                    //dd($current_user->employeeData->name);
                    $show =  url('/admin/requests',$current_user->id);
                    $edit =  url('/admin/requests/'.$current_user->id.'/edit');
                    $nestedData['cvm_id'] = $current_user->cvm_id ?? '';
                    $nestedData['first_name'] = $current_user->customer->first_name ?? '';
                    $nestedData['last_name'] = $current_user->customer->last_name ?? '';
                    $nestedData['hospital_name'] = $current_user->hospital->hospital_name ?? '';
                    $nestedData['name'] = $current_user->departmentData->name ?? '';
                    $nestedData['city'] = $current_user->hospital->city ?? '';
                    $nestedData['state'] = $current_user->hospital->state ?? '';
                    $nestedData['employee_name'] = $current_user->employeeData->name ?? '';
                    $nestedData['request_type'] = $current_user->request_type ?? '';
                    $nestedData['sub_type'] = $current_user->sub_type ?? '';
                    $nestedData['status'] = $current_user->status ?? '';
                    $nestedData['remarks'] = $current_user->remarks ?? '';
                    $nestedData['last_updated_by'] = $current_user->last_updated_by ?? '';
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($current_user->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($current_user->updated_at));
                    if(Auth::user()->isA('superadministrator|administrator')){
                        $nestedData['options'] = "
                            <a style='margin-left: 12px;' class='btn btn-xs btn-success' href='{$show}'  title='Show'><i class='fa fa-eye'></i></a>

                            <a style='' class='btn btn-xs btn-info' href='{$edit}'  title='Edit'><i class='fa fa-edit'></i></a>
                            <a style=''
                            class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure? \nAll related data (feedback, status timelines) will also be deleted.`)' href='requests/deletes/{$current_user->id}'>
                                <i class='fa fa-trash'></i>
                            </a>
                            <a style='margin-top: 12px;' class='btn btn-xs btn-success editProduct' title='Edit' data-id='$current_user->id' data-toggle='modal' data-target='#editModal' data-original-title='Edit'>View Status History</a>
                        ";
                    }else{
                        $nestedData['options'] = "
                            <a style='margin-left: 12px;' class='btn btn-xs btn-success' href='{$show}'  title='Show'><i class='fa fa-eye'></i></a>
                            <a style='margin-top: 12px;' class='btn btn-xs btn-success editProduct' title='Edit' data-id='$current_user->id' data-toggle='modal' data-target='#editModal' data-original-title='Edit'>View Status History</a>
                        ";
                    }

                    $data[] = $nestedData;
                }
            }
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );
            echo json_encode($json_data);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|integer',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }

        $service_request = ServiceRequests::where('id', $id)->first();
        if($service_request){
            $request = ServiceRequests::where('id', $id)->first();
        }

        $archive_service_request = ArchiveServiceRequests::where('id', $id)->first();
        if($archive_service_request){
            $request = ArchiveServiceRequests::where('id', $id)->first();
        }

        //$request =  ServiceRequests::findOrFail($id);
        $request_type =  $request->request_type;
        // if(in_array($request->status,\Config('servicec_statuses'))){dd('allowed: yes');}

        // switch ($request_type) {
        //     case 'service':
        //         $allowed = Auth::user()->isA('superadministrator|administrator|administratorservice|administratorservicec|service2servicec');
        //         break;
        //     case 'servicec':
        //         $allowed = Auth::user()->isA('superadministrator|administrator|administratorservice|administratorservicec|service2servicec');
        //         break;
        //     case 'academic':
        //         $allowed = Auth::user()->isA('superadministrator|administrator|administratoracademic');
        //         break;
        //     case 'enquiry':
        //         $allowed = Auth::user()->isA('superadministrator|administrator|administratorenquiry');
        //         break;
        //     case 'others':
        //         $allowed = Auth::user()->isA('superadministrator|administrator|administratorothers');
        //         break;
        //     default:
        //         return abort(404);
        // }

        // if($allowed){
            return view('requests.edit', ['servicerequest'=>$request,'request_type'=>$request_type]);
        // }else{
        //     return "Unauthorized Request. You are not allowed to access ".ucfirst($request_type)." requests";
        // }
    }

    public function ajaxsubmit(Request $request)
    {
        $validation = Validator::make($request->all(), [
            // 'select_file' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:8192'
            'select_file' => 'required|mimes:pdf|max:8192'
        ]);
        if ($validation->passes()) {
            $file = $request->file('select_file');
            $fileOriginalName = $file->getClientOriginalName();
            $new_name = rand() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/technical_reports'), $new_name);
            return response()->json([
                'message'   => 'File Upload Successfully',
                'uploaded_image' => env('APP_URL')."/storage/technical_reports/".$new_name,
                'file_name' => $fileOriginalName,
                'class_name'  => 'alert-success'
            ]);
        } else {
            return response()->json([
                'message'   => $validation->errors()->all(),
                'uploaded_image' => '',
                'class_name'  => 'alert-danger'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {

        $this->validate(request(), [
            'sub_type' => 'string',
            'request_type' => 'required|string',
            'previous_url' => 'string',
            'departments' => 'string',
            'to_emails' => 'string',
            'cc_emails' => 'string',
            'customer_id' => 'required|string',
            'hospital_id' => 'required|string',
            'dept_id' => 'required|string',
            'status' => 'required|string',
            'sap_id' => 'string',
            'sfdc_id' => 'string',
            //'sfdc_customer_id' => 'string',
            'employee_code' => 'string',
        ]);
        $servicerequest = ServiceRequests::findOrFail($id);
        $oldData = $servicerequest;
        $customer = Customers::findOrFail($servicerequest->customer_id);

        // if(!is_null($request->body)){
        //     $trep_data = json_decode(stripslashes($request->body),true)[0];
        //     $technical_report = new TechnicalReport;
        //     $technical_report->service_requests_id = $id;
        //     $technical_report->trep_filename = $trep_data['file_name'];
        //     $technical_report->trep_filetype = $trep_data['type'];
        //     $technical_report->trep_file = $trep_data['value'];
        //     NotifyCustomer::send_notification('request_technical_report', $servicerequest, $customer);
        //     $technical_report->save();
        // }
        if ($oldData->request_type != $request->request_type) {
            if(env("SFDC_ENABLED") && $request->request_type == 'service' && !$oldData->sfdc_id){
                $hospitals = Hospitals::find($servicerequest->hospital_id);
                $customer = Customers::findOrFail($servicerequest->customer_id);

                $SFDCCreateRequest = SFDC::createRequest($servicerequest, $customer, $hospitals, "");
                if(isset($SFDCCreateRequest->success)){
                    if($SFDCCreateRequest->success == "true" && isset($SFDCCreateRequest->id)){
                        $servicerequest->sfdc_id = $SFDCCreateRequest->id;
                        $servicerequest->save();
                    }
                    else{
                        Log::info("\n===Error SFDCCreateRequest request_type_change"."\n\n");
                        Log::info($SFDCCreateRequest);
                    }
                }else{
                    Log::info("\n===Error SFDCCreateRequest request_type_changed"."\n\n");
                    Log::info($SFDCCreateRequest);
                }
            }
            $servicerequest->request_type = $request->request_type;
            $servicerequest->sub_type = $request->sub_type;
            $servicerequest->save();
            //send_sms('request_type_changed', $customer, $servicerequest, $oldData->request_type);
            NotifyCustomer::send_notification('request_type_changed', $servicerequest, $customer);
            return redirect($request->previous_url)->with('message', 'Request Type successfully changed.');
        }
        $servicerequest->customer_id = $request->customer_id;
        $servicerequest->hospital_id = $request->hospital_id;
        $servicerequest->dept_id = $request->dept_id;
        $servicerequest->status = $request->status;
        $servicerequest->sap_id = $request->sap_id;
        $servicerequest->sfdc_id = $request->sfdc_id;
        $servicerequest->sfdc_customer_id = $request->sfdc_customer_id;
        $servicerequest->employee_code = $request->employee_code;
        $servicerequest->last_updated_by = Auth::user()->name;
        $servicerequest->save();

        $status = new StatusTimeline;
        $status->status =$servicerequest->status;
        $status->customer_id = $servicerequest->customer_id;
        $status->request_id = $servicerequest->id;
        $status ->save();

        //send_sms('status_update', $customer, $servicerequest, '');
        NotifyCustomer::send_notification('request_update', $servicerequest, $customer);
        event(new RequestStatusUpdated($servicerequest, $customer, $oldData));

        return redirect($request->previous_url)->with('message', 'Request data successfully updated');
    }

    public function show($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|integer',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }

        $service_request = ServiceRequests::where('id', $id)->first();
        if($service_request){
            $servicerequest = ServiceRequests::where('id', $id)->first();
        }

        $archive_service_request = ArchiveServiceRequests::where('id', $id)->first();
        if($archive_service_request){
            $servicerequest = ArchiveServiceRequests::where('id', $id)->first();
        }

        //$servicerequest = ServiceRequests::findOrFail($id);
        $productinfo = ProductInfo::where('service_requests_id',$id)->first();
        return view('requests.show', ['servicerequest'=>$servicerequest,'productinfo'=>$productinfo]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
         $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|integer',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        ServiceRequests::findOrFail($id)->delete();
        Feedback::where('request_id',$id)->delete();
        StatusTimeline::where('request_id',$id)->delete();
        return redirect('/admin/requests')->with('message', "Request data for $id successfully deleted");
    }

    public function deletes($id)
    {
         $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|integer',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        ServiceRequests::findOrFail($id)->delete();
        Feedback::where('request_id',$id)->delete();
        StatusTimeline::where('request_id',$id)->delete();
        return redirect('/admin/requests')->with('message', "Request data for $id successfully deleted");
    }

    public function export()
    {
        $wherein = ['Received','Assigned','Re-assigned','Attended','Closed'];
        $file_name = 'All';
        exportExcel('All Requests ',['academic', 'enquiry', 'service'],$wherein);
    }

    // public function export()
    // {
    //     $export_query = ServiceRequests::
    //         // join('status_timelines', 'status_timelines.request_id', '=', 'service_requests.id')
    //         // ->join('hospitals', 'hospitals.id', '=', 'service_requests.hospital_id')
    //         // ->join('departments', 'departments.id', '=', 'service_requests.dept_id')
    //         where('id','>=','11950')
    //         ->where('status', '!=', 'Received')
    //         ->select('id', 'request_type', 'created_at')
    //         // ->where('request_type','>=','academic')
    //         // ->select('*', 'customers.first_name', 'customers.last_name', 'customers.email', 'hospitals.hospital_name', 'hospitals.state', 'departments.name')
    //         ->orderBy('created_at', 'ASC')
    //         ->get();
    //     // foreach ($export_query as $request) {
    //     //     $assigned_time = \App\StatusTimeline::where('request_id', $request->id)
    //     //         ->where('status','Assigned')->get();
    //     //     if($assigned_time[0]){
    //     //         $request->assigned_time = $assigned_time[0]->created_at;
    //     //     }else{
    //     //         $request->assigned_time = '-';
    //     //     }
    //     // }
    //     $export_query = $export_query->toArray();
    //     // $export_query = $export_query->where('region', 'south')->toArray();

    //     Excel::create('All Requests', function ($excel) use ($export_query) {
    //         $excel->sheet('Sheet1', function ($sheet) use ($export_query) {
    //             if (count($export_query) != 0) {
    //                 $sheet->fromArray($export_query);

    //                 foreach ($export_query as $key => $value) {
    //                     if ($key % 2 == 0) {
    //                         $sheet->row($key+2, function ($row) {
    //                             $row->setBackground('#b8cce4');
    //                         });
    //                     } else {
    //                         $sheet->row($key+2, function ($row) {
    //                             $row->setBackground('#dbe5f1');
    //                         });
    //                     }
    //                 }
    //                 $sheet->row(1, function ($row) {
    //                     $row->setBackground('#4f81bd');
    //                 });
    //                 $sheet->setAutoSize(true);
    //                 $sheet->setWidth('H', 30);
    //                 $sheet->getStyle('A1:AM1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
    //             } else {
    //                 $sheet->setCellValue('A1', 'No requests to display');
    //                 $sheet->row(1, function ($row) {
    //                     $row->setBackground('#4f81bd');
    //                 });
    //                 $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
    //             }
    //         });
    //     })->export('xls');
    //     return redirect('/admin/requests');
    // }
}
