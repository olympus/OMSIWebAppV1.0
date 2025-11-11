<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Mail\RequestUpdated;
use App\Models\AutoEmails;
use App\Models\Customers;
use App\Models\EmployeeTeam;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\NotifyCustomer;
use App\StatusTimeline;
use Auth;
use DB;
use Excel;
use Illuminate\Http\Request;
use Mail;
use Session;
use Validator;

class EsasController extends Controller
{
	public function verify(Request $request)
	{
		$this->validate($request, [
            'attachment' => 'mimes:xlsx,doc,docx,ppt,pptx,ods,odt,odp,application/csv,application/excel, application/vnd.ms-excel, application/vnd.msexcel, text/csv, text/anytext, text/plain, text/x-c, text/comma-separated-values, inode/x-empty, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
		$attachment = $request->file('attachment');
		$excelfile = 'ESASImport_'.time().".".$attachment->getClientOriginalExtension();
		$attachment->move(storage_path('exports'), $excelfile);
		$filepath = storage_path('exports/').$excelfile;
		$csv_data = [];
		Excel::selectSheets('Sheet1')->load($filepath, function($reader) use (&$csv_data) {
			$results = $reader->toArray();
			foreach($results as $result){
				// dd($result);
				// $invalid = ['direct','loan',''];
				$myvoiceno = strtolower($result['my_voice_no.']);
				if(strpos($myvoiceno, ".") !== false){
					$myvoiceno1 = ServiceRequests::where("import_id", $myvoiceno)->value('id');
					$myvoiceno = (is_null($myvoiceno1)) ? $myvoiceno : $myvoiceno1 ;
				}
				if(!empty($myvoiceno) && is_numeric($myvoiceno)){
				// if(strpos($myvoiceno, 'direct') !== false && strpos($myvoiceno, 'loan') !== false)
					$csv_data[] = [
						'external_no.'=>$result['external_no.'],
						'status'=>$result['repair_status'],
						'cvm_req_no'=>$myvoiceno,
						// 'cvm_req_no'=>'',
					];
				}
			}
		});
		$table_data = [];

		foreach ($csv_data as $data) {
			$request_id = $data["cvm_req_no"];
			$new_status = $this->validate_status(strtolower($data["status"]));

			$existingCVM = ServiceRequests::where("id", $request_id)->first();
			if (!is_null($existingCVM)) {
				$action_type = "CVM Existing";
			} else {
				$action_type = "CVM Deleted";
			}

			switch ($action_type) {
				case 'CVM Deleted':
					$temp_row = [
						"cvm_req_no"=>$request_id,
						"previous_status"=>null,
						"action"=>$action_type,
						"checked"=>0,
						"trigger"=>'cvm_ignore'
					];
					array_push($table_data, $this->makeRowDataArr($temp_row,$new_status));
					break;

				case 'CVM Existing':
					$checkStatus = $this->checkStatus($existingCVM->status,$new_status);
					$temp_row = [
						"cvm_req_no"=>$request_id,
						"previous_status"=>$existingCVM->status,
						"action"=>$checkStatus["table_action"],
						"checked"=>$checkStatus["checked"],
						"trigger"=>'cvm_update_cvmid'
					];
					array_push($table_data, $this->makeRowDataArr($temp_row,$new_status));
					break;
			}
		}
		// dd($table_data);
		return view('esas.verify')->with('table_data',$table_data);
	}


	public function checkStatus($previous_status,$new_status){
		$allowed_statuses = ["Received_At_Repair_Center","Quotation_Prepared","PO_Received","Repair_Started","Repair_Completed","Dispatched"];
		$disabled_statuses = ["Received","Income_Inspected","Recognized","Parts_Arranged","Assigned","Repair_Completed1","Confirmed","Ready_To_Dispatch"];
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
			$table_action = (in_array($new_status,$disabled_statuses)) ? "Skip" : "Unknown Status" ;
			$checked = 0;
		}
		return ["table_action"=>$table_action, "checked"=>$checked];
	}

	public function makeRowDataArr($temp_row,$new_status)
	{
		$previous_status = ($temp_row["previous_status"] == "BROUGHT TO SC") ? "Received_At_Repair_Center" : $temp_row["previous_status"];
		$row_data = [
			'cvm_req_no'=>$temp_row["cvm_req_no"],
			'previous_status'=>$previous_status,
			'status'=>$new_status,
			'action'=>$temp_row["action"],
			'checked'=>$temp_row["checked"],
			'trigger'=>$temp_row["trigger"],
		];
		return $row_data;
	}

	public function validate_status($status){
		$raw_keys = [
			'Received' => ['received'],
			'Received_At_Repair_Center' => ['registered'],
			'Income_Inspected' => ['income_inspected','income inspected'],
			'Quotation_Prepared' => ['quoted'],
			'Recognized' => ['recognized'],
			'PO_Received' => ['po_received','po received'],
			'Parts_Arranged' => ['parts_arranged','parts arranged'],
			'Repair_Started' => ['repair_started','repair started'],
			'Assigned' => ['assigned'],
			'Repair_Completed1' => ['repair_completed','repair completed'],
			'Repair_Completed' => ['final_inspected','final inspected'],
			'Ready_To_Dispatch' => ['invoiced'],
			'Dispatched' => ['returned'],
			'Confirmed' => ['confirmed'],
		];
		foreach ($raw_keys as $raw_key => $value) {
			if(in_array($status, $value)){
				return $raw_key;
			}
		}
		return $status;
	}

	public function validate_status_increment($previous_status,$new_status){
		$status_keys = array_keys(\Config('oly.requests_statuses.service_repair'));
		if(in_array($previous_status, $status_keys) && in_array($new_status, $status_keys)){
			$previous_index = array_search($previous_status, $status_keys);
			$new_index = array_search($new_status, $status_keys);
			if($previous_index > $new_index){
				return false;
			}
		}
		return true;
	}


	public function index()
	{
		return view('esas.index');
	}

	public function imported()
	{
		return view('esas.final');
	}

	public function verify_excel($result)
	{
		$validator = Validator::make(
          [
            'result' => $result,
          ],[
            'result' => 'required|string',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
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
		return redirect()->route('esasimported');
	}

	public function processCVMRequest($data, $type){
		// dd($data);
		$request = ServiceRequests::where('id',$data['cvm_req_no'])->first();
		if(!is_null($request)){
			$request->update(["status"=>$data["status"]]);

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
			Mail::raw('ESAS Bulk Import Check: <br><br>'.json_encode($data), function($message){
			    $message->from('no-reply@olympusmyvoice.com', 'Olympus My Voice App');
			    $message->to('sarvar.kumar+alert@weareflamingo.in');
			});
		}
		return $request->id;
	}

	public function process_store($data_all)
	{
		// dd($data_all);
		$counter = 1;
		$messages = [];
		foreach ($data_all as $data) {
			$request_id = $this->processCVMRequest($data, 'cvm');
			array_push($messages, [
				$counter,
				"My Voice Request $request_id updated using My_Voice_ID.",
				$request_id,
			]);
			$counter++;
			// Main loop to process
		}
		// dd($messages,$counter);
		return $messages;
	}
}
