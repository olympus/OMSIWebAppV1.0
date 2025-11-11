<?php

namespace App\Http\Controllers;

use App\DataTables\InstallationRequestsDataTable;
use Validator;

class InstallationController extends Controller
{
    public function index(InstallationRequestsDataTable $dataTable)
    {
        return $dataTable->render('installation.index',['export_path'=>'all', 'page_name'=>'All']);
    }

    public function indexByType(InstallationRequestsDataTable $dataTable, $type)
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
            case 'received' :
                return $dataTable->render('installation.index',['export_path'=>'received', 'page_name'=>'Received']);
                break;
            case 'assigned' :
                return $dataTable->render('installation.index',['export_path'=>'assigned', 'page_name'=>'Assigned']);
                break;
            case 'attended' :
                return $dataTable->render('installation.index',['export_path'=>'attended', 'page_name'=>'Attended']);
                break;
            case 'installed' :
                return $dataTable->render('installation.index',['export_path'=>'installed', 'page_name'=>'Installed']);
                break;
            case 'closed' :
                return $dataTable->render('installation.index',['export_path'=>'closed', 'page_name'=>'Closed']);
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
                $wherein = ['Received','Assigned','Re-assigned','Attended','Installed','Closed'];
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
            case 'installed' :
                $wherein = ['Installed'];
                $file_name = 'Installed';
                break;
            case 'closed' :
                $wherein = ['Closed'];
                $file_name = 'Closed';
                break;
            
        }
        exportExcel('Installation Requests '.$file_name,'installation',$wherein);
    }
}