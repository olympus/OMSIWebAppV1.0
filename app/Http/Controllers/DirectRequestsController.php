<?php

namespace App\Http\Controllers;

use App\DataTables\DirectRequestsDataTable;
use App\Models\DirectRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class DirectRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(DirectRequestsDataTable $dataTable)
    {
        return $dataTable->render('directrequests.index');
    }

    public function directrequestsList(Request $request)
    {
        try{
            $columns = array(
                0 => 'id',
                1 => 'sap_id',
                2 => 'status',
                3 => 'fse_code',
                4 => 'customer_name',
                5 => 'customer_code',
                6 => 'customer_city',
                7 => 'customer_state',
                8 => 'prod_model_no',
                9 => 'prod_material',
                10 => 'prod_serial_no',
                11 => 'prod_equipment_no',
                12 => 'prod_material_description',
                13 => 'sort_field',
                14 => 'contract_desc',
                15 => 'branch',
                16 => 'zone',
                17 => 'created_at',
                18 => 'updated_at',
            );

            $totalData = DirectRequest::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            //$dir = $request->input('order.0.dir');
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = DirectRequest::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('*')
                ->get();
            }else{
                $search = $request->input('search.value');
                $posts =  DirectRequest::
                select('*')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('sap_id', 'LIKE',"%{$search}%")
                ->orWhere('status', 'LIKE',"%{$search}%")
                ->orWhere('fse_code', 'LIKE',"%{$search}%")
                ->orWhere('customer_name', 'LIKE',"%{$search}%")
                ->orWhere('customer_code', 'LIKE',"%{$search}%")
                ->orWhere('customer_city', 'LIKE',"%{$search}%")
                ->orWhere('customer_state', 'LIKE',"%{$search}%")
                ->orWhere('prod_model_no', 'LIKE',"%{$search}%")
                ->orWhere('prod_material', 'LIKE',"%{$search}%")
                ->orWhere('prod_serial_no', 'LIKE',"%{$search}%")
                ->orWhere('prod_equipment_no', 'LIKE',"%{$search}%")
                ->orWhere('prod_material_description', 'LIKE',"%{$search}%")
                ->orWhere('sort_field', 'LIKE',"%{$search}%")
                ->orWhere('contract_desc', 'LIKE',"%{$search}%")
                ->orWhere('branch', 'LIKE',"%{$search}%")
                ->orWhere('zone', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();

                $totalFiltered = DirectRequest::
                select('*')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('sap_id', 'LIKE',"%{$search}%")
                ->orWhere('status', 'LIKE',"%{$search}%")
                ->orWhere('fse_code', 'LIKE',"%{$search}%")
                ->orWhere('customer_name', 'LIKE',"%{$search}%")
                ->orWhere('customer_code', 'LIKE',"%{$search}%")
                ->orWhere('customer_city', 'LIKE',"%{$search}%")
                ->orWhere('customer_state', 'LIKE',"%{$search}%")
                ->orWhere('prod_model_no', 'LIKE',"%{$search}%")
                ->orWhere('prod_material', 'LIKE',"%{$search}%")
                ->orWhere('prod_serial_no', 'LIKE',"%{$search}%")
                ->orWhere('prod_equipment_no', 'LIKE',"%{$search}%")
                ->orWhere('prod_material_description', 'LIKE',"%{$search}%")
                ->orWhere('sort_field', 'LIKE',"%{$search}%")
                ->orWhere('contract_desc', 'LIKE',"%{$search}%")
                ->orWhere('branch', 'LIKE',"%{$search}%")
                ->orWhere('zone', 'LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $post)
                {
                    $show =  route('directrequests.show',$post->id);
                    $nestedData['id'] = $post->id;
                    $nestedData['sap_id'] = $post->sap_id;
                    $nestedData['status'] = $post->status;
                    $nestedData['fse_code'] = $post->fse_code;
                    $nestedData['customer_name'] = $post->customer_name;
                    $nestedData['customer_code'] = $post->customer_code;
                    $nestedData['customer_city'] = $post->customer_city;
                    $nestedData['customer_state'] = $post->customer_state;
                    $nestedData['prod_model_no'] = $post->prod_model_no;
                    $nestedData['prod_material'] = $post->prod_material;
                    $nestedData['prod_serial_no'] = $post->prod_serial_no;
                    $nestedData['prod_equipment_no'] = $post->prod_equipment_no;
                    $nestedData['prod_material_description'] = $post->prod_material_description;
                    $nestedData['sort_field'] = $post->sort_field;
                    $nestedData['contract_desc'] = $post->contract_desc;
                    $nestedData['branch'] = $post->branch;
                    $nestedData['zone'] = $post->zone;
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($post->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($post->updated_at));
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
        $request = DirectRequest::findOrFail($id);
        return view('directrequests.show', ['request'=>$request]);
    }
}
