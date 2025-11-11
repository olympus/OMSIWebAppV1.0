<?php

namespace App\Http\Controllers;

use App\DataTables\HospitalsDataTable;
use App\Models\Departments;
use App\Models\Hospitals;
use Auth;
use DB;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class HospitalsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(HospitalsDataTable $dataTable)
    {
        return $dataTable->render('hospitals.index');
    }
    public function index1()
    {
        return view('hospitals.index');
        //return $dataTable->render('hospitals.index');
    }

    public function hospitalList(Request $request)
    {
        try{
            $columns = array(
                0 => 'id',
                1 => 'hospital_name',
                2 => 'dept_id',
                3 => 'address',
                4 => 'city',
                5 => 'state',
                6 => 'zip',
                7 => 'country',
                8 => 'customer_id',
                9 => 'created_at',
                10 => 'updated_at',
            );

            $totalData = Hospitals::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = Hospitals::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('id','hospital_name','dept_id','address','city','state','zip','country', 'customer_id', 'created_at','updated_at')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  Hospitals::
                select('id','hospital_name','dept_id','address','city','state','zip','country', 'customer_id', 'created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('hospital_name', 'LIKE',"%{$search}%")
                ->orWhere('dept_id', 'LIKE',"%{$search}%")
                ->orWhere('address', 'LIKE',"%{$search}%")
                ->orWhere('city', 'LIKE',"%{$search}%")
                ->orWhere('state', 'LIKE',"%{$search}%")
                ->orWhere('zip', 'LIKE',"%{$search}%")
                ->orWhere('country', 'LIKE',"%{$search}%")
                ->orWhere('customer_id', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();

                $totalFiltered = Hospitals::
                select('id','hospital_name','dept_id','address','city','state','zip','country', 'customer_id', 'created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('hospital_name', 'LIKE',"%{$search}%")
                ->orWhere('dept_id', 'LIKE',"%{$search}%")
                ->orWhere('address', 'LIKE',"%{$search}%")
                ->orWhere('city', 'LIKE',"%{$search}%")
                ->orWhere('state', 'LIKE',"%{$search}%")
                ->orWhere('zip', 'LIKE',"%{$search}%")
                ->orWhere('country', 'LIKE',"%{$search}%")
                ->orWhere('customer_id', 'LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $current_user) {

                    $nestedData['id'] = $current_user->id;
                    $nestedData['hospital_name'] = $current_user->hospital_name;
                    $nestedData['dept_id'] = $current_user->dept_id;
                    $nestedData['address'] = $current_user->address;
                    $nestedData['city'] = $current_user->city;
                    $nestedData['state'] = $current_user->state;
                    $nestedData['zip'] = $current_user->zip;
                    $nestedData['country'] = $current_user->country;
                    $nestedData['customer_id'] = $current_user->customer_id;
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($current_user->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($current_user->updated_at));

                    if(Auth::user()->isA('superadministrator|administrator')){
                        $nestedData['options'] = " <a style='width: 100%;'
                             class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure you want to delete`)' href='hospitals/deletes/{$current_user->id}'>
                                Delete
                            </a>
                        ";
                    }else{
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
        return view('hospitals.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //Create new service team member
        $hospital = new Hospitals;
        $hospital->name  = $request->name;
        $hospital->email  = $request->email;
        $hospital->designation  = $request->designation;
        $hospital->employee_code  = $request->employee_code;
        $hospital->mobile  = $request->mobile;
        $hospital->category  = $request->category;
        $hospital->branch  = $request->branch;
        $hospital->zone = $request->zone;

        $hospital->save();

        return redirect('/admin/hospitals')->with('message', 'New team member created');
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
            'id' => 'required|numeric',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $hospital = Hospitals::findOrFail($id);
        return view('hospitals.show', ['hospital'=>$hospital]);
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
            'id' => 'required|numeric',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $hospital =  Hospitals::findOrFail($id);
        return view('hospitals.edit', ['hospital'=>$hospital]);
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
        $hospital = Hospitals::findOrFail($id);
        $hospital->name  = $request->name;
        $hospital->email  = $request->email;
        $hospital->designation  = $request->designation;
        $hospital->employee_code  = $request->employee_code;
        $hospital->mobile  = $request->mobile;
        $hospital->category  = $request->category;
        $hospital->branch  = $request->branch;
        $hospital->zone = $request->zone;

        $hospital->save();
        return redirect('/admin/hospitals')->with('message', 'Service Member details updated');
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
            'id' => 'required|numeric',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        Hospitals::findOrFail($id)->delete();
        return redirect('/admin/hospitals')->with('message', 'Service Member details deleted');
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
            return  $validator->messages()->first();
        }
        Hospitals::findOrFail($id)->delete();
        return redirect('/admin/hospitals')->with('message', 'Service Member details deleted');
    }

    public function export()
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        $new_data = Hospitals::select(
            'id',
            'hospital_name',
            'dept_id',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'responsible_branch',
            'customer_id',
            'created_at',
            'updated_at'
        )
        ->orderBy('created_at', 'DESC')
        ->get();
        $final_data = [];
        $new_dept = [];
        foreach ($new_data as $new_datas) {
            $dept = explode(',', $new_datas->dept_id);
            $get_Dept_name = Departments::whereIn('id', $dept)->get()->pluck('name')->toArray();
            $get_Dept = implode(',', $get_Dept_name);
            $data = [
                'ID' => $new_datas->id ?? '',
                'Hospital Name' => $new_datas->hospital_name ?? '',
                'Dept ID' => $new_datas->dept_id ?? '',
                'Dept Name' => $get_Dept ?? '',
                'Address' => $new_datas->address ?? '',
                'City' => $new_datas->city ?? '',
                'State' => $new_datas->state ?? '',
                'Zip' => $new_datas->zip ?? '',
                'Country' => $new_datas->country ?? '',
                'Responsible Branch' => $new_datas->responsible_branch ?? '',
                'Customer Id' => $new_datas->customer_id ?? '',
                'Created_At' => $new_datas->created_at ?? '',
                'Updated' => $new_datas->updated_at ?? '',
            ];
            array_push($final_data, $data);
        }

        Excel::create("Hospital Report Export", function ($excel) use ($final_data) {

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
                    $sheet->setCellValue('A1', 'No hospital to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->export('xls');
    }
}
