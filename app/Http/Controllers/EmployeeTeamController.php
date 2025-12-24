<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTeam;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class EmployeeTeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('employee_team.index');
        //return $dataTable->render('employee_team.index');
    }

    public function employeeList(Request $request)
    {
        try{
            $columns = array(
                0 => 'id',
                1 => 'name',
                2 => 'email',
                3 => 'designation',
                4 => 'employee_code',
                5 => 'disabled',
                6 => 'image',
                7 => 'mobile',
                8 => 'gender',
                9 => 'category',
                10 => 'branch',
                11 => 'zone',
                12 => 'escalation_1',
                13 => 'escalation_2',
                14 => 'escalation_3',
                15 => 'escalation_4',
                16 => 'created_at',
                17 => 'updated_at'
            );

            $totalData = EmployeeTeam::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            //$dir = $request->input('order.0.dir');
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = EmployeeTeam::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('id','name','email','designation','employee_code','disabled','image','mobile','gender','category','branch','zone','escalation_1','escalation_2','escalation_3','escalation_4','created_at', 'updated_at')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  EmployeeTeam::
                select('id','name','email','designation','employee_code','disabled','image','mobile','gender','category','branch','zone','escalation_1','escalation_2','escalation_3','escalation_4','created_at', 'updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('name', 'LIKE',"%{$search}%")
                ->orWhere('email', 'LIKE',"%{$search}%")
                ->orWhere('designation', 'LIKE',"%{$search}%")
                ->orWhere('employee_code', 'LIKE',"%{$search}%")
                ->orWhere('mobile', 'LIKE',"%{$search}%")
                ->orWhere('gender', 'LIKE',"%{$search}%")
                ->orWhere('category', 'LIKE',"%{$search}%")
                ->orWhere('branch', 'LIKE',"%{$search}%")
                ->orWhere('zone', 'LIKE',"%{$search}%")
                ->orWhere('escalation_1', 'LIKE',"%{$search}%")
                ->orWhere('escalation_2', 'LIKE',"%{$search}%")
                ->orWhere('escalation_3', 'LIKE',"%{$search}%")
                ->orWhere('escalation_4', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();

                $totalFiltered = EmployeeTeam::
                select('id','name','email','designation','employee_code','disabled','image','mobile','gender','category','branch','zone','escalation_1','escalation_2','escalation_3','escalation_4','created_at', 'updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('name', 'LIKE',"%{$search}%")
                ->orWhere('email', 'LIKE',"%{$search}%")
                ->orWhere('designation', 'LIKE',"%{$search}%")
                ->orWhere('employee_code', 'LIKE',"%{$search}%")
                ->orWhere('mobile', 'LIKE',"%{$search}%")
                ->orWhere('gender', 'LIKE',"%{$search}%")
                ->orWhere('category', 'LIKE',"%{$search}%")
                ->orWhere('branch', 'LIKE',"%{$search}%")
                ->orWhere('zone', 'LIKE',"%{$search}%")
                ->orWhere('escalation_1', 'LIKE',"%{$search}%")
                ->orWhere('escalation_2', 'LIKE',"%{$search}%")
                ->orWhere('escalation_3', 'LIKE',"%{$search}%")
                ->orWhere('escalation_4', 'LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $post)
                {

                    if($post->disabled == 0){
                        $disabled = 'False';
                    }else{
                        $disabled = 'True';
                    }

                    $url= asset('storage/'.$post->image);
                    $image = '<img src="'.$url.'" border="0" width="40" class="img-rounded" align="center" />';


                    $edit =  route('employee-team.edit',$post->id);

                    $nestedData['id'] = $post->id;
                    $nestedData['name'] = $post->name;
                    $nestedData['email'] = $post->email;
                    $nestedData['designation'] = $post->designation;
                    $nestedData['employee_code'] = $post->employee_code;
                    $nestedData['disabled'] = $disabled ;
                    $nestedData['image'] = $image;
                    $nestedData['mobile'] = $post->mobile;
                    $nestedData['gender'] = $post->gender;
                    $nestedData['category'] = $post->category;
                    $nestedData['branch'] = $post->branch;
                    $nestedData['zone'] = $post->zone;
                    $nestedData['escalation_1'] = $post->escalation_1;
                    $nestedData['escalation_2'] = $post->escalation_2;
                    $nestedData['escalation_3'] = $post->escalation_3;
                    $nestedData['escalation_4'] = $post->escalation_4;
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($post->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($post->updated_at));

                    if(Auth::user()->isA('superadministrator|administrator')){
                        if($disabled == 1){
                            $nestedData['options'] = "
                                <a style='width: 100%;' class='btn btn-xs btn-info' href='{$edit}' title='Edit'>Edit</a>
                                <a style='width: 100%;'
                                 class='btn btn-xs btn-success' href='/admin/employee-team/updatestatus/{$post->id}/0'>
                                    Enable
                                </a>
                            ";
                        }
                        else{
                            $nestedData['options'] = "
                                <a style='width: 100%;' class='btn btn-xs btn-info' href='{$edit}' title='Edit'>Edit</a>
                                <a style='width: 100%;'
                                 class='btn btn-xs btn-danger' href='/admin/employee-team/updatestatus/{$post->id}/1'>
                                    Disable
                                </a>
                            ";
                        }
                    }
                    else{
                        $nestedData['options'] = "";
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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('employee_team.create');
    }

    public function updatestatus($id, $status)
    {
        $validator = Validator::make(
          [
            'id' => $id,
            'status' => $status,
          ],[
            'id' => 'required|numeric',
            'status' => 'required|numeric',
          ]
        );

        if ($validator->fails()) {
            return $validator->messages()->first();
        }
        EmployeeTeam::where('id', $id)->update(['disabled' => $status]);
        $status_text = ($status == 0) ? "Enabled" : "Disabled";
        return redirect('/admin/employee-team')->with('message', "Employee with $id $status_text");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'image' => 'mimes:jpg,jpeg,png|max:2048',
            'name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
            'designation' => 'regex:/^[a-zA-Z0-9\s]*$/',
            'employee_code' => 'regex:/^[a-zA-Z0-9\s][\w\.\-]*$/',
            'mobile' => 'required|regex:/[0-9]{10}/|max:13',
            'email' => 'required|email|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            'gender' => 'bail|regex:/^[a-zA-Z\s]*$/',
            'category' => 'bail|regex:/^[a-zA-Z0-9\s]*$/',
            'branch' => 'bail|regex:/^[a-zA-Z0-9\s]*$/',
            'zone' => 'bail|regex:/^[a-zA-Z\s]*$/',
            // 'escalation_1' => 'regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            // 'escalation_2' => 'regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            // 'escalation_3' => 'regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            // 'escalation_4' => 'regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
        ]);
        $employeeteam = new EmployeeTeam;
        $employeeteam->name  = $request->name;
        $employeeteam->email  = $request->email;
        $employeeteam->designation  = $request->designation;
        $employeeteam->employee_code  = $request->employee_code;
        $employeeteam->mobile  = $request->mobile;
        $employeeteam->gender  = $request->gender;
        $employeeteam->category  = $request->category;
        $employeeteam->branch  = $request->branch;
        $employeeteam->zone = $request->zone;
        $employeeteam->escalation_1 = $request->escalation_1;
        $employeeteam->escalation_2 = $request->escalation_2;
        $employeeteam->escalation_3 = $request->escalation_3;
        $employeeteam->escalation_4 = $request->escalation_4;

        if(!is_null($request->file('image'))){
            $frontimage = $request->file('image');
            $fimage_fileName = $employeeteam->employee_code.'.'.$frontimage->getClientOriginalExtension();
            $frontimage->move(storage_path('app/public/employeeimages'), $fimage_fileName);
            $fimage_fileSavePath = 'employeeimages/'.$fimage_fileName;
            $employeeteam->image = $fimage_fileSavePath;
        }else{
            $employeeteam->image = 'shared/employee_image.jpg';
        }
        $employeeteam->save();
        return redirect('/admin/employee-team')->with('message', "Team Member $employeeteam->id successfully created");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EmployeeTeam $employeeTeam
     * @return Response
     */
    public function edit($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|numeric',
          ]
        );
        if ($validator->fails()) {
            return $validator->messages()->first();
        }
        $employeeteam =  EmployeeTeam::findOrFail($id);
        return view('employee_team.edit', ['employeeteam'=>$employeeteam]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param EmployeeTeam $employeeTeam
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'image' => 'mimes:jpg,jpeg,png|max:2048',
            'name' => 'bail|required|regex:/^[a-zA-Z\s]*$/',
            'designation' => 'regex:/^[a-zA-Z0-9\s]*$/',
            'employee_code' => 'regex:/^[a-zA-Z0-9\s][\w\.\-]*$/',
            'mobile' => 'required|regex:/[0-9]{10}/|max:13',
            'email' => 'required|email|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            'gender' => 'bail|regex:/^[a-zA-Z\s]*$/',
            'category' => 'bail|regex:/^[a-zA-Z0-9\s]*$/',
            'branch' => 'bail|regex:/^[a-zA-Z0-9\s]*$/',
            'zone' => 'bail|regex:/^[a-zA-Z\s]*$/',
            // 'escalation_1' => 'regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            // 'escalation_2' => 'regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            // 'escalation_3' => 'regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
            // 'escalation_4' => 'regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i',
        ]);
        $employeeteam = EmployeeTeam::findOrFail($id);
        $employeeteam->name  = $request->name;
        $employeeteam->email  = $request->email;
        $employeeteam->designation  = $request->designation;
        $employeeteam->employee_code  = $request->employee_code;
        $employeeteam->mobile  = $request->mobile;
        $employeeteam->gender  = $request->gender;
        $employeeteam->category  = $request->category;
        $employeeteam->branch  = $request->branch;
        $employeeteam->zone = $request->zone;
        $employeeteam->escalation_1 = $request->escalation_1;
        $employeeteam->escalation_2 = $request->escalation_2;
        $employeeteam->escalation_3 = $request->escalation_3;
        $employeeteam->escalation_4 = $request->escalation_4;

        if(!is_null($request->file('image'))){
            $files = glob(storage_path('app/public/employeeimages/'.$employeeteam->employee_code.'.*'));
            /*foreach ($files as $file) { unlink($file); }*/

            $frontimage = $request->file('image');
            $fimage_fileName = $employeeteam->employee_code.'.'.$frontimage->getClientOriginalExtension();
            $frontimage->move(storage_path('app/public/employeeimages'), $fimage_fileName);
            $fimage_fileSavePath = 'employeeimages/'.$fimage_fileName;
            $employeeteam->image = $fimage_fileSavePath;
        }elseif(is_null($employeeteam->image)){
            $employeeteam->image = 'shared/employee_image.jpg';
        }
        $employeeteam->save();
        return redirect('/admin/employee-team')->with('message', "Team Member $id successfully updated");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param EmployeeTeam $employeeTeam
     * @return Response
     */
    public function destroy($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|numeric',
          ]
        );
        if ($validator->fails()) {
            return $validator->messages()->first();
        }
        EmployeeTeam::findOrFail($id)->delete();
        return redirect('/admin/employee-team')->with('message', 'Team Member details successfully deleted');
    }

    public function deletes($id)
    {
        $validator = Validator::make(
          [
            'id' => $id,
          ],[
            'id' => 'required|numeric',
          ]
        );
        if ($validator->fails()) {
            return $validator->messages()->first();
        }
        EmployeeTeam::findOrFail($id)->delete();
        return redirect('/admin/employee-team')->with('message', "Team Member details successfully deleted");
    }
}
