<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Mail\RequestUpdated;
use App\Models\AutoEmails;
use App\Models\Customers;
use App\Models\DirectRequest;
use App\Models\EmployeeTeam;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\NotifyCustomer;
use App\Models\ProductInfo;
use App\StatusTimeline;
use Auth;
use DB;
use Illuminate\Http\Request;
use Mail;
use Session;

class SapController extends Controller
{
	public function verify(Request $request)
	{
		$this->validate($request, [
            'attachment' => 'required',
        ]);

		$attachment = $request->file('attachment');
		$excelfile = 'SAPImport_'.time().".".$attachment->getClientOriginalExtension();
		$attachment->move(storage_path('exports'), $excelfile);
		$filepath = storage_path('exports/').$excelfile;
		$csv_data = $this->csv2array($filepath);
		$table_data = [];

		foreach ($csv_data as $data) {
			// dd($data);
			$request_ids = $data["cvm_req_complete"];
			$request_id = $data["cvm_req_no"];
			$new_status = $this->validate_status(strtolower($data["status"]));

			if (strpos(strtolower($request_id), 'direct') !== false || strpos(strtolower($request_id), 'n/a') !== false) {
				$is_direct_new = DirectRequest::where('sap_id',$data["sap_id"])->first();
				if(is_null($is_direct_new)){
					$action_type = "Direct New";
				}else{
					$action_type = "Direct Existing";
				}
			} else {
				// $existing_or_new = ServiceRequests::where("id", $request_id)->first();
				$existingSAP = ServiceRequests::where("sap_id", $data["sap_id"])->first();
				if (!is_null($existingSAP)) {
					if(!is_null(ServiceRequests::where("id", $request_id)->first())){
						$action_type = "SAP Existing";
					}else{
						$action_type = "CVM Deleted";
					}
				} else {
					$existingCVM = ServiceRequests::where("id", $request_id)->first();
					if (!is_null($existingCVM)) {
						$action_type = "CVM Existing";
					} else {
						$action_type = "CVM Deleted";
					}
				}
				// Find Split Request
				if( strpos($data["cvm_req_no"], '.') !== false ){
					$existingCVM = ServiceRequests::where("id", explode('.',$request_id)[0])->first();
					$action_type = "CVM Split Request";
					// dd("Split Request",$data,$existingCVM);
				}

			}
			switch ($action_type) {
				case 'Direct New':
					// $this->insertDirectRequest($data);
					$temp_row = [
						"cvm_req_no"=>$request_id,
						"previous_status"=>"",
						"action"=>"Direct New",
						"checked"=>1,
						"trigger"=>'direct_new'
					];
					array_push($table_data, $this->makeRowDataArr($data,$temp_row,$request_ids,$request_id,$new_status));
					break;

				case 'Direct Existing':
					$checkStatus = $this->checkStatus($is_direct_new->status,$new_status);
					$temp_row = [
						"cvm_req_no"=>$is_direct_new->id,
						"previous_status"=>$is_direct_new->status,
						"action"=>"Direct Existing",
						"checked"=> (is_null($is_direct_new->status))? 1 : 0 ,
						"trigger"=>'direct_update'
					];
					array_push($table_data, $this->makeRowDataArr($data,$temp_row,$request_ids,$request_id,$new_status));
					break;

				case 'SAP Existing':
					$this->checkProductExists('sap',$data);
					$checkStatus = $this->checkStatus($existingSAP->status,$new_status);
					$temp_row = [
						"cvm_req_no"=>$request_id,
						"previous_status"=>$existingSAP->status,
						"action"=>$checkStatus["table_action"],
						"checked"=>$checkStatus["checked"],
						"trigger"=>'cvm_update_sapid'
					];
					// if($data['status']=="ASSIGNED" && $existingSAP['request_type'] == "service"){
					// 	$new_status = "Attended";
					// }
					array_push($table_data, $this->makeRowDataArr($data,$temp_row,$request_ids,$request_id,$new_status));
					break;

				case 'CVM Deleted':
					$temp_row = [
						"cvm_req_no"=>$request_id,
						"previous_status"=>null,
						"action"=>$action_type,
						"checked"=>0,
						"trigger"=>'cvm_ignore'
					];
					array_push($table_data, $this->makeRowDataArr($data,$temp_row,$request_ids,$request_id,$new_status));
					break;

				case 'CVM Existing':
					$this->checkProductExists('cvm',$data);
					$checkStatus = $this->checkStatus($existingCVM->status,$new_status);
					$temp_row = [
						"cvm_req_no"=>$request_id,
						"previous_status"=>$existingCVM->status,
						"action"=>$checkStatus["table_action"],
						"checked"=>$checkStatus["checked"],
						"trigger"=>'cvm_update_cvmid'
					];
					// if($data['status']=="ASSIGNED" && $existingSAP['request_type'] == "service"){
					// 	$new_status = "Attended";
					// }
					array_push($table_data, $this->makeRowDataArr($data,$temp_row,$request_ids,$request_id,$new_status));
					break;

				case 'CVM Split Request':
					$checkStatus = $this->checkStatus($existingCVM->status,$new_status);
					$temp_row = [
						"cvm_req_no"=>$request_id,
						"previous_status"=>$existingCVM->status,
						"action"=>'CVM Split',
						"checked"=>1,
						"trigger"=>'cvm_split_request'
					];
					// if($data['status']=="ASSIGNED" && $existingSAP['request_type'] == "service"){
					// 	$new_status = "Attended";
					// }
					array_push($table_data, $this->makeRowDataArr($data,$temp_row,$request_ids,$request_id,$new_status));
					break;
			}
		}
		// dd($table_data);
		return view('sap.verify')->with('table_data',$table_data);
	}

	public function checkProductExists($type,$data){
		if($type = 'sap'){
			$servicerequest = ServiceRequests::where('sap_id',$data['sap_id'])->value('id');
		}else{
			$servicerequest = $data["cvm_req_no"];
		}
		if(is_null($servicerequest)){
			$servicerequest = $data["cvm_req_no"];
		}
		$product_data = [
			"service_requests_id"=>$servicerequest,
			"pd_name"=>$data["prod_model_no"],
			"pd_serial"=>$data["prod_serial_no"],
			"pd_description"=>$data["prod_material_description"]
		];
		$product = ProductInfo::where('service_requests_id',$servicerequest)->first();
		if(is_null($product)){
			$product = ProductInfo::create($product_data);
		}

	}

	public function checkStatus($existing_or_new_status,$new_status){
		$allowed_statuses = ['Received', 'Assigned', 'Attended', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched', 'Closed'];
		$previous_status = $existing_or_new_status;
		if(in_array($new_status, $allowed_statuses)){
			if ($new_status != $previous_status) {
				$validate_status_increment = $this->validate_status_increment($previous_status,$new_status);
				if($validate_status_increment){
					$table_action = "Status Update";
					$checked = 1;
				}else{
					$table_action = "Status Rollback";
					$checked = 0;
				}
			} else {
				$table_action = "Status Unchanged";
				$checked = 0;
			}
		}else{
			$table_action = "Unknown Status";
			$checked = 0;
		}
		return ["table_action"=>$table_action, "checked"=>$checked];
	}

	public function makeRowDataArr($data,$temp_row,$requests,$request_id,$new_status)
	{
		$filter_data_type = (strpos(strtolower($temp_row['action']), 'direct')!== false) ? 'direct' : 'cvm' ;
		// if ($filter_data_type == "direct") {
		// 	$
		// }
		// elseif($filter_data_type == "cvm")
		$previous_status = ($temp_row["previous_status"] == "BROUGHT TO SC") ? "Received_At_Repair_Center" : $temp_row["previous_status"];
		$row_data = [
			'type'=>$filter_data_type,
			'orig_cvm_col'=>$requests,
			'cvm_req_no'=>$temp_row["cvm_req_no"],
			'request_id'=>$request_id,
			'previous_status'=>$previous_status,
			'status'=>$new_status,
			'action'=>$temp_row["action"],
			'checked'=>$temp_row["checked"],
			'trigger'=>$temp_row["trigger"],

			"fse_code"=>$data["fse_code"],
			"customer_name"=>$data["customer_name"],
			"sap_id"=>$data["sap_id"],
			"customer_code"=>$data["customer_code"],
			"customer_city"=>$data["customer_city"],
			"customer_state"=>$data["customer_state"],
			"prod_model_no"=>$data["prod_model_no"],
			"prod_material"=>$data["prod_material"],
			"prod_serial_no"=>$data["prod_serial_no"],
			"prod_equipment_no"=>$data["prod_equipment_no"],
			"prod_material_description"=>$data["prod_material_description"]
		];
		return $row_data;
	}

	public function validate_status($status){
		$raw_keys = [
			'Received' => ['received'],
			'Assigned' => ['assigned','troubleshoot','others','onsite repaired'],
			'Attended' => ['attended','attended'],
			'Received_At_Repair_Center' => ['received_at_repair_center','received at repair center','brought to sc'],
			'Quotation_Prepared' => ['quotation_prepared','quotation prepared'],
			'PO_Received' => ['po_received','po received'],
			'Repair_Started' => ['repair_started','repair started'],
			'Repair_Completed' => ['repair_completed','repair completed'],
			'Ready_To_Dispatch' => ['ready_to_dispatch','ready to dispatch'],
			'Dispatched' => ['dispatched','dispatched'],
			'Closed' => ['closed','closed']
		];
		foreach ($raw_keys as $raw_key => $value) {
			if(in_array($status, $value)){
				return $raw_key;
			}
		}
		return $status;
	}

	public function validate_status_increment($previous_status,$new_status){
		$status_keys = \Config('oly.requests_statuses');
		foreach ($status_keys as $status_key) {
			$values = array_keys($status_key);
			if(in_array($previous_status, $values) && in_array($new_status, $values)){
				$previous_index = array_search($previous_status, $values);
				$new_index = array_search($new_status, $values);
				if($previous_index > $new_index){
					return false;
				}
			}
		}
		return true;
	}


	public function index()
	{
		return view('sap.index');
	}

	public function imported()
	{
		return view('sap.final');
	}

	public function show_direct($id)
	{
		$request = DirectRequest::findOrFail($id);
		return view('sap.show_direct',["request"=>$request]);
	}

	public function verify_excel($result)
	{
		$mapping = [
			0 => 'CVM Req No',
			1 => 'Notification no',
			2 => 'Notification Status',
			3 => 'FSE Code',
			4 => 'Model no',
			5 => 'Customer code',
			6 => 'Customer name',
			7 => 'City',
			8 => 'State',
			9 => 'Material',
			10 => 'Serial no.',
			11 => 'Equipment no',
			12 => 'Material Description'
		];
		$data = array_diff($mapping, $result);
		if (!empty($data)) {
			dd('Excel File invalid. Check below mentioned columns', $data);
		}
		return true;
	}

	public function ascii2utf8($array)
	{
		array_walk_recursive($array, function (&$item, $key) {
			$item = trim(preg_replace('/[^\PC\s]/u', '', $item));
		});
		return $array;
	}

	public function csv2array($filepath)
	{
		ini_set('auto_detect_line_endings', true);
		$csv_data = [];
		$csv_index = [];
		$row = 1;
		if (($handle = fopen($filepath, "r")) !== false) {
			while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
				$data = $this->ascii2utf8($data);
				$num = count($data);
				// echo "<p> $num fields in line $row: <br /></p>\n";
				if ( $row != 1 && !empty($data) || ( $row == 2 && !empty($data) )) {
					$request_ids = explode(",", $data[0]);
					foreach ($request_ids as $request_id) {
						if(strtolower($request_id) !== "ssbd"){
							$row_data = [];
							$row_data['cvm_req_complete']=$data[0];
							$row_data['cvm_req_no']=ltrim(trim($request_id), '0');
							$row_data['sap_id']=ltrim(trim($data[1]), '0');
							$row_data['status']=$data[2] ;
							$row_data['fse_code']=$data[3];
							$row_data['prod_model_no']=$data[4];
							$row_data['customer_code']=$data[5];
							$row_data['customer_name']=$data[6];
							$row_data['customer_city']=$data[7];
							$row_data['customer_state']=$data[8];
							$row_data['prod_material']=$data[9];
							$row_data['prod_serial_no']=$data[10];
							$row_data['prod_equipment_no']=ltrim(trim($data[11]), '0');
							$row_data['prod_material_description']=$data[12];
							array_push($csv_data, $row_data);
						}
					}
				}
				// if ($row == 2) { $this->verify_excel($data); }
				$row++;
			}
			fclose($handle);
		}
		return $csv_data;
	}

	public function store(Request $request)
	{
		$request = $request->toArray();
		$selected_ids = json_decode(stripslashes($request['selected_ids']),true);
		$table_data = Session::get($request['table_data'])[0];

		$data = [];
		foreach ($table_data as $key => $filter_data) {
			if(in_array($key, $selected_ids)){
				array_push($data, $filter_data);
			}
		}
		$messages = $this->process_store($data);
		Session::flash('messages', $messages);
		return redirect()->route('sapimported');
	}

	public function processDirectRequest($data, $type){
		$zone = find_region($data["customer_state"]);
		$zone = (empty($zone)) ? null : $zone ;
		$row_data = [
				"sap_id"=>$data["sap_id"],
				"status"=>$data["status"],
				"fse_code"=>$data["fse_code"],
				"customer_name"=>$data["customer_name"],
				"customer_code"=>$data["customer_code"],
				"customer_city"=>$data["customer_city"],
				"customer_state"=>$data["customer_state"],
				"prod_model_no"=>$data["prod_model_no"],
				"prod_material"=>$data["prod_material"],
				"prod_serial_no"=>$data["prod_serial_no"],
				"prod_equipment_no"=>$data["prod_equipment_no"],
				"prod_material_description"=>$data["prod_material_description"],
				"zone"=>$zone,

			];
		if ($type == 'update') {
			$request = DirectRequest::where('sap_id',$data['sap_id'])->first();
			if(!is_null($request)){
				$request->update($row_data);
				return $request->id;
			}
		}
		elseif ($type == 'create') {
			$is_direct_new = DirectRequest::where('sap_id',$row_data["sap_id"])->first();
			if(is_null($is_direct_new)){
				$request = DirectRequest::create($row_data)->id;
			}else{
				$request = $is_direct_new->id;
			}
			return $request;
		}
	}

	public function processCVMRequest($data, $type){
		$row_data = [
				"sap_id"=>$data["sap_id"],
				"status"=>$data["status"],
				"employee_code"=>$data["fse_code"]
			];
		if ($type == 'sap') {
			$request = ServiceRequests::where('sap_id',$data['sap_id'])->first();
		}
		elseif ($type == 'cvm') {
			$request = ServiceRequests::where('id',$data['cvm_req_no'])->first();
		}
		if(!is_null($request)){
			$request->update($row_data);

			$servicerequest = ServiceRequests::findOrFail($request->id);
	        $customer = Customers::findOrFail($servicerequest->customer_id);

	        $created_at = $servicerequest->created_at->toDateTimeString();
	        $oldData = $servicerequest;
	        // dd($oldData->request_type,$request->request_type);

	        $oldData_employee_code = null;
	        session()->forget('oldData_employee_code');
	        if ($oldData->employee_code != $request->employee_code) {
	            session(['oldData_employee_code' => $oldData->employee_code]);
	            $oldData_employee_code = $oldData->employee_code;
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

	        $hospitals = Hospitals::find($request->hospital_id);
	        $assigned_employee = EmployeeTeam::where('employee_code', $request->employee_code)->first();
	        $hospital_name = $hospitals->hospital_name;

            if (!(strtolower($servicerequest->status) == "attended" && $servicerequest->request_type == "service")) {
                //send_sms('status_update', $customer, $servicerequest, '');
                NotifyCustomer::send_notification('request_update', $servicerequest, $customer);
            }
	        $departments = Departments::find($servicerequest->dept_id);
	        $hospitals_list = Hospitals::where('customer_id', $customer->id)->get();
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
	            $to_emails_final['email'] = array_unique(array_flatten($to_emails));
	            $cc_emails_final['email'] = array_unique(array_flatten($cc_emails));
	        } else {
	            $to_emails = [];
	            $cc_emails = [];


	            $rules_list = AutoEmails::where('request_type', $servicerequest->request_type)->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();



	            if (!is_null($rules_list)) {
	                $to_emails_final['email'] = explode(',', $rules_list->to_emails);
	                $cc_emails_final['email'] = explode(',', $rules_list->cc_emails);
	            } else {
	                $to_emails_final['email'] = [];
	                $cc_emails_final['email'] = [];
	            }
	        }

			if ($servicerequest->request_type!='service') {
	            $cc_emails_final['email'] = array_merge($cc_emails_final,\Config('oly.enq_acad_coordinator_email'));
            }
	        $users = collect(array_flatten($to_emails_final['email']))->flatten();
	        $cc = collect(array_flatten($cc_emails_final['email']))->flatten();
	        $users_final = [];
	        $cc_final = [];
	        for ($i=0; $i < sizeof($users->all()); $i++) {
	            $users_final[]['email'] = $users[$i];
	        }

	        for ($j=0; $j < sizeof($cc->all()); $j++) {
	            $cc_final[]['email'] = $cc[$j];
	        }

	        if ($oldData->employee_code != $servicerequest->employee_code) {
	            $users_final[]['email'] = EmployeeTeam::where('employee_code', $oldData->employee_code)->first()->email;
	        }

	        $assigned_person = EmployeeTeam::where('employee_code', $servicerequest->employee_code)->first();
	        if($assigned_person){
	            $users_final[]['email'] = $assigned_person->email;
	        }

	        $pathToImage = json_decode(file_get_contents(env('APP_URL').'/capture_screenshot/'.$servicerequest->id.'/updated?oldData_employee_code='.$oldData_employee_code));

            if (env('MAIL_HOST', false) != 'smtp.mailtrap.io') {
                Mail::to($users_final)->cc($cc_final)
            	->send(new RequestUpdated($pathToImage, $servicerequest, $customer));
            }
		}
		else{
			Mail::raw('SAP Bulk Import Check: <br><br>'.json_encode($row_data), function($message){
			    $message->from('no-reply@olympusmyvoice.com', 'Olympus My Voice App');
			    $message->to('sarvar.kumar+alert@weareflamingo.in');
			});
		}
		// Update or insert product info
		$product_data = [
			"service_requests_id"=>$request->id,
			"pd_name"=>$data["prod_model_no"],
			"pd_serial"=>$data["prod_serial_no"],
			"pd_description"=>$data["prod_material_description"]
		];
		$product_info = ProductInfo::where('service_requests_id',$request->id)->first();
		if(is_null($product_info)){
			$product = ProductInfo::create($product_data);
			$prod_id = $product->id;
		}else{
			$product = $product_info->update($product_data);
			$prod_id = $product_info->id;
		}

		$return = [
			"request_id"=>$request->id,
			"product_id"=>$prod_id
		];
		return $return;
	}



	public function processCVMSplit($data, $type){
		$row_data = [
			"sap_id"=>$data["sap_id"],
			"status"=>$data["status"],
			"employee_code"=>$data["fse_code"]
		];
		$request = ServiceRequests::where('id',explode('.',$data['cvm_req_no'])[0])->first();
		// dd($data,$request,$row_data);
		if(!is_null($request)){
			// $request->replicate();
			$newrequest = new ServiceRequests;
			$newrequest->import_id = $data["cvm_req_no"];
			$newrequest->request_type = $request->request_type;
			$newrequest->sub_type = $request->sub_type;
			$newrequest->customer_id = $request->customer_id;
			$newrequest->hospital_id = $request->hospital_id;
			$newrequest->dept_id = $request->dept_id;
			$newrequest->sap_id = $data["sap_id"];
			$newrequest->sfdc_id = $request->sfdc_id;
			$newrequest->sfdc_customer_id = $request->sfdc_customer_id;
			$newrequest->product_category = $request->product_category;
			$newrequest->remarks = $request->remarks;
			$newrequest->status = $data["status"];
			if($data["status"]!= "Received"){
				$newrequest->employee_code = $data["fse_code"];
			}
			$newrequest->save();
			$newrequestId = $newrequest->id;
			$newrequest->update(["cvm_id"=>sprintf('%08d', $newrequestId)]);
			$servicerequest = ServiceRequests::findOrFail($newrequestId);
			// send mail and notify customer
			// send update email for request
			// send sms for request assigned to fse
		}
		else{
			Mail::raw('SAP Bulk Import Check SPLIT: <br><br>'.json_encode($row_data), function($message){
			    $message->from('no-reply@olympusmyvoice.com', 'Olympus My Voice App');
			    $message->to('sarvar.kumar+alert@weareflamingo.in');
			});
		}

		// Update or insert product info
		$product_data = [
			"service_requests_id"=>$newrequestId,
			"pd_name"=>$data["prod_model_no"],
			"pd_serial"=>$data["prod_serial_no"],
			"pd_description"=>$data["prod_material_description"]
		];
		$product_info = ProductInfo::where('service_requests_id',$newrequestId)->first();
		if(is_null($product_info)){
			$product = ProductInfo::create($product_data);
			$prod_id = $product->id;
		}else{
			$product = $product_info->update($product_data);
			$prod_id = $product_info->id;
		}

		return  [
			"request_id"=>$newrequestId,
			"product_id"=>$prod_id
		];
	}

	public function process_store($data_all)
	{
		$counter = 1;
		$messages = [];
		foreach ($data_all as $data) {
			switch ($data['trigger']) {
				case 'cvm_update_sapid':
					$cvm_return = $this->processCVMRequest($data, 'sap');
					$request_id = $cvm_return['request_id'];
					array_push($messages, [$counter, "CVM Request $request_id updated using SAP_ID.",$request_id,$data['type'],$cvm_return['product_id']]);
					break;

				case 'cvm_update_cvmid':
					$cvm_return = $this->processCVMRequest($data, 'cvm');
					$request_id = $cvm_return['request_id'];
					array_push($messages, [$counter, "CVM Request $request_id updated using CVM_ID.",$request_id,$data['type'],$cvm_return['product_id']]);
					break;

				case 'cvm_split_request':
					$cvm_return = $this->processCVMSplit($data, 'cvm');
					$request_id = $cvm_return['request_id'];
					array_push($messages, [$counter, "MyVoice Request $request_id created by splitting as ".$data['cvm_req_no'].".",$request_id,$data['type'],$cvm_return['product_id']]);
					break;

				case 'direct_new':
					$id = $this->processDirectRequest($data, 'create');
					array_push($messages, [$counter, "Direct Request with $id created.",$id,$data['type']]);
					break;

				case 'direct_update':
					$id = $this->processDirectRequest($data, 'update');
					array_push($messages, [$counter, "Direct Request with $id updated.",$id,$data['type']]);
					break;
			}
			$counter++;
			// Main loop to process
		}
		return $messages;
	}
}
