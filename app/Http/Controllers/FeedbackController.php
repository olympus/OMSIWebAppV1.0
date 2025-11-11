<?php

namespace App\Http\Controllers;
use App\DataTables\feedbackDataTable;
use App\Models\Departments;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
use App\Models\Feedback;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use DB;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(feedbackDataTable $dataTable)
    {
        return view('feedback.index');
        //return $dataTable->render('feedback.index');
    }

    public function feedbackListOld(Request $request)
    {
        try{
            $columns = array(
                0 => 'id',
                1 => 'request_id',
                2 => 'response_speed',
                3 => 'quality_of_response',
                4 => 'app_experience',
                5 => 'olympus_staff_performance',
                6 => 'remarks',
                7 => 'first_name',
                8 => 'last_name',
                9 => 'hospital_names',
                10 => 'departments',
                11 => 'city_names',
                12 => 'state_names',
                13 => 'request_type',
                14 => 'sub_type',
                15 => 'created_at',
                16 => 'updated_at',
            );

            $totalData = Feedback::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = Feedback::offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->select('id','request_id','response_speed','quality_of_response','app_experience','olympus_staff_performance','remarks','created_at','updated_at')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  Feedback::
                select('id','request_id','response_speed','quality_of_response','app_experience','olympus_staff_performance','remarks','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('request_id', 'LIKE',"%{$search}%")
                ->orWhere('response_speed', 'LIKE',"%{$search}%")
                ->orWhere('app_experience', 'LIKE',"%{$search}%")
                ->orWhere('olympus_staff_performance', 'LIKE',"%{$search}%")
                ->orWhere('remarks', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->get();

                $totalFiltered = Feedback::
                select('id','request_id','response_speed','quality_of_response','app_experience','olympus_staff_performance','remarks','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('request_id', 'LIKE',"%{$search}%")
                ->orWhere('response_speed', 'LIKE',"%{$search}%")
                ->orWhere('app_experience', 'LIKE',"%{$search}%")
                ->orWhere('olympus_staff_performance', 'LIKE',"%{$search}%")
                ->orWhere('remarks', 'LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $current_user) {
                    $user_temp = ServiceRequests::where('id',$current_user->request_id)->first();
                    if(!empty($user_temp)){
                        $hospitals = Hospitals::where('customer_id',$user_temp->customer_id)->get();
                        $hospitals_name = Hospitals::where('customer_id',$user_temp->customer_id)->pluck('hospital_name')->all();
                        $hospital_names = implode(', ', $hospitals_name);
                        $count = 1;

                        $city = [];
                        $state = [];
                        $depart_names = '';
                        foreach($hospitals as $hospital){
                            $dept_ids = explode(',',$hospital->dept_id);
                            $departments = Departments::whereIn('id',$dept_ids)->pluck('name')->all();
                            $depart_names = implode(', ', $departments);
                            $city[] = $hospital->city;
                            $state[] = $hospital->state;
                        }
                        $cust_details = Customers::select('first_name','last_name')->where('id',$user_temp->customer_id)->first();
                    }

                    $show =  route('feedback.show',$current_user->id);


                    if($current_user->response_speed == 5 ){
                        $response_speed =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
                    }elseif($current_user->response_speed == 4){
                        $response_speed =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->response_speed == 3){
                        $response_speed =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->response_speed == 2){
                        $response_speed =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->response_speed == 1){
                        $response_speed = "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }

                    if($current_user->quality_of_response == 5 ){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
                    }elseif($current_user->quality_of_response == 4){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->quality_of_response == 3){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->quality_of_response == 2){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->quality_of_response == 1){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }

                    if($current_user->app_experience == 5 ){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
                    }elseif($current_user->app_experience == 4){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->app_experience == 3){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->app_experience == 2){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->app_experience == 1){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }

                    if($current_user->olympus_staff_performance == 5 ){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
                    }elseif($current_user->olympus_staff_performance == 4){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->olympus_staff_performance == 3){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->olympus_staff_performance == 2){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->olympus_staff_performance == 1){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }


                    $nestedData['id'] = $current_user->id;
                    $nestedData['request_id'] = $current_user->request_id;
                    $nestedData['response_speed'] = $response_speed;
                    $nestedData['quality_of_response'] = $quality_of_response;
                    $nestedData['app_experience'] = $app_experience;
                    $nestedData['olympus_staff_performance'] = $olympus_staff_performance;
                    $nestedData['remarks'] = $current_user->remarks;
                    $nestedData['first_name'] = $cust_details->first_name;
                    $nestedData['last_name'] = $cust_details->last_name;
                    $nestedData['hospital_names'] = $hospital_names;
                    $nestedData['departments'] = $depart_names;
                    $nestedData['city_names'] = implode(',',array_unique($city));
                    $nestedData['state_names'] = implode(',',array_unique($state));
                    if(!empty($user_temp->request_type)){
                        $nestedData['request_type'] = $user_temp->request_type;
                    }else{
                        $nestedData['request_type'] = '';
                    }
                    if(!empty($user_temp->sub_type)){
                        $nestedData['sub_type'] = $user_temp->sub_type;
                    }else{
                        $nestedData['sub_type'] = '';
                    }


                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($current_user->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($current_user->updated_at));
                    $nestedData['options'] = "
                        <a style='width: 100%;' class='btn btn-xs btn-success' href='{$show}'  title='Show'>Show</a>

                    ";
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

    public function feedbackList23March(Request $request)
    {
        try{
            $columns = array(
                0 => 'id',
                1 => 'request_id',
                2 => 'response_speed',
                3 => 'quality_of_response',
                4 => 'app_experience',
                5 => 'olympus_staff_performance',
                6 => 'remarks',
                7 => 'first_name',
                8 => 'last_name',
                9 => 'hospital_names',
                10 => 'departments',
                11 => 'city_names',
                12 => 'state_names',
                13 => 'request_type',
                14 => 'sub_type',
                15 => 'created_at',
                16 => 'updated_at',
            );

            $totalData = Feedback::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = Feedback::offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->select('id','request_id','response_speed','quality_of_response','app_experience','olympus_staff_performance','remarks','created_at','updated_at')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  Feedback::
                select('id','request_id','response_speed','quality_of_response','app_experience','olympus_staff_performance','remarks','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('request_id', 'LIKE',"%{$search}%")
                ->orWhere('response_speed', 'LIKE',"%{$search}%")
                ->orWhere('app_experience', 'LIKE',"%{$search}%")
                ->orWhere('olympus_staff_performance', 'LIKE',"%{$search}%")
                ->orWhere('remarks', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->get();

                $totalFiltered = Feedback::
                select('id','request_id','response_speed','quality_of_response','app_experience','olympus_staff_performance','remarks','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('request_id', 'LIKE',"%{$search}%")
                ->orWhere('response_speed', 'LIKE',"%{$search}%")
                ->orWhere('app_experience', 'LIKE',"%{$search}%")
                ->orWhere('olympus_staff_performance', 'LIKE',"%{$search}%")
                ->orWhere('remarks', 'LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                $city = [];
                $state = [];
                foreach ($posts as $current_user) {
                    $chk_service_request = ServiceRequests::where('id',$current_user->request_id)->first();
                    if(!empty($chk_service_request)){
                        $user_temp = ServiceRequests::where('id',$current_user->request_id)->first();
                    }else{
                        $user_temp = ArchiveServiceRequests::where('id',$current_user->request_id)->first();
                    }
                    if(!empty($user_temp)){
                        $hospitals = Hospitals::where('customer_id',$user_temp->customer_id)->get();
                        $hospitals_name = Hospitals::where('customer_id',$user_temp->customer_id)->pluck('hospital_name')->all();
                        $hospital_names = implode(', ', $hospitals_name);
                        $count = 1;

                        $city = [];
                        $state = [];
                        $depart_names = '';
                        foreach($hospitals as $hospital){
                            $dept_ids = explode(',',$hospital->dept_id);
                            $departments = Departments::whereIn('id',$dept_ids)->pluck('name')->all();
                            $depart_names = implode(', ', $departments);
                            $city[] = $hospital->city;
                            $state[] = $hospital->state;
                        }
                        $cust_details = Customers::select('first_name','last_name')->where('id',$user_temp->customer_id)->first();
                    }

                    $show =  route('feedback.show',$current_user->id);


                    if($current_user->response_speed == 5 ){
                        $response_speed =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
                    }elseif($current_user->response_speed == 4){
                        $response_speed =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->response_speed == 3){
                        $response_speed =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->response_speed == 2){
                        $response_speed =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->response_speed == 1){
                        $response_speed = "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }

                    if($current_user->quality_of_response == 5 ){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
                    }elseif($current_user->quality_of_response == 4){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->quality_of_response == 3){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->quality_of_response == 2){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->quality_of_response == 1){
                        $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }

                    if($current_user->app_experience == 5 ){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
                    }elseif($current_user->app_experience == 4){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->app_experience == 3){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->app_experience == 2){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->app_experience == 1){
                        $app_experience =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }

                    if($current_user->olympus_staff_performance == 5 ){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
                    }elseif($current_user->olympus_staff_performance == 4){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->olympus_staff_performance == 3){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->olympus_staff_performance == 2){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }elseif($current_user->olympus_staff_performance == 1){
                        $olympus_staff_performance =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
                    }


                    $nestedData['id'] = $current_user->id ?? '';
                    $nestedData['request_id'] = $current_user->request_id ?? '';
                    $nestedData['response_speed'] = $response_speed ?? '';
                    $nestedData['quality_of_response'] = $quality_of_response ?? '';
                    $nestedData['app_experience'] = $app_experience ?? '';
                    $nestedData['olympus_staff_performance'] = $olympus_staff_performance ?? '';
                    $nestedData['remarks'] = $current_user->remarks ?? '';
                    $nestedData['first_name'] = $cust_details->first_name ?? '';
                    $nestedData['last_name'] = $cust_details->last_name ?? '';
                    $nestedData['hospital_names'] = $hospital_names ?? '';
                    $nestedData['departments'] = $depart_names ?? '';
                    $nestedData['city_names'] =  implode(',',array_unique($city));
                    $nestedData['state_names'] = implode(',',array_unique($state));
                    if(!empty($user_temp->request_type)){
                        $nestedData['request_type'] = $user_temp->request_type ?? '';
                    }else{
                        $nestedData['request_type'] = '';
                    }
                    if(!empty($user_temp->sub_type)){
                        $nestedData['sub_type'] = $user_temp->sub_type ?? '';
                    }else{
                        $nestedData['sub_type'] = '';
                    }


                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($current_user->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($current_user->updated_at));
                    $nestedData['options'] = "
                        <a style='width: 100%;' class='btn btn-xs btn-success' href='{$show}'  title='Show'>Show</a>

                    ";
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

    public function feedbackList(Request $request)
    {
        // if($request->from_date && $request->to_date && $request->data_type == 'archive_data'){
        //     $from_date = $request->from_date.' 00:00:00';
        //     $to_date = $request->to_date.' 23:59:59';
        // }elseif($request->from_date && $request->to_date && $request->data_type == 'current_data'){
        //     $month = date('m');
        //     if($month >= 4){
        //         $y = date('Y');
        //         $pt = date('Y', strtotime('+1 year'));
        //     }else{
        //         $pt = date('Y');
        //         $y = date('Y', strtotime('-1 year'));
        //     }
        //     $from_date = $y."-04-01".' 00:00:00';
        //     $to_date = $pt."-03-31".' 23:59:59';
        // }else{
        //     $month = date('m');
        //     if($month >= 4){
        //         $y = date('Y');
        //         $pt = date('Y', strtotime('+1 year'));
        //     }else{
        //         $pt = date('Y');
        //         $y = date('Y', strtotime('-1 year'));
        //     }
        //     $from_date = $y."-04-01".' 00:00:00';
        //     $to_date = $pt."-03-31".' 23:59:59';
        // }
        if($request->from_date && $request->to_date){
            $from_date = $request->from_date.' 00:00:00';
            $to_date = $request->to_date.' 23:59:59';
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
        try{
            $columns = array(
                0 => 'id',
                1 => 'request_id',
                2 => 'response_speed',
                3 => 'quality_of_response',
                4 => 'app_experience',
                5 => 'olympus_staff_performance',
                6 => 'remarks',
                7 => 'first_name',
                8 => 'last_name',
                9 => 'hospital_names',
                10 => 'departments',
                11 => 'city_names',
                12 => 'state_names',
                13 => 'request_type',
                14 => 'sub_type',
                15 => 'created_at',
                16 => 'updated_at',
            );

            $totalData = Feedback::
                whereBetween('created_at', [$from_date, $to_date])
                ->with(['ServiceRequestData','ArchiveServiceRequestData'])
                ->count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = Feedback::select(
                    'id',
                    'request_id',
                    'response_speed',
                    'quality_of_response',
                    'app_experience',
                    'olympus_staff_performance',
                    'remarks',
                    'created_at',
                    'updated_at'
                )
                ->whereBetween('created_at', [$from_date, $to_date])
                ->with(['ServiceRequestData','ArchiveServiceRequestData'])
                ->offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->get();
            }
            else{
                $search = $request->input('search.value');

                $posts = Feedback::select(
                    'id',
                    'request_id',
                    'response_speed',
                    'quality_of_response',
                    'app_experience',
                    'olympus_staff_performance',
                    'remarks',
                    'created_at',
                    'updated_at'
                )
                ->whereBetween('created_at', [$from_date, $to_date])
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('request_id', 'LIKE',"%{$search}%")
                ->orWhere('response_speed', 'LIKE',"%{$search}%")
                ->orWhere('app_experience', 'LIKE',"%{$search}%")
                ->orWhere('olympus_staff_performance', 'LIKE',"%{$search}%")
                ->orWhere('remarks', 'LIKE',"%{$search}%")
                ->with(['ServiceRequestData','ArchiveServiceRequestData'])
                ->offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->get();


                $totalFiltered = Feedback::select(
                    'id',
                    'request_id',
                    'response_speed',
                    'quality_of_response',
                    'app_experience',
                    'olympus_staff_performance',
                    'remarks',
                    'created_at'
                )
                ->whereBetween('created_at', [$from_date, $to_date])
                ->with(['ServiceRequestData','ArchiveServiceRequestData'])
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('request_id', 'LIKE',"%{$search}%")
                ->orWhere('response_speed', 'LIKE',"%{$search}%")
                ->orWhere('app_experience', 'LIKE',"%{$search}%")
                ->orWhere('olympus_staff_performance', 'LIKE',"%{$search}%")
                ->orWhere('remarks', 'LIKE',"%{$search}%")
                ->count();
            }
            $data = [];
            if(!empty($posts))
            {
                foreach ($posts as $new_datas) {
                    if(!empty($new_datas->ServiceRequestData) ||$new_datas->ServiceRequestData != null || $new_datas->ServiceRequestData !=  ''){

                        $hospital = $new_datas->ServiceRequestData->hospital;
                        $department = $new_datas->ServiceRequestData->departmentData;
                        $assigned_employee = $new_datas->ServiceRequestData->employeeData;
                        $cust_details = $new_datas->ServiceRequestData->customer;

                        $request_type = $new_datas->ServiceRequestData->request_type;
                        $sub_type = $new_datas->ServiceRequestData->sub_type;
                        $request_id = $new_datas->ServiceRequestData->id;
                    }elseif(!empty($new_datas->ArchiveServiceRequestData) ||$new_datas->ArchiveServiceRequestData != null || $new_datas->ArchiveServiceRequestData !=  ''){
                        $hospital = $new_datas->ArchiveServiceRequestData->hospital;
                        $department = $new_datas->ArchiveServiceRequestData->departmentData;
                        $assigned_employee = $new_datas->ArchiveServiceRequestData->employeeData;
                        $cust_details = $new_datas->ArchiveServiceRequestData->customer;

                        $request_type = $new_datas->ArchiveServiceRequestData->request_type;
                        $sub_type = $new_datas->ArchiveServiceRequestData->sub_type;
                        $request_id = $new_datas->ArchiveServiceRequestData->id;
                    }

                    if (!isset($assigned_employee->name)) {
                        $assign =  "Assigned employee not found for Feedback ID $new_datas->id";
                    }else{
                        $assign =  $assigned_employee->name." employee assigned for this Feedback ID $new_datas->id";
                    }

                    $show =  route('feedback.show',$new_datas->id);

                    $response_speed = $this->star($new_datas->response_speed ?? '');
                    $quality_of_response = $this->star($new_datas->quality_of_response ?? '');
                    $app_experience = $this->star($new_datas->app_experience ?? '');
                    $olympus_staff_performance = $this->star($new_datas->olympus_staff_performance ?? '');

                    $nestedData['id'] = $new_datas->id ?? '';
                    $nestedData['request_id'] = $request_id ?? '';
                    $nestedData['response_speed'] = $response_speed ?? '';
                    $nestedData['quality_of_response'] = $quality_of_response ?? '';
                    $nestedData['app_experience'] = $app_experience ?? '';
                    $nestedData['olympus_staff_performance'] = $olympus_staff_performance ?? '';
                    $nestedData['remarks'] = $new_datas->remarks ?? '';
                    $nestedData['first_name'] = $cust_details->first_name ?? '';
                    $nestedData['last_name'] = $cust_details->last_name ?? '';
                    $nestedData['hospital_names'] = $hospital->hospital_name ?? '';
                    $nestedData['departments'] = $department->name ?? '';
                    $nestedData['city_names'] =  $hospital->city ?? '';
                    $nestedData['state_names'] = $hospital->state ?? '';
                    if(!empty($request_type)){
                        $nestedData['request_type'] = $request_type ?? '';
                    }else{
                        $nestedData['request_type'] = '';
                    }
                    if(!empty($sub_type)){
                        $nestedData['sub_type'] = $sub_type ?? '';
                    }else{
                        $nestedData['sub_type'] = '';
                    }

                    $nestedData['assigned_employee_name'] = $assign ?? '';
                    $nestedData['assigned_engineer'] = $assigned_employee->name ?? '';
                    $nestedData['employee_code'] = $assigned_employee->employee_code ?? '';
                    $nestedData['responsible_branch'] = $hospital->responsible_branch ?? '';
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($new_datas->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($new_datas->updated_at));
                    $nestedData['options'] = "
                        <a style='width: 100%;' class='btn btn-xs btn-success' href='{$show}'  title='Show'>Show</a>

                    ";
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
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
        $user = Feedback::findOrFail($id);
        return view('feedback.show', ['user'=>$user]);
    }

    public function export(Request $request)
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        // if($request->from_date && $request->to_date && $request->data_type == 'archive_data'){
        //     $from_date = $request->from_date.' 00:00:00';
        //     $to_date = $request->to_date.' 23:59:59';
        // }elseif($request->from_date && $request->to_date && $request->data_type == 'current_data'){
        //     $month = date('m');
        //     if($month >= 4){
        //         $y = date('Y');
        //         $pt = date('Y', strtotime('+1 year'));
        //     }else{
        //         $pt = date('Y');
        //         $y = date('Y', strtotime('-1 year'));
        //     }
        //     $from_date = $y."-04-01".' 00:00:00';
        //     $to_date = $pt."-03-31".' 23:59:59';
        // }else{
        //     $month = date('m');
        //     if($month >= 4){
        //         $y = date('Y');
        //         $pt = date('Y', strtotime('+1 year'));
        //     }else{
        //         $pt = date('Y');
        //         $y = date('Y', strtotime('-1 year'));
        //     }
        //     $from_date = $y."-04-01".' 00:00:00';
        //     $to_date = $pt."-03-31".' 23:59:59';
        // }
        if($request->from_date && $request->to_date){
            $from_date = $request->from_date.' 00:00:00';
            $to_date = $request->to_date.' 23:59:59';
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
        $new_data = Feedback::select(
            'id',
            'request_id',
            'response_speed',
            'quality_of_response',
            'app_experience',
            'olympus_staff_performance',
            'remarks',
            'created_at'
        )
        ->whereBetween('created_at', [$from_date, $to_date])
        ->with(['ServiceRequestData','ArchiveServiceRequestData'])
        ->orderBy('created_at', 'DESC')
        ->get();
        $final_data = [];
        foreach ($new_data as $new_datas) {
            if(!empty($new_datas->ServiceRequestData) ||$new_datas->ServiceRequestData != null || $new_datas->ServiceRequestData !=  ''){

                $hospital = $new_datas->ServiceRequestData->hospital;
                $department = $new_datas->ServiceRequestData->departmentData;
                $assigned_employee = $new_datas->ServiceRequestData->employeeData;
                $cust_details = $new_datas->ServiceRequestData->customer;

                $request_type = $new_datas->ServiceRequestData->request_type;
                $sub_type = $new_datas->ServiceRequestData->sub_type;
                $request_id = $new_datas->ServiceRequestData->id;

            }elseif(!empty($new_datas->ArchiveServiceRequestData) ||$new_datas->ArchiveServiceRequestData != null || $new_datas->ArchiveServiceRequestData !=  ''){
                $hospital = $new_datas->ArchiveServiceRequestData->hospital;
                $department = $new_datas->ArchiveServiceRequestData->departmentData;
                $assigned_employee = $new_datas->ArchiveServiceRequestData->employeeData;
                $cust_details = $new_datas->ArchiveServiceRequestData->customer;

                $request_type = $new_datas->ArchiveServiceRequestData->request_type;
                $sub_type = $new_datas->ArchiveServiceRequestData->sub_type;
                $request_id = $new_datas->ArchiveServiceRequestData->id;

            }

            if (!isset($assigned_employee->name)) {
                $assign =  "Assigned employee not found for Feedback ID $new_datas->id";
            }else{
                $assign =  $assigned_employee->name." employee assigned for this Feedback ID $new_datas->id";
            }
            $data = [
                'Request_Id' => $request_id ?? '',
                'Request_Type' => $request_type ?? '',
                'Sub_Type' => $sub_type ?? '',
                'Created_At' => $new_datas->created_at ?? '',
                'Response_Speed' => (int)$new_datas->response_speed ?? '',
                'Quality_Of_Response' => (int)$new_datas->quality_of_response ?? '',
                'App_Experience' => (int)$new_datas->app_experience ?? '',
                'Olympus_Staff_Performance' => (int)$new_datas->olympus_staff_performance ?? '',
                'Hospital' => $hospital->hospital_name ?? '',
                'Department' => $department->name ?? '',
                'City' => $hospital->city ?? '',
                'State' => $hospital->state ?? '',
                'First_Name' => $cust_details->first_name ?? '',
                'Last_Name' => $cust_details->last_name ?? '',
                'Assigned Employee Name' => $assign ?? '',
                'Assigned_Engineer' => $assigned_employee->name ?? '',
                'Employee_Code' => $assigned_employee->employee_code ?? '',
                'Responsible_Branch' => $hospital->responsible_branch ?? '',
            ];
            array_push($final_data, $data);
        }

        Excel::create("Feedback Report Export", function ($excel) use ($final_data) {
            $excel->sheet('Sheet1', function ($sheet) use ($final_data) {
                if (count($final_data) != 0) {
                    $sheet->setStyle(array('font' => array('size' => 10)));
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
                    $sheet->setWidth('F', 20);
                    $sheet->getStyle('A1:R1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->export('xls');
    }

    function star($star_count){
        if($star_count == 5 ){
            $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i>";
        }elseif($star_count == 4){
            $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
        }elseif($star_count == 3){
            $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
        }elseif($star_count == 2){
            $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
        }elseif($star_count == 1){
            $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
        }else{
            $quality_of_response =  "<i class='fa fa-fw fa-star' style='color:blue'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i><i class='fa fa-fw fa-star' style='background-color:white'></i>";
        }
        return $quality_of_response;
    }
}
