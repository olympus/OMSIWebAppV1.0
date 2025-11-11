<?php

namespace App\Http\Controllers;

use App\ApiRequests;
use App\Autoemail_Setting;
use App\Models\Departments;
use App\Models\AutoEmails;
use App\Models\Customers;
use App\Models\EmployeeTeam;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\Models\User;
use App\NotifyCustomer;
use App\Reportsetting;
use App\SettingModel;
use Cookie;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Mobile_Detect;
use Validator;

class AdditionalController extends Controller
{


    public function esasimport()
    {
        return view('development.esasimport');
    }

    public function esasimportstore(Request $request)
    {

        $attachment = $request->file('attachment');
        $excelfile = 'SAPImport_'.time().".".$attachment->getClientOriginalExtension();

        $attachment->move(storage_path('exports'), $excelfile);
        Excel::load(storage_path('exports/').$excelfile, function ($reader) {
            $results = $reader->toObject();
            foreach ($results as $key => $value) {
                foreach ($value->toArray() as $key1 => $value1) {
                    $data = explode("\t", $value1);
                    if (count($data) < 12) {
                        continue;
                    }
                    echo '<span style="color:red;">';
                    $cvm_req_no = $data[0];
                    $status = ucfirst(strtolower($data[2]));
                    $directentry = ["DIRECT-N/A","DIRECT- N/A","N/A-DIRECT","DIRECT CALL - NA"];
                    if (in_array($cvm_req_no, $directentry)) {
                        echo "Direct\t"."(Import to MyVoiceApp)<br>";
                    } else {
                        $request_ids = explode(",", $cvm_req_no);
                        foreach ($request_ids as $request) {
                            $request_id = ltrim($request, '0');
                            echo $request_id.":"."\t";
                            $status_cvm = ServiceRequests::where("id", $request_id)->value('status');
                            if ($status != $status_cvm) {
                                echo "Status changed from ".$status_cvm." to ".$status."<br>";
                            } else {
                                echo "Status Unchanged"."<br>";
                            }
                        }
                    }
                    echo '</span>';
                    print_r($data);
                    echo "<br><br>";
                }
            }
            //dd($results);
        });
    }


    public function apirequests_index1()
    {
        $requests = ApiRequests::orderBy("created_at", "DESC")->paginate(25);
        return view('apirequests.index', ['requests'=>$requests]);
    }

    public function api_requests_index()
    {
        return view('apirequests.index');
    }

    public function apirequestListOld(Request $request)
    {
        try {
            $columns = array(
                0 => 'id',
                1 => 'identifier',
                2 => 'request_type',
                3 => 'request_url',
                4 => 'request_body',
                5 => 'created_at',
                6 => 'updated_at'
            );

            $totalData = ApiRequests::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = ApiRequests::offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->select('id','identifier','request_type','request_url','request_body','created_at','updated_at')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  ApiRequests::
                select('id','identifier','request_type','request_url','request_body','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                //->orWhere('identifier', 'LIKE',"%{$search}%")
                //->orWhere('request_type', 'LIKE',"%{$search}%")
                ->orWhere('request_url', 'LIKE',"%{$search}%")
                ->orWhere('request_body', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->get();

                $totalFiltered = ApiRequests::
                select('id','identifier','request_type','request_url','request_body','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                //->orWhere('identifier', 'LIKE',"%{$search}%")
                //->orWhere('request_type', 'LIKE',"%{$search}%")
                ->orWhere('request_url', 'LIKE',"%{$search}%")
                ->orWhere('request_body', 'LIKE',"%{$search}%")
                ->count();
            }
            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $api_request) {
                    $nestedData['id'] = $api_request->id;
                    $nestedData['identifier'] = $api_request->identifier;
                    $nestedData['request_type'] = $api_request->request_type;
                    $nestedData['request_url'] = $api_request->request_url;
                    $nestedData['request_body'] = $api_request->request_body;
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($api_request->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($api_request->updated_at));
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

    public function apirequestList(Request $request)
    {
        if($request->from_date && $request->to_date){
            $current_date = $request->from_date.' 00:00:00';
            $old_date = $request->to_date.' 23:59:59';
        }else{
            $month = date('m');
            if($month >= 4){
                $y = date('Y');
                $pt = date('Y', strtotime('+1 year'));
            }else{
                $pt = date('Y');
                $y = date('Y', strtotime('-1 year'));
            }
            $current_date = $y."-04-01".' 00:00:00';
            $old_date = $pt."-03-31".' 23:59:59';
        }
        try {
            $columns = array(
                0 => 'id',
                1 => 'identifier',
                2 => 'request_type',
                3 => 'request_url',
                4 => 'request_body',
                5 => 'created_at',
                6 => 'updated_at'
            );

            $totalData = ApiRequests::whereBetween('created_at', [$current_date, $old_date])->count();
            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = ApiRequests::offset($start)
                ->limit($limit)
                ->orderBy('id', $dir)
                ->whereBetween('created_at', [$current_date, $old_date])
                ->select('id','identifier','request_type','request_url','request_body','created_at','updated_at')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  ApiRequests::
                select('id','identifier','request_type','request_url','request_body','created_at','updated_at')
                ->whereBetween('created_at', [$current_date, $old_date])
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('request_url', 'LIKE',"%{$search}%")
                ->orWhere('request_body', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->get();

                $totalFiltered = ApiRequests::
                select('id','identifier','request_type','request_url','request_body','created_at','updated_at')
                ->whereBetween('created_at', [$current_date, $old_date])
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('request_url', 'LIKE',"%{$search}%")
                ->orWhere('request_body', 'LIKE',"%{$search}%")
                ->count();
            }
            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $api_request) {
                    $nestedData['id'] = $api_request->id;
                    $nestedData['identifier'] = $api_request->identifier;
                    $nestedData['request_type'] = $api_request->request_type;
                    $nestedData['request_url'] = $api_request->request_url;
                    $nestedData['request_body'] = $api_request->request_body;
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($api_request->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($api_request->updated_at));
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

    public function khuljasimsim()
    {
        Cookie::queue('developer', 'TRUE', time() + (10 * 365 * 24 * 60 * 60));
        return redirect('admin/merged-service-requests');
    }

    public function app_redirect()
    {
        $detect = new Mobile_Detect;
        if ($detect->isiOS()) {
            return redirect('https://itunes.apple.com/us/app/my-voice/id1396546430?ls=1&mt=8');
        }
        if ($detect->isAndroidOS()) {
            return redirect('https://play.google.com/store/apps/details?id=com.app.olympus');
        }
    }

    public function exportOlympusCustomers()
    {
        $user = Customers::select('id', 'customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')->where('email', 'LIKE', '%@olympus.com%')->get();
        // ->except('password','device_token','middle_name','otp_code','valid_upto');
        // dd($user);
        foreach ($user as $user_temp) {
            $hospitals_ids = explode(',', $user_temp->hospital_id);
            $hospitals = Hospitals::where('customer_id', $user_temp->id)->get();
            $hospitals_name = Hospitals::where('customer_id', $user_temp->id)->pluck('hospital_name')->all();
            $hospital_names = implode(', ', $hospitals_name);
            $count = 1;
            foreach ($hospitals as $hospital) {
                $dept_ids = explode(',', $hospital->dept_id);
                $departments = Departments::whereIn('id', $dept_ids)->pluck('name')->all();
                $depart_names = implode(', ', $departments);
            }
            $user_temp->hospital_names = $hospital_names;
            $user_temp->departments = $depart_names;
        }
        Excel::create('Olympus Customers', function ($excel) use ($user) {
            $excel->sheet('Sheet 1', function ($sheet) use ($user) {
                $sheet->fromArray($user);
            });
        })->export('xls');
    }

    public function exportsID($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|numeric',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $servicerequest = ServiceRequests::findOrFail($id);
        $customer = Customers::findOrFail($servicerequest->customer_id);
        $hospArr = explode(',', $customer->hospital_id);
        $hospitals_cust = Hospitals::whereIn('id', $hospArr)->get();
        $hospitals = Hospitals::find($servicerequest->hospital_id);
        $count = 0;
        Excel::create('ServiceRequests-'.$id, function ($excel) use ($customer,$servicerequest,$hospitals,$hospArr,$count,$hospitals_cust) {
            $excel->sheet('RequestData', function ($sheet) use ($customer,$servicerequest,$hospitals,$hospArr,$count,$hospitals_cust) {
                $sheet->loadView('excel.servicerequest', array('servicerequest'=>$servicerequest,'customer'=>$customer,'hospArr'=>$hospArr,'hospitals_cust'=>$hospitals_cust,'hospitals'=>$hospitals,'count'=>$count));

                //Heading for General Information
                $sheet->mergeCells('A1:B1');
                // Set Cells in center
                $sheet->row(1, function ($row) {
                    // call cell manipulation methods
                    $row->setAlignment('center');
                });
                //Heading for Reuqest Details
                $sheet->mergeCells('A6:B6');
                // Set Cells in center
                $sheet->row(6, function ($row) {
                    // call cell manipulation methods
                    $row->setAlignment('center');
                });

                //Heading for Profile
                $sheet->mergeCells('A14:B14');
                // Set Cells in center
                $sheet->row(14, function ($row) {

                    // call cell manipulation methods
                    $row->setAlignment('center');
                });
                $offset = 20;
                for ($i=0;$i<count($hospitals_cust);$i++) {
                    //Heading for Hospital
                    $rowc = ($i*10)+4+$offset;
                    $sheet->mergeCells('A'.$rowc.':B'.$rowc);
                    // Set Cells in center
                    $sheet->row($rowc, function ($row) {

                    // call cell manipulation methods
                        $row->setAlignment('center');
                    });
                }
                $sheet->setAutoSize(true);
            });
        })
        ->store('xls', storage_path('/exports/'));
        // ->export('xls');
    }


    public function notifyToUpdate()
    {
        $customers = Customers::where(function ($q) {
            $q->where([['platform','=','iOS'],['app_version','<', \Config('oly.current_version_iOS')]])
        ->orWhere([['platform','=','android'],['app_version','<', \Config('oly.current_version_android')]]);
        })->get();
        $servicerequest = ServiceRequests::find(61);
        foreach ($customers as $customer) {
            NotifyCustomer::send_notification('app_update_available', $servicerequest, $customer);
        }
        return 'success';
    }

    public function emailExistsIndex(){
        return view('email_exists');
    }

    public function emailExistsVerify(Request $request){
        $email = $request->email;
        $validator = Validator::make(
          [
            'email' => $email,
          ],[
            'email' => 'required|email|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
          ]
        );

        if ($validator->fails()) {
            return view('email_exists')->withErrors($validator->messages()->first());
        }

        $email_q = "%".$email."%";

        $config = substr_count(file_get_contents(base_path() . "/config/oly.php") , $email);
        $autoemail_settings = Autoemail_Setting::where('user_email','LIKE',$email_q)->count();
        $autoemaillist = AutoEmails::where('to_emails','LIKE',$email_q)->orWhere('cc_emails','LIKE',$email_q)->count();
        $customers = Customers::where('email','LIKE',$email_q)->count();
        $employee_team = EmployeeTeam::
            where('escalation_1','LIKE',$email_q)
            ->orWhere('escalation_2','LIKE',$email_q)
            ->orWhere('escalation_3','LIKE',$email_q)
            ->orWhere('escalation_4','LIKE',$email_q)->count();
        $employee_enabled = EmployeeTeam::
            where('email','LIKE',$email_q)->value('disabled');
        $reportsettings = Reportsetting::where('to_emails','LIKE',$email_q)->orWhere('cc_emails','LIKE',$email_q)->count();
        $settings = SettingModel::where('value','LIKE',$email_q)->count();
        $users = User::where('email','LIKE',$email_q)->count();

        $service_requests = EmployeeTeam::join('service_requests','service_requests.employee_code','=','employee_team.employee_code')
        	->where("email",$email)->where("status","!=","Closed")->count();

        $errors = [];
        if (!empty($config)){$errors[] = "$config found in configuration file <br>";}
        if (!empty($autoemail_settings)){$errors[] = "$autoemail_settings found in autoemail_settings <br>";}
        if (!empty($autoemaillist)){$errors[] = "$autoemaillist found in autoemaillist <br>";}
        if (!empty($customers)){$errors[] = "$customers found in customers <br>";}
        if ($employee_enabled == "0"){$errors[] = "$email is still enabled. <br>";}
        if (!empty($employee_team)){$errors[] = "$employee_team found in employee_team as escalation<br>";}
        if (!empty($reportsettings)){$errors[] = "$reportsettings found in reportsettings <br>";}
        if (!empty($settings)){$errors[] = "$settings found in settings <br>";}
        if (!empty($users)){$errors[] = "$users found in users <br>";}
        if (!empty($service_requests)){$errors[] = "$service_requests unclosed  service_requests <br>";}

        if($errors){
            return view('email_exists',["email"=>$email])->withErrors($errors);
        }else{
            return view('email_exists',["email"=>$email])->with(["email"=>$email, "message"=>"No occurances found."]);
        }
    }
}
