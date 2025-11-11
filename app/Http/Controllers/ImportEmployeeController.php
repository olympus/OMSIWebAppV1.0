<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTeam;
use Log;

class ImportEmployeeController extends Controller
{
    public function importEmployee(){
        $filepath = storage_path('employee.csv');
        $csv_data = $this->csv2array($filepath);
        $table_data = [];

        foreach ($csv_data as $csv_datas) {
            EmployeeTeam::where('id', $csv_datas['id'])->update([
                'designation' => $csv_datas['designation'],
                'category' => $csv_datas['category'],
                'branch' => $csv_datas['branch'],
                'zone' => $csv_datas['zone'],
                'escalation_1' => $csv_datas['escalation_1'],
                'escalation_2' => $csv_datas['escalation_2'],
                'escalation_3' => $csv_datas['escalation_3'],
                'escalation_4' => $csv_datas['escalation_4']
            ]);
        }
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
                if ( $row != 1 && !empty($data) || ( $row == 2 && !empty($data) )) {
                    $request_ids = explode(",", $data[0]);
                    $row_data = [];
                    $row_data['id']=$request_ids[0];
                    if($request_ids[1] != NULL || $request_ids[1] != ""){
                        $row_data['designation']=$request_ids[1];
                    }else{
                        $row_data['designation'] = NULL;
                    }
                    if($request_ids[2] != "NULL" || $request_ids[2] != ""){
                        $row_data['category']=$request_ids[2];
                    }else{
                        $row_data['category'] = NULL;
                    }
                    if($request_ids[3] != "NULL" || $request_ids[3] != ""){
                        $row_data['branch']=$request_ids[3];
                    }else{
                        $row_data['branch'] = NULL;
                    }
                    if($request_ids[4] != "NULL" || $request_ids[4] != ""){
                        $row_data['zone']=$request_ids[4];
                    }else{
                        $row_data['zone'] = NULL;
                    }
                    if($request_ids[5] != "NULL" || $request_ids[5] != ""){
                        $row_data['escalation_1']=$request_ids[5];
                    }else{
                        $row_data['escalation_1'] = NULL;
                    }
                    if($request_ids[6] != "NULL" || $request_ids[6] != ""){
                        $row_data['escalation_2']=$request_ids[6];
                    }else{
                        $row_data['escalation_2'] = NULL;
                    }
                    if($request_ids[7] != "NULL" || $request_ids[7] != ""){
                        $row_data['escalation_3']=$request_ids[7];
                    }else{
                        $row_data['escalation_3'] = NULL;
                    }
                    if($request_ids[8] != "NULL" || $request_ids[8] != ""){
                        $row_data['escalation_4']=$request_ids[8];
                    }else{
                        $row_data['escalation_4'] = NULL;
                    }
                    array_push($csv_data, $row_data);
                }
                $row++;
            }
            fclose($handle);
        }
        return $csv_data;
    }
}
