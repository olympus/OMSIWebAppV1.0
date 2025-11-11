<?php

namespace App\Http\Controllers;

use App\DataTables\ServiceRequestDataTable;
use App\Models\ArchiveServiceRequests;
use App\Models\ServiceRequests;
use Auth;
use DB;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function indexOld(ServiceRequestDataTable $dataTable)
    {
        $update_allowed = $this->isAllowed('administratorservice');
        // dd($update_allowed);
        return $dataTable->render('service.index',['export_path'=>'all', 'page_name'=>'All', 'update_allowed'=>$update_allowed]);
    }

    public function index(Request $request, $type = 'ALL')
    {
        $data['page_name'] = $type;
        return view('service.index',['export_path'=> $type, 'page_name'=> $type]);
    }

    public function serviceRequestList(Request $request)
    {
        if($request->page_name == 'ALL'){
            $page_name = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
        }else{
            if($request->page_name == 'sc'){
                $page_name =  ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'];
            }elseif($request->page_name == 'received'){
                $page_name =  ['Received'];
            }elseif($request->page_name == 'closed'){
                $page_name =  ['Closed'];
            }elseif($request->page_name == 'assigned'){
                $page_name =  ['Assigned','Re-assigned'];
            }elseif($request->page_name == 'attended'){
                $page_name =  ['Attended'];
            }elseif($request->page_name == 'rarp'){
                $page_name =  ['Received_At_Repair_Center'];
            }elseif($request->page_name == 'qp'){
                $page_name =  ['Quotation_Prepared'];
            }elseif($request->page_name == 'por'){
                $page_name =  ['PO_Received'];
            }elseif($request->page_name == 'rs'){
                $page_name =  ['Repair_Started'];
            }elseif($request->page_name == 'rc'){
                $page_name =  ['Repair_Completed'];
            }elseif($request->page_name == 'rtd'){
                $page_name =  ['Ready_To_Dispatch'];
            }elseif($request->page_name == 'dispatched'){
                $page_name =  ['Dispatched'];
            }
        }

        //dd($page_name);
        //echo $page_name;
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

                $totalArchiveData = ArchiveServiceRequests::where('request_type','service')->whereIn('status', $page_name)->
                    with('hospital','departmentData','employeeData','customer')
                    ->whereBetween('archive_service_requests.created_at', [$from_date, $to_date])
                    ->where('archive_service_requests.is_practice', false)
                    ->count();
                $totalCurrentData = ServiceRequests::where('request_type','service')->whereIn('status', $page_name)->
                    with('hospital','departmentData','employeeData','customer')
                    ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                    ->where('service_requests.is_practice', false)
                    ->count();

                $totalData = $totalArchiveData + $totalCurrentData;
                $totalFiltered = $totalData;
                $limit = $request->input('length');
                $start = $request->input('start');
                $dir = "desc";

                if(empty($request->input('search.value'))){  //filter for archive or current financial year. data without search
                    $archive_data = ArchiveServiceRequests::where('request_type','service')->whereIn('status', $page_name)->
                        with('hospital','departmentData','employeeData','customer')
                        ->where('archive_service_requests.is_practice', false)
                        ->whereBetween('archive_service_requests.created_at', [$from_date, $to_date])
                        ->orderBy('archive_service_requests.id',$dir)
                        ->get();
                    $current_data = ServiceRequests::where('request_type','service')->whereIn('status', $page_name)->
                        with('hospital','departmentData','employeeData','customer')
                        ->where('service_requests.is_practice', false)
                        ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                        ->orderBy('service_requests.id',$dir)
                        ->get();
                        $posts =   $archive_data->merge($current_data);
                        $posts = $posts->slice($start, $limit);
                    //$posts =   $archive_data;
                }else{ //filter for archive or current financial year. data with search
                    $search = $request->input('search.value');
                    $archive_data = ArchiveServiceRequests::where('request_type','service')->whereIn('status', $page_name)->
                        with('hospital','departmentData','employeeData','customer')
                        ->where('archive_service_requests.is_practice', false)
                        ->whereBetween('archive_service_requests.created_at', [$from_date, $to_date])
                        ->where('archive_service_requests.cvm_id', 'LIKE',"%{$search}%")
                        ->orWhere('archive_service_requests.remarks', 'LIKE',"%{$search}%")
                        ->orWhere('archive_service_requests.request_type', 'LIKE',"%{$search}%")
                        ->orWhere('archive_service_requests.employee_code', 'LIKE',"%{$search}%")
                        ->orWhere('archive_service_requests.sub_type', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.first_name', 'LIKE',"%{$search}%")
                        // ->orWhere('customers.last_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.hospital_name', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.state', 'LIKE',"%{$search}%")
                        // ->orWhere('hospitals.city', 'LIKE',"%{$search}%")
                        ->orderBy('archive_service_requests.id',$dir)
                        ->get();
                    $current_data = ServiceRequests::where('request_type','service')->whereIn('status', $page_name)->
                        with('hospital','departmentData','employeeData','customer')
                        ->where('service_requests.is_practice', false)
                        ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                        ->where('service_requests.cvm_id', 'LIKE',"%{$search}%")
                        ->orWhere('service_requests.remarks', 'LIKE',"%{$search}%")
                        ->orWhere('service_requests.request_type', 'LIKE',"%{$search}%")
                        ->orWhere('service_requests.employee_code', 'LIKE',"%{$search}%")
                        ->orWhere('service_requests.sub_type', 'LIKE',"%{$search}%")
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

                $totalData = ServiceRequests::where('request_type','service')->whereIn('status', $page_name)->
                    with('hospital','departmentData','employeeData','customer')
                    ->where('service_requests.is_practice', false)
                    ->whereBetween('service_requests.created_at', [$from_date, $to_date])
                    ->count();
                $totalFiltered = $totalData;
                $limit = $request->input('length');
                $start = $request->input('start');
                $dir = "desc";

                if(empty($request->input('search.value'))){
                    //filter current financial year or open tickets of old year data without search
                    $posts = ServiceRequests::where('request_type','service')->whereIn('status', $page_name)->offset($start)
                        ->limit($limit)
                        ->with('hospital','departmentData','employeeData','customer')
                        ->where('service_requests.is_practice', false)
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
                        ->where('service_requests.is_practice', false)
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

                $totalData = ServiceRequests::where('request_type','service')->whereIn('status', $page_name)->
                    with('hospital','departmentData','employeeData','customer')
                    ->where('service_requests.is_practice', false)
                    ->count();
                $totalFiltered = $totalData;
                $limit = $request->input('length');
                $start = $request->input('start');
                $dir = "desc";

                if(empty($request->input('search.value'))){
                    //filter current financial year or open tickets of old year data without search
                    $posts = ServiceRequests::where('request_type','service')->whereIn('status', $page_name)->offset($start)
                        ->limit($limit)
                        ->with('hospital','departmentData','employeeData','customer')
                        ->where('service_requests.is_practice', false)
                        ->orderBy('id',$dir)
                        ->get();
                }else{
                    //filter current financial year or open tickets of old year data with search functionality
                    $search = $request->input('search.value');
                    $posts =  ServiceRequests::offset($start)
                        ->limit($limit)
                        ->with('hospital','departmentData','employeeData','customer')
                        ->where('service_requests.is_practice', false)
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

                    if(Auth::user()->isA('superadministrator|administrator|administratorservicec|administratorservice')){
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
                            <a style=''
                            class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure? \nAll related data (feedback, status timelines) will also be deleted.`)' href='requests/deletes/{$current_user->id}'>
                                <i class='fa fa-trash'></i>
                            </a>
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

    public function isAllowed($allowed)
    {
        if(Auth::user()->isA($allowed) ){
            session(['update_allowed' => true]);
            return true;
        }
        session(['update_allowed' => false]);
        return false;
    }
    public function indexByType(ServiceRequestDataTable $dataTable, $type)
    {
        $validator = Validator::make(
          [
            'type' => $type,
          ],[
            'type' => 'required|string|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        switch ($type){
            case 'sc' :
                $update_allowed = $this->isAllowed('administratorservicec');
                return $dataTable->render('service.index',['export_path'=>'sc', 'page_name'=>'Service Center All']);
                break;
            case 'received' :
                $update_allowed = $this->isAllowed('administratorservice');
                return $dataTable->render('service.index',['export_path'=>'received', 'page_name'=>'Received', 'update_allowed'=>$update_allowed]);
                break;
            case 'attended' :
                $update_allowed = $this->isAllowed('administratorservice');
                return $dataTable->render('service.index',['export_path'=>'attended', 'page_name'=>'Attended', 'update_allowed'=>$update_allowed]);
                break;
            case 'assigned' :
                $update_allowed = $this->isAllowed('administratorservice|administratorservicec|service2servicec');
                return $dataTable->render('service.index',['export_path'=>'assigned', 'page_name'=>'Assigned', 'update_allowed'=>$update_allowed]);
                break;
            case 'rarp' :
                $update_allowed = $this->isAllowed('administratorservice|administratorservicec');
                return $dataTable->render('service.index',['export_path'=>'rarp', 'page_name'=>'Received At Repair Center', 'update_allowed'=>$update_allowed]);
                break;
            case 'qp' :
                $update_allowed = $this->isAllowed('administratorservice|administratorservicec');
                return $dataTable->render('service.index',['export_path'=>'qp', 'page_name'=>'Quotation Prepared', 'update_allowed'=>$update_allowed]);
                break;
            case 'por' :
                $update_allowed = $this->isAllowed('administratorservice|administratorservicec');
                return $dataTable->render('service.index',['export_path'=>'por', 'page_name'=>'PO Received', 'update_allowed'=>$update_allowed]);
                break;
            case 'rs' :
                $update_allowed = $this->isAllowed('administratorservice|administratorservicec');
                return $dataTable->render('service.index',['export_path'=>'rs', 'page_name'=>'Repair Started', 'update_allowed'=>$update_allowed]);
                break;
            case 'rc' :
                $update_allowed = $this->isAllowed('administratorservice|administratorservicec');
                return $dataTable->render('service.index',['export_path'=>'rc', 'page_name'=>'Repair Completed', 'update_allowed'=>$update_allowed]);
                break;
            case 'rtd' :
                $update_allowed = $this->isAllowed('administratorservice|administratorservicec');
                return $dataTable->render('service.index',['export_path'=>'rtd', 'page_name'=>'Ready To Dispatch', 'update_allowed'=>$update_allowed]);
                break;
            case 'dispatched' :
                $update_allowed = $this->isAllowed('administratorservice|administratorservicec');
                return $dataTable->render('service.index',['export_path'=>'dispatched', 'page_name'=>'Dispatched', 'update_allowed'=>$update_allowed]);
                break;
            case 'closed' :
                $update_allowed = $this->isAllowed('administratorservice');
                return $dataTable->render('service.index',['export_path'=>'closed', 'page_name'=>'Closed', 'update_allowed'=>$update_allowed]);
                break;
            default :
                return abort(404);

        }
    }

	public function export($type)
    {
        $validator = Validator::make(
          [
            'type' => $type,
          ],[
            'type' => 'required|string|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $file_name = '';
        $wherein = '';
        switch ($type){
            case 'all' :
                $wherein = ['Received','Assigned','Attended','Re-assigned','Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched','Closed'];
                $file_name = 'All';
                break;
            case 'received' :
                $wherein = ['Received'];
                $file_name = 'Received';
                break;
            case 'assigned' :
                $wherein = ['Assigned','Re-assigned'];
                $file_name = 'Assigned';
                break;
            case 'attended' :
                $wherein = ['Attended'];
                $file_name = 'Attended';
                break;
            case 'rarp' :
                $wherein = ['Received_At_Repair_Center'];
                $file_name = 'Received_At_Repair_Center';
                break;
            case 'qp' :
                $wherein = ['Quotation_Prepared'];
                $file_name = 'Quotation_Prepared';
                break;
            case 'por' :
                $wherein = ['PO_Received'];
                $file_name = 'PO_Received';
                break;
            case 'rs' :
                $wherein = ['Repair_Started'];
                $file_name = 'Repair_Started';
                break;
            case 'rc' :
                $wherein = ['Repair_Completed'];
                $file_name = 'Repair_Completed';
                break;
            case 'rtd' :
                $wherein = ['Ready_To_Dispatch'];
                $file_name = 'Ready_To_Dispatch';
                break;
            case 'dispatched' :
                $wherein = ['Dispatched'];
                $file_name = 'Dispatched';
                break;
            case 'sc' :
                $wherein = ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'];
                $file_name = 'ServiceCenter_All';
                break;
            case 'closed' :
                $wherein = ['Closed'];
                $file_name = 'Closed';
                break;

        }
        exportExcel('Service Requests '.$file_name,'service',$wherein);
    }
}
