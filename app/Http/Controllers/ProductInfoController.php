<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\ProductInfo;
use App\DataTables\ProductInfoDataTable;
use Validator;
class ProductInfoController extends Controller
{
    public function index1(ProductInfoDataTable $dataTable)
    {
        return $dataTable->render('productinfo.index');
    }

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
        $product = ProductInfo::findOrFail($id);
        return view('productinfo.show', ['product'=>$product]);
    }

    public function index()
    {   
        return view('productinfo.index'); 
    }

    public function productList(Request $request)
    {   
        try{
            $columns = array( 
                0 => 'id', 
                1 => 'service_requests_id',
                2 => 'pd_name',
                3 => 'pd_serial',
                4 => 'pd_description', 
                15 => 'created_at',
                16 => 'updated_at',  
            );
             
            $totalData = ProductInfo::count();

            $totalFiltered = $totalData; 

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = "desc";

            if(empty($request->input('search.value')))
            {            
                $posts = ProductInfo::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('*') 
                ->get();
            }
            else{
                $search = $request->input('search.value');  
                $posts =  ProductInfo::  
                select('*')  
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('service_requests_id', 'LIKE',"%{$search}%")
                ->orWhere('pd_name', 'LIKE',"%{$search}%")
                ->orWhere('pd_serial', 'LIKE',"%{$search}%")
                ->orWhere('pd_description', 'LIKE',"%{$search}%") 
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();

                $totalFiltered = ProductInfo:: 
                select('*')  
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('service_requests_id', 'LIKE',"%{$search}%")
                ->orWhere('pd_name', 'LIKE',"%{$search}%")
                ->orWhere('pd_serial', 'LIKE',"%{$search}%")
                ->orWhere('pd_description', 'LIKE',"%{$search}%") 
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {   
                foreach ($posts as $product_info) {  
                    $show =  url('admin/product_info',$product_info->id);  
                    $nestedData['id'] = $product_info->id; 
                    $nestedData['service_requests_id'] = $product_info->service_requests_id; 
                    $nestedData['pd_name'] = $product_info->pd_name;
                    $nestedData['pd_serial'] = $product_info->pd_serial;
                    $nestedData['pd_description'] = $product_info->pd_description; 
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($product_info->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($product_info->updated_at)); 
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

}
