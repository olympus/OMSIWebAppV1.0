<?php

namespace App\Charts;

use App\Models\Departments;
use App\Models\Customers;
use App\Models\DirectRequest;
use App\Models\Feedback;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\StatusTimeline as Timeline;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Facades\Cache;

class GetCharts
{
    // public static function get_month_diff($start, $end = FALSE)
    // {
    //     // dd($start,$end);
    //     $end OR $end = time();
    //     $start = new DateTime("@$start");
    //     $end   = new DateTime("@$end");
    //     $diff  = $start->diff($end);
    //     return $diff->format('%y') * 12 + $diff->format('%m');
    // }

    public static function home($daterange)
    {
        $date_from = new Carbon(explode("_", $daterange)[0]);
        $date_to = new Carbon(explode("_", $daterange)[1]);
        $datet1 = explode("-", explode("_", $daterange)[0]);
        $datet2 = explode("-", explode("_", $daterange)[1]);
        $filterdate = $datet1[2].'-'.$datet1[1].'-'.$datet1[0].'_'.$datet2[2].'-'.$datet2[1].'-'.$datet2[0];

        $direct_requests = DirectRequest::select('id', 'zone', 'created_at')
            ->whereBetween('created_at', [$date_from, $date_to])
            ->get();

        $services_obj = ServiceRequests::where('is_practice', false)
            ->whereBetween('service_requests.created_at', [$date_from, $date_to])
            ->with('hospital')
            ->get();

        foreach ($services_obj as $key) {
            //$hospital_state = Hospitals::where('id', $key->hospital_id)->value('state');
            $service_region = find_region($key->hospital->state);
            $key->region = $service_region;
        }

        $customers_obj = Customers::whereBetween('created_at', [$date_from, $date_to])->where('email', 'NOT LIKE', '%olympus-ap.com%')->get();
        $customers_obj_region = array();
        foreach ($customers_obj as $customer) {
            foreach (explode(",", $customer->hospital_id) as $key) {
                $hospital_state = Hospitals::where('id', $key)->value('state');
                $service_region = find_region($hospital_state);
                $temp_arr = $customer;
                $temp_arr->region = $service_region;
                array_push($customers_obj_region, $temp_arr);
            }
            $customer->region = $service_region;
        }

        $dept_obj = Departments::get();
        $period = CarbonPeriod::create($date_from->firstOfMonth(), '1 month', $date_to);
        foreach ($period as $dt) {
            $chart_months[] = $dt->format("M-y");
            $months12[] = $dt->format("m");
            $year = $dt->format("Y");
            $year_of = ($dt->format("n") > 3) ? $year : $year-1;
            $years[$year_of][] = $dt->format("M-y");
        }
        $chart_months = array_reverse($chart_months);
        $months12 = array_reverse($months12);
        $months_count = count($months12);
        $date_to1 = new Carbon(explode("_", $daterange)[1]);
        $filtereddata =  [
            'filterdate'=>$filterdate,
            'chart_months'=>$chart_months,
            'chart56'=>GetCharts::chart56($daterange, $services_obj, $months12, $chart_months, $date_to1),
            'chart63'=>GetCharts::chart63($daterange,$customers_obj, $months12, $chart_months, $date_to1),
            'chart71'=>GetCharts::chart71($daterange, $services_obj, $dept_obj),
            'chart72'=>GetCharts::chart72($daterange, $services_obj),
            'chart81'=>GetCharts::chart81_combined($daterange, $direct_requests, $services_obj, $months12, $chart_months, $date_to1),
            'chart83'=>GetCharts::chart83($daterange, $direct_requests, $months12, $chart_months, $date_to1),
            'chart91'=>GetCharts::chart91($services_obj),
            'chart92'=>GetCharts::chart92($daterange, $services_obj, $months12, $chart_months, $date_to1, $years),
            'chart93'=>GetCharts::chart93($daterange, $services_obj, $months12, $chart_months, $date_to1),
        ];
        return $filtereddata;
    }

    public static function all_india($daterange)
    {
        // $time_start = microtime(true);

        $date_from = new Carbon(explode("_", $daterange)[0]);
        $date_to = new Carbon(explode("_", $daterange)[1]);
        $date_to1 = new Carbon(explode("_", $daterange)[1]);

        $datet1 = explode("-", explode("_", $daterange)[0]);
        $datet2 = explode("-", explode("_", $daterange)[1]);
        $filterdate = $datet1[2].'-'.$datet1[1].'-'.$datet1[0].'_'.$datet2[2].'-'.$datet2[1].'-'.$datet2[0];

        if (Cache::has("home_charts_$daterange"."_all_india")) {
            return Cache::get("home_charts_$daterange"."_all_india");
        } else {
            // Get data from all tables and add region to them
            $indian_all_states  = \Config('oly.indian_all_states');

            $services_obj = ServiceRequests::where('service_requests.is_practice', false)
            ->whereBetween('service_requests.created_at', [$date_from, $date_to])
            ->get();

            foreach ($services_obj as $key) {
                $hospital_state = Hospitals::where('id', $key->hospital_id)->value('state');
                $service_region = find_region($hospital_state);
                $key->region = $service_region;
            }

            $direct_requests = DirectRequest::select('id', 'zone', 'created_at')
                ->whereBetween('direct_requests.created_at', [$date_from, $date_to])
                ->get();

            $customers_obj = Customers::whereBetween('created_at', [$date_from, $date_to])->get();

            $customers_obj_region = array();
            foreach ($customers_obj as $customer) {
                foreach (explode(",", $customer->hospital_id) as $key) {
                    $hospital_state = Hospitals::where('id', $key)->value('state');
                    $service_region = find_region($hospital_state);
                    $temp_arr = $customer;
                    $temp_arr->region = $service_region;
                    array_push($customers_obj_region, $temp_arr);
                }
                $customer->region = $service_region;
            }

            $dept_obj = Departments::get();

            $period = CarbonPeriod::create($date_from->firstOfMonth(), '1 month', $date_to);
            foreach ($period as $dt) {
                    $chart_months[] = $dt->format("M-y");
                    $months12[] = $dt->format("m");
            }
            $chart_months = array_reverse($chart_months);
            $months12 = array_reverse($months12);
            $months_count = count($months12);

            $chart5_6Data=GetCharts::chart5_6Data($daterange, $date_from, $date_to1, $indian_all_states);
            $customers_obj_freq=GetCharts::customers_obj_freqData($daterange, $date_from, $date_to1);
            // Chart 23 24 25 data

            // echo '<b>DateRange:</b> '.$daterange.'<br>';
            // echo '<b>Direct Requests: </b> '.count($direct_requests).'<br>';
            // echo '<b>Requests: </b> '.count($services_obj).'<br>';
            // echo '<b>Customers: </b> '.count($customers_obj).'<br>';

            $filtereddata =  [
                'filterdate'=>$filterdate,
                'chart_months'=>$chart_months,
                'chart1'=>GetCharts::chart1($daterange, $services_obj),
                'chart2'=>GetCharts::chart2($daterange, $services_obj),
                'chart3'=>GetCharts::chart3($daterange, $services_obj),
                'chart4'=>GetCharts::chart4($daterange, $services_obj),
                'chart5'=>GetCharts::chart5($chart5_6Data),
                'chart6'=>GetCharts::chart6($chart5_6Data),
                'chart7'=>GetCharts::chart7($daterange, $services_obj),
                'chart8'=>GetCharts::chart8($daterange, $services_obj),
                'chart9'=>GetCharts::chart9($daterange, $date_from, $date_to1, $services_obj),
                'chart23'=>GetCharts::chart23($daterange, $customers_obj_freq),
                'chart24'=>GetCharts::chart24($daterange, $customers_obj_freq),
                'chart25'=>GetCharts::chart25($daterange, $customers_obj_freq),
                'chart53'=>GetCharts::chart53($daterange, $services_obj),
                'chart54'=>GetCharts::chart54($daterange, $services_obj, $months12, $chart_months, $date_to1),
                'chart55'=>GetCharts::chart55($daterange, $services_obj, $months12, $chart_months, $date_to1, $dept_obj),
                'chart56'=>GetCharts::chart56($daterange, $services_obj, $months12, $chart_months, $date_to1),
                'chart59'=>GetCharts::chart59($daterange, $services_obj),
                'chart60'=>GetCharts::chart60($daterange, $date_from, $date_to1, $customers_obj),
                'chart61'=>GetCharts::chart61($daterange, $customers_obj),
                'chart62'=>GetCharts::chart62($daterange, $customers_obj, $months12, $chart_months, $date_to1),
                'chart68'=>GetCharts::chart68($daterange, $services_obj),
                'chart70'=>GetCharts::chart70($daterange, $services_obj),
                'chart73'=>GetCharts::chart73($daterange, $services_obj),
                'chart74'=>GetCharts::chart74($daterange, $services_obj),
                'chart63'=>GetCharts::chart63($daterange,$customers_obj,$months12, $chart_months, $date_to1),
                'chart71'=>GetCharts::chart71($daterange, $services_obj, $dept_obj),
                'chart72'=>GetCharts::chart72($daterange, $services_obj),
                'chart28'=>GetCharts::chart28($daterange,$customers_obj),
                'chart81'=>GetCharts::chart81_combined($daterange, $direct_requests, $services_obj, $months12, $chart_months, $date_to1),
                // 'chart82'=>GetCharts::chart82($daterange, $direct_requests, $months12, $chart_months, $date_to1),
                // 'chart83'=>GetCharts::chart83($daterange, $direct_requests, $months12, $chart_months, $date_to1),
            ];

            // $time_end = microtime(true);
            // $execution_time = $time_end - $time_start;
            // echo '<b>Total Execution Time:</b> '.$execution_time.' Seconds<br>';
            // dd($months12,$filtereddata['chart81']);
            Cache::put("home_charts_$daterange"."_all_india", $filtereddata, 15);
            return $filtereddata;
        }

    }











// ===Charts Start Here===

    public static function chart5_6Data($daterange, $date_from, $date_to, $indian_all_states){
        if (Cache::has("home_charts_$daterange"."_chart5_6")) {
            return Cache::get("home_charts_$daterange"."_chart5_6");
        } else {
            $Hospitals=Hospitals::whereBetween('created_at', [$date_from, $date_to])->whereIn('customer_id', Customers::select('id')->get()->toArray())->select('id', 'dept_id', 'state')->get()->all();
            $Departments = $dept_north = $dept_east = $dept_south = $dept_west = [];
            $count = 1;
            foreach ($Hospitals as $Hospital) {
                if (in_array($Hospital['state'], $indian_all_states['north'])) {
                    foreach (explode(',', str_replace(' ', '', $Hospital['dept_id'])) as $Dept) {
                        array_push($dept_north, $Dept);
                    }
                } elseif (in_array($Hospital['state'], $indian_all_states['east'])) {
                    foreach (explode(',', str_replace(' ', '', $Hospital['dept_id'])) as $Dept) {
                        array_push($dept_east, $Dept);
                    }
                } elseif (in_array($Hospital['state'], $indian_all_states['south'])) {
                    foreach (explode(',', str_replace(' ', '', $Hospital['dept_id'])) as $Dept) {
                        array_push($dept_south, $Dept);
                    }
                } elseif (in_array($Hospital['state'], $indian_all_states['west'])) {
                    foreach (explode(',', str_replace(' ', '', $Hospital['dept_id'])) as $Dept) {
                        array_push($dept_west, $Dept);
                    }
                }
            }
            $dept_all = [$dept_north, $dept_east, $dept_south, $dept_west];
            $chart5_6_north = array_count_values($dept_north);
            $chart5_6_east = array_count_values($dept_east);
            $chart5_6_south = array_count_values($dept_south);
            $chart5_6_west = array_count_values($dept_west);
            for ($i=1; $i <= 8; $i++) {
                if (!array_key_exists($i, $chart5_6_north)) { $chart5_6_north[$i] = 0; }
                if (!array_key_exists($i, $chart5_6_east)) { $chart5_6_east[$i] = 0; }
                if (!array_key_exists($i, $chart5_6_south)) { $chart5_6_south[$i] = 0; }
                if (!array_key_exists($i, $chart5_6_west)) { $chart5_6_west[$i] = 0; }
            }
            $chart5_6Data['north'] = $chart5_6_north;
            $chart5_6Data['east'] = $chart5_6_east;
            $chart5_6Data['south'] = $chart5_6_south;
            $chart5_6Data['west'] = $chart5_6_west;
            Cache::put("home_charts_$daterange"."_chart5_6", $chart5_6Data, 20);
            return $chart5_6Data;
        }
    }

    public static function customers_obj_freqData($daterange, $date_from, $date_to){
        if (Cache::has("home_charts_$daterange"."_customers_obj_freq")) {
            return Cache::get("home_charts_$daterange"."_customers_obj_freq");
        } else {
            $customers_obj_freq = Customers::get();
            $customers_freq = array();
            foreach ($customers_obj_freq as $customer) {
                foreach (explode(",", $customer->hospital_id) as $key) {
                    $hospital_state = Hospitals::where('id', $key)->value('state');
                    $service_region = find_region($hospital_state);
                    $temp_arr = $customer;
                    $temp_arr->region = $service_region;
                    array_push($customers_freq, $temp_arr);
                }
                $customer->region = $service_region;

                $cust_req = ServiceRequests::where('customer_id', ltrim($customer->customer_id, '0'))->get();

                $count_service = $count_enquiry = $count_academic = 0;
                foreach ($cust_req as $service) {
                    if ($service->request_type == 'service') {
                        $count_service++;
                    }
                    if ($service->request_type == 'enquiry') {
                        $count_enquiry++;
                    }
                    if ($service->request_type == 'academic') {
                        $count_academic++;
                    }
                }
                $customer->req_count_service = $count_service;
                $customer->req_count_enquiry = $count_enquiry;
                $customer->req_count_academic = $count_academic;
            }
            Cache::put("home_charts_$daterange"."_customers_obj_freq", $customers_obj_freq, 20);
            return $customers_obj_freq;
        }
    }

    public static function chart1($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart1")) {
            return Cache::get("home_charts_$daterange"."_chart1");
        } else {
            $serreq_count = $services_obj->where('request_type', 'service')->count();
            $enqreq_count = $services_obj->where('request_type', 'enquiry')->count();
            $acadreq_count = $services_obj->where('request_type', 'academic')->count();
            $chart1 = [
                'Service'=>$serreq_count,
                'Enquiry'=>$enqreq_count,
                'Academic'=>$acadreq_count
            ];
            Cache::put("home_charts_$daterange"."_chart1", $chart1, 20);
            return $chart1;
        }
    }

    public static function chart2($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart2")) {
            return Cache::get("home_charts_$daterange"."_chart2");
        } else {
            $chart2 = [];
            for ($dept=1; $dept <= 8; $dept++) {
                $chart2["Service"][] = $services_obj->where('request_type', 'service')->where('dept_id', $dept)->count();
                $chart2["Enquiry"][] = $services_obj->where('request_type', 'enquiry')->where('dept_id', $dept)->count();
                $chart2["Academic"][] = $services_obj->where('request_type', 'academic')->where('dept_id', $dept)->count();
            }
        Cache::put("home_charts_$daterange"."_chart2", $chart2, 20);
        return $chart2;
        }
    }

    public static function chart3($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart3")) {
            return Cache::get("home_charts_$daterange"."_chart3");
        } else {
            $chart3 = [];
            $chart3['Closed'] = $chart3['Under_Repair'] = $chart3['Assigned'] = $chart3['Received'] = $chart3['Attended'] = 0;
            foreach ($services_obj as $service) {
                if ($service->status == 'Closed') { ++$chart3['Closed']; }
                else if ( in_array($service->status, ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched']) ) { ++$chart3['Under_Repair']; }
                else if (in_array($service->status, ['Assigned','Re-assigned'])) { ++$chart3['Assigned']; }
                else if ($service->status == 'Attended') { ++$chart3['Attended']; }
                if ($service->status == 'Received') { ++$chart3['Received']; }
                else{ }
            }
            Cache::put("home_charts_$daterange"."_chart3", $chart3, 20);
            return $chart3;
        }
    }

    public static function chart4($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart4")) {
            return Cache::get("home_charts_$daterange"."_chart4");
        } else {
            for ($dept=1; $dept <= 8; $dept++) {
                $chart4["Received"][] = $services_obj->where('status', 'Received')->where('dept_id', $dept)->count();
                $chart4["Assigned"][] = $services_obj->whereIn('status', ['Assigned','Re-assigned','Attended'])->where('dept_id', $dept)->count();
                $chart4["Under_Repair"][] = $services_obj->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->where('dept_id', $dept)->count();
                $chart4["Closed"][] = $services_obj->where('status', 'Closed')->where('dept_id', $dept)->count();
            }
        Cache::put("home_charts_$daterange"."_chart4", $chart4, 20);
        return $chart4;
        }
    }

    public static function chart5($chart5_6Data){
        $chart5 = [];
        for ($i=1; $i <= 8; $i++) {
            $chart5[$i] = $chart5_6Data['north'][$i] + $chart5_6Data['east'][$i] + $chart5_6Data['south'][$i] + $chart5_6Data['west'][$i];
        }
        return $chart5;
    }

    public static function chart6($chart5_6Data){
        $chart6 = [];
        for ($i=1; $i <= 8; $i++) {
            array_push($chart6, [ $chart5_6Data['north'][$i], $chart5_6Data['east'][$i], $chart5_6Data['south'][$i], $chart5_6Data['west'][$i] ]);
        }
        return $chart6;
    }

    public static function chart7($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart7")) {
            return Cache::get("home_charts_$daterange"."_chart7");
        } else {
            $escalated_count7 = [];
            $notescalated_count7 = [];
            $request_types = ['academic', 'enquiry', 'service'];
            foreach ($request_types as $keyx => $valuex) {
                $data = $services_obj->where('request_type', $valuex);
                $esc_count = 0;
                $not_esc_count = 0;
                foreach ($data as $keyy => $valuey) {
                    $esc_status = Timeline::where('request_id', $valuey['id'])->where('status', 'Escalated')->count();
                    if (!empty($esc_status) && $esc_status >0) {
                        $esc_count = $esc_count+1;
                    } else {
                        $not_esc_count = $not_esc_count+1;
                    }
                }
                array_push($escalated_count7, $esc_count);
                array_push($notescalated_count7, $not_esc_count);
            }
            $chart7 = [
                'Escalated'=>$escalated_count7,
                'NotEscalated'=>$notescalated_count7,
            ];
            Cache::put("home_charts_$daterange"."_chart7", $chart7, 20);
            return $chart7;
        }
    }

    public static function chart8($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart8")) {
            return Cache::get("home_charts_$daterange"."_chart8");
        } else {
            $escalated_count8 = [];
            $notescalated_count8 = [];
            $dept_types = [1,2,3,4,5,6,7,8];
            for ($deptid=1; $deptid <= 8; $deptid++) {
                $data = $services_obj->where('dept_id', $deptid);
                $esc_count = 0;
                $not_esc_count = 0;
                foreach ($data as $keyy => $valuey) {
                    $esc_status = Timeline::where('request_id', $valuey['id'])->where('status', 'Escalated')->count();
                    if (!empty($esc_status) && $esc_status >0) {
                        $esc_count = $esc_count+1;
                    } else {
                        $not_esc_count = $not_esc_count+1;
                    }
                }
                array_push($escalated_count8, $esc_count);
                array_push($notescalated_count8, $not_esc_count);
            }
            $chart8 = [
                'Escalated'=>$escalated_count8,
                'NotEscalated'=>$notescalated_count8,
            ];
            Cache::put("home_charts_$daterange"."_chart8", $chart8, 20);
            return $chart8;
        }
    }

    public static function chart9($daterange, $date_from, $date_to, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart9")) {
            return Cache::get("home_charts_$daterange"."_chart9");
        } else {
            $chart9_all = $services_obj;
            $chart9_academic = $chart9_all->where('request_type', 'academic')->all();
            $chart9_enquiry = $chart9_all->where('request_type', 'enquiry')->all();
            $chart9_service = $chart9_all->where('request_type', 'service')->all();
            $r2a = [];
            $types = [$chart9_academic, $chart9_enquiry, $chart9_service];
            foreach ($types as $type) {
                $r2a_data = [];
                $counter = 1;
                foreach ($type as $key) {
                    $temp_data = Timeline::select('id', 'request_id', 'status', 'created_at')->where('request_id', $key['id'])->whereIn('status', ['Received','Assigned'])->get();
                    if ($temp_data->contains('status', 'Received') && $temp_data->contains('status', 'Assigned')) {
                        $temp_received_1 = $temp_data->firstWhere('status', 'Received');
                        $temp_assigned_1 = $temp_data->firstWhere('status', 'Assigned');
                        array_push($r2a_data, $temp_received_1['created_at']->diffInHours($temp_assigned_1['created_at']));
                    }
                    $counter++;
                }
                array_push($r2a, round(array_sum($r2a_data)/$counter, 2));
            }
            $chart9 = [
                'r2a'=> $r2a
            ];
            Cache::put("home_charts_$daterange"."_chart9", $chart9, 2880);
            return $chart9;
        }
    }

    public static function chart23($daterange, $customers_obj_freq){
        if (Cache::has("home_charts_$daterange"."_chart23")) {
            return Cache::get("home_charts_$daterange"."_chart23");
        } else {
            $times0_service = $times0_enquiry = $times0_academic = $times1_service = $times1_enquiry = $times1_academic = $times5_service = $times5_enquiry = $times5_academic = $times10_service = $times10_enquiry = $times10_academic = 0;
            foreach ($customers_obj_freq as $customer) {
                $request_types = ['service', 'enquiry', 'academic', ];
                foreach ($request_types as $req_type) {
                    $request_name = $customer->{'req_count_'.$req_type};
                    if ($request_name == 0) {
                        ${'times0_'.$req_type}++;
                    } elseif ($request_name > 0 && $request_name <= 5) {
                        ${'times1_'.$req_type}++;
                    } elseif ($request_name > 5 && $request_name <= 10) {
                        ${'times5_'.$req_type}++;
                    } elseif ($request_name > 10) {
                        ${'times10_'.$req_type}++;
                    }
                }
            }
            $chart23 = [
                'Times0' => [$times0_academic, $times0_enquiry, $times0_service],
                'Times1' => [$times1_academic, $times1_enquiry, $times1_service],
                'Times5' => [$times5_academic, $times5_enquiry, $times5_service],
                'Times10' => [$times10_academic, $times10_enquiry, $times10_service]
            ];
            Cache::put("home_charts_$daterange"."_chart23", $chart23, 20);
            return $chart23;
        }
    }

    public static function chart24($daterange, $customers_obj_freq){
        if (Cache::has("home_charts_$daterange"."_chart24")) {
            return Cache::get("home_charts_$daterange"."_chart24");
        } else {
            $times0_North = $times0_East = $times0_South = $times0_West = $times1_North = $times1_East = $times1_South = $times1_West = $times5_North = $times5_East = $times5_South = $times5_West = $times10_North = $times10_East = $times10_South = $times10_West = 0;
            foreach ($customers_obj_freq as $customer) {
                if ($customer->region == 'north') { $cust_region = "North"; }
                if ($customer->region == 'east') { $cust_region = "East"; }
                if ($customer->region == 'south') { $cust_region = "South"; }
                if ($customer->region == 'west') { $cust_region = "West"; }
                $req_count_24 = (int)$customer->req_count_service + (int)$customer->req_count_academic + (int)$customer->req_count_enquiry;
                if ($req_count_24 == 0) {
                    ${'times0_'.$cust_region}++;
                } elseif ($req_count_24 > 0 && $req_count_24 <= 5) {
                    ${'times1_'.$cust_region}++;
                } elseif ($req_count_24 > 5 && $req_count_24 <= 10) {
                    ${'times5_'.$cust_region}++;
                } elseif ($req_count_24 > 10) {
                    ${'times10_'.$cust_region}++;
                }
            }
            $chart24 = [
                'Times0' => [$times0_North, $times0_East, $times0_South, $times0_West],
                'Times1' => [$times1_North, $times1_East, $times1_South, $times1_West],
                'Times5' => [$times5_North, $times5_East, $times5_South, $times5_West],
                'Times10' => [$times10_North, $times10_East, $times10_South, $times10_West]
            ];
            Cache::put("home_charts_$daterange"."_chart24", $chart24, 20);
            return $chart24;
        }
    }

    public static function chart25($daterange, $customers_obj_freq){
        if (Cache::has("home_charts_$daterange"."_chart25")) {
            return Cache::get("home_charts_$daterange"."_chart25");
        } else {
            $times0_Dr = $times0_Mr = $times0_Ms = $times1_Dr = $times1_Mr = $times1_Ms = $times5_Dr = $times5_Mr = $times5_Ms = $times10_Dr = $times10_Mr = $times10_Ms = 0;
            foreach ($customers_obj_freq as $customer) {
                if ($customer->title == 'Dr.') { $salutation = "Dr"; }
                if ($customer->title == 'Mr.') { $salutation = "Mr"; }
                if ($customer->title == 'Ms.') { $salutation = "Ms"; }
                $req_count_25 = (int)$customer->req_count_service + (int)$customer->req_count_academic + (int)$customer->req_count_enquiry;
                if ($req_count_25 == 0) {
                    ${'times0_'.$salutation}++;
                } elseif ($req_count_25 > 0 && $req_count_25 <= 5) {
                    ${'times1_'.$salutation}++;
                } elseif ($req_count_25 > 5 && $req_count_25 <= 10) {
                    ${'times5_'.$salutation}++;
                } elseif ($req_count_25 > 10) {
                    ${'times10_'.$salutation}++;
                }
            }
            $chart25 = [
                'Times0' => [$times0_Dr, $times0_Mr, $times0_Ms],
                'Times1' => [$times1_Dr, $times1_Mr, $times1_Ms],
                'Times5' => [$times5_Dr, $times5_Mr, $times5_Ms],
                'Times10' => [$times10_Dr, $times10_Mr, $times10_Ms]
            ];
            Cache::put("home_charts_$daterange"."_chart25", $chart25, 20);
            return $chart25;
        }
    }

    public static function chart28($daterange,$customers_obj){
        if (Cache::has("home_charts_$daterange"."_chart28")) {
            return Cache::get("home_charts_$daterange"."_chart28");
        } else {
            $chart28 = [
                'iOS_old' => 0,
                'iOS_new' => 0,
                'android_old' => 0,
                'android_new' => 0,
                'unknown' => 0
            ];
            foreach ($customers_obj as $customer) {
                if ($customer->platform == 'iOS' && $customer->app_version!=\Config('oly.current_version_iOS')) {
                    $chart28['iOS_old']++;
                } elseif ($customer->platform == 'iOS' && $customer->app_version==\Config('oly.current_version_iOS')) {
                    $chart28['iOS_new']++;
                } elseif ($customer->platform == 'android' && $customer->app_version!=\Config('oly.current_version_android')) {
                    $chart28['android_old']++;
                } elseif ($customer->platform == 'android' && $customer->app_version==\Config('oly.current_version_android')) {
                    $chart28['android_new']++;
                } else {
                    $chart28['unknown']++;
                }
            }
            Cache::put("home_charts_$daterange"."_chart28", $chart28, 1);

            return $chart28;
        }
    }

    public static function chart53($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart53")) {
            return Cache::get("home_charts_$daterange"."_chart53");
        } else {
            $chart_53_service = $chart_53_enquiry = $chart_53_academic = array();
            foreach ($services_obj as $service) {
                $hospital_state = Hospitals::where('id', $service->hospital_id)->value('state');
                $service_region = find_region($hospital_state);
                if ($service->request_type == 'service') {
                    array_push($chart_53_service, $service_region);
                }
                if ($service->request_type == 'enquiry') {
                    array_push($chart_53_enquiry, $service_region);
                }
                if ($service->request_type == 'academic') {
                    array_push($chart_53_academic, $service_region);
                }
            }
            $chart53 = [
                'Service' => [count(array_keys($chart_53_service, "north")),
                        count(array_keys($chart_53_service, "east")),
                        count(array_keys($chart_53_service, "south")),
                        count(array_keys($chart_53_service, "west"))],
                'Enquiry' => [count(array_keys($chart_53_enquiry, "north")),
                        count(array_keys($chart_53_enquiry, "east")),
                        count(array_keys($chart_53_enquiry, "south")),
                        count(array_keys($chart_53_enquiry, "west"))],
                'Academic' => [count(array_keys($chart_53_academic, "north")),
                        count(array_keys($chart_53_academic, "east")),
                        count(array_keys($chart_53_academic, "south")),
                        count(array_keys($chart_53_academic, "west"))]
            ];
            Cache::put("home_charts_$daterange"."_chart53", $chart53, 20);
            return $chart53;
        }
    }

    public static function chart54($daterange, $services_obj, $months12, $chart_months, $date_to){
        if (Cache::has("home_charts_$daterange"."_chart54")) {
            return Cache::get("home_charts_$daterange"."_chart54");
        } else {
            $chart54 = [];
            foreach (["service","academic","enquiry","installation"] as $type) {
                for ($month=0; $month < count($months12); $month++) {
                    $chart54[$type][$month]=0;
                }
            }
            foreach ($services_obj as $request) {
                $month = array_search ($request->created_at->format("M-y"), $chart_months);
                if(strlen($month)){
                    //$chart54[$request->request_type][$month]++;
                }
            }
            for ($i=0; $i < count($months12); $i++) {
                $chart_54['Service'][$i] = $chart54["service"][$i];
                $chart_54['Academic'][$i] = $chart54["academic"][$i];
                $chart_54['Enquiry'][$i] = $chart54["enquiry"][$i];
                $chart_54['Installation'][$i] = $chart54["installation"][$i];
            }
            Cache::put("home_charts_$daterange"."_chart54", $chart_54, 20);
            return $chart_54;
        }
    }

    public static function chart55($daterange, $services_obj, $months12, $chart_months, $date_to, $dept_obj){
        if (Cache::has("home_charts_$daterange"."_chart55")) {
            return Cache::get("home_charts_$daterange"."_chart55");
        } else {
            $chart55 = [];
            foreach (["Gastroenterology","Respiratory","GeneralSurgery","Urology","Gynaecology","ENT","Others","BioMedical"] as $type) {
                for ($month=0; $month < count($months12); $month++) {
                    $chart55[$type][$month]=0;
                }
            }
            foreach ($services_obj as $request) {
                $month = array_search ($request->created_at->format("M-y"), $chart_months);
                if(strlen($month)){
                    $dept = $dept_obj[$request->dept_id-1]->name;
                    $chart55[str_replace(' ', '', $dept)][$month]++;
                }
            }
            for ($i=0; $i < count($months12); $i++) {
                $chart_55['Gastroenterology'][$i] = $chart55["Gastroenterology"][$i];
                $chart_55['Respiratory'][$i] = $chart55["Respiratory"][$i];
                $chart_55['General Surgery'][$i] = $chart55["GeneralSurgery"][$i];
                $chart_55['Urology'][$i] = $chart55["Urology"][$i];
                $chart_55['Gynaecology'][$i] = $chart55["Gynaecology"][$i];
                $chart_55['ENT'][$i] = $chart55["ENT"][$i];
                $chart_55['Other'][$i] = $chart55["Others"][$i];
                $chart_55['BioMedical'][$i] = $chart55["BioMedical"][$i];
            }
            Cache::put("home_charts_$daterange"."_chart55", $chart_55, 20);
            return $chart_55;
        }
    }

    public static function difff($request_date,$date_to){
        return $date_to->firstOfMonth()->diffInMonths($request_date->firstOfMonth());
        $period = CarbonPeriod::create($request_date->firstOfMonth(), '1 month', $date_to);
        foreach ($period as $dt) {
                $chart_months[] = $dt->format("M-y");
                $months12[] = $dt->format("m");
        }
    }
    public static function chart56($daterange, $services_obj, $months12, $chart_months, $date_to){
        $date_to_orig = $date_to;
        if (Cache::has("home_charts_$daterange"."_chart56")) {
            return Cache::get("home_charts_$daterange"."_chart56");
        } else {
            $chart56 = [];
            foreach (["North","East","South","West"] as $region) {
                for ($month=0; $month < count($months12); $month++) {
                    $chart56[$region][$month]=0;
                }
            }

            // echo count($chart_months)."<pre>";
            // print_r($chart_months);
            // echo "</pre>";

            foreach ($services_obj as $request) {
                $month = array_search ($request->created_at->format("M-y"), $chart_months);
                // dd($date_to,$date_to_orig,$request->created_at->format());
                // echo $date_to."<br>";
                // print_r($request->created_at);
                // print_r($date_to);
                // $month = $date_to->firstOfMonth()->diffInMonths($request->created_at->firstOfMonth());
                // $month = GetCharts::difff($request->created_at,$date_to);
                // dd($request->created_at,$date_to,$month);
                // echo $date_to."<br>";
                // $date1 = $request->created_at->firstOfMonth()->timestamp;
                // $date2 = $date_to->firstOfMonth()->timestamp;
                // $difff = GetCharts::get_month_diff($date1,$date2);
                // dd($date1,$date2,$difff, $month);
                // dd($date1,$date2,$difff, $month,gettype($date1),gettype($date2),gettype($difff), gettype($month));
                // echo $date1." ".$date2." ".$difff.""."<br>";
                if (strlen($month)) {
                    // echo $month." \t ".$date_to->format("d-M-y")." \t ".$date_to." \t"."<br>";
                    // echo $month." ".$date_to->format("d-M-y")." ".$request->created_at->format("d-M-y")."<br><br>";
                    $chart56[ucfirst($request->region)][$month]++;
                }else{
                    echo "Unknown ".$request->id." : ".$request->created_at." : ".$month."<br>";
                }
            }
            //dd($chart56);
        Cache::put("home_charts_$daterange"."_chart56", $chart56, 20);
        return $chart56;
        }
    }

    public static function chart59($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart59")) {
            return Cache::get("home_charts_$daterange"."_chart59");
        } else {
            $chart_59_received = $chart_59_assigned = $chart_59_closed = $chart_59_under_repair = array();
            foreach ($services_obj as $service) {
                $hospital_state = Hospitals::where('id', $service->hospital_id)->value('state');
                $service_region = find_region($hospital_state);
                if ($service->status == 'Received') {
                    array_push($chart_59_received, $service_region);
                }
                if ($service->status == 'Assigned' || $service->status == 'Re-assigned' || $service->status == 'Attended') {
                    array_push($chart_59_assigned, $service_region);
                }
                if ($service->status == 'Closed') {
                    array_push($chart_59_closed, $service_region);
                }
                if (in_array($service->status, ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])) {
                    array_push($chart_59_under_repair, $service_region);
                }
            }
            $chart59 = [
                'Received' => [count(array_keys($chart_59_received, "north")),
                        count(array_keys($chart_59_received, "east")),
                        count(array_keys($chart_59_received, "south")),
                        count(array_keys($chart_59_received, "west"))],
                'Assigned' => [count(array_keys($chart_59_assigned, "north")),
                        count(array_keys($chart_59_assigned, "east")),
                        count(array_keys($chart_59_assigned, "south")),
                        count(array_keys($chart_59_assigned, "west"))],
                'Under_Repair' => [count(array_keys($chart_59_under_repair, "north")),
                        count(array_keys($chart_59_under_repair, "east")),
                        count(array_keys($chart_59_under_repair, "south")),
                        count(array_keys($chart_59_under_repair, "west"))],
                'Closed' => [count(array_keys($chart_59_closed, "north")),
                        count(array_keys($chart_59_closed, "east")),
                        count(array_keys($chart_59_closed, "south")),
                        count(array_keys($chart_59_closed, "west"))]
            ];
            Cache::put("home_charts_$daterange"."_chart59", $chart59, 20);
            return $chart59;
        }
    }

    public static function chart60($daterange, $date_from, $date_to, $customers_obj){
        if (Cache::has("home_charts_$daterange"."_chart60")) {
            return Cache::get("home_charts_$daterange"."_chart60");
        } else {
            $chart60['Dr'] = $chart60['Mr'] = $chart60['Ms'] = 0;
            foreach ($customers_obj as $customer) {
                if ($customer->title == 'Dr.') { ++$chart60['Dr']; }
                else if ($customer->title == 'Mr.') { ++$chart60['Mr']; }
                else if ($customer->title == 'Ms.') { ++$chart60['Ms']; }
            }
            Cache::put("home_charts_$daterange"."_chart60", $chart60, 20);
            return $chart60;
        }
    }

    public static function chart61($daterange, $customers_obj){
        if (Cache::has("home_charts_$daterange"."_chart61")) {
            return Cache::get("home_charts_$daterange"."_chart61");
        } else {
            $chart_61_Dr = $chart_61_Mr = $chart_61_Ms = array();
            foreach ($customers_obj as $customer) {
                if ($customer->title == 'Dr.') { array_push($chart_61_Dr, $customer->region); }
                if ($customer->title == 'Mr.') { array_push($chart_61_Mr, $customer->region); }
                if ($customer->title == 'Ms.') { array_push($chart_61_Ms, $customer->region); }
            }
            $chart61 = [
                'Dr' => [count(array_keys($chart_61_Dr, "north")),
                        count(array_keys($chart_61_Dr, "east")),
                        count(array_keys($chart_61_Dr, "south")),
                        count(array_keys($chart_61_Dr, "west"))],
                'Mr' => [count(array_keys($chart_61_Mr, "north")),
                        count(array_keys($chart_61_Mr, "east")),
                        count(array_keys($chart_61_Mr, "south")),
                        count(array_keys($chart_61_Mr, "west"))],
                'Ms' => [count(array_keys($chart_61_Ms, "north")),
                        count(array_keys($chart_61_Ms, "east")),
                        count(array_keys($chart_61_Ms, "south")),
                        count(array_keys($chart_61_Ms, "west"))]
            ];
            Cache::put("home_charts_$daterange"."_chart61", $chart61, 20);
            return $chart61;
        }
    }

    public static function chart62($daterange, $customers_obj, $months12, $chart_months, $date_to){
        if (Cache::has("home_charts_$daterange"."_chart62")) {
            return Cache::get("home_charts_$daterange"."_chart62");
        } else {
            $chart62 = [];
            foreach (["Dr","Mr","Ms"] as $type) {
                for ($month=0; $month < count($months12); $month++) {
                    $chart62[$type][$month]=0;
                }
            }
            foreach ($customers_obj as $customer) {
                $month = array_search ($customer->created_at->format("M-y"), $chart_months);
                if(strlen($month)){
                    if(!($customer->created_at->format('Y') == date('Y')-1 && $month == 0)){
                        $chart62[str_replace('.', '', $customer->title)][$month]++;
                    }
                }
            }
            for ($i=0; $i < count($months12); $i++) {
                $chart_62['Dr'][$i] = $chart62["Dr"][$i];
                $chart_62['Mr'][$i] = $chart62["Mr"][$i];
                $chart_62['Ms'][$i] = $chart62["Ms"][$i];
            }
            Cache::put("home_charts_$daterange"."_chart62", $chart_62, 20);
            return $chart_62;
        }
    }

    public static function chart63($daterange, $customers_obj, $months12, $chart_months, $date_to){
        if (Cache::has("home_charts_$daterange"."_chart63")) {
            return Cache::get("home_charts_$daterange"."_chart63");
        } else {
            $chart63 = [];
            foreach (["North","East","South","West"] as $region) {
                for ($month=0; $month < count($months12); $month++) {
                    $chart63[$region][$month]=0;
                }
            }
            foreach ($customers_obj as $customer) {
                $month = array_search ($customer->created_at->format("M-y"), $chart_months);
                if(strlen($month)){
                    $chart63[ucfirst($customer->region)][$month]++;
                }else{
                    // echo "Unknown CustomerId: ".$customer->id." : ".$customer->created_at." : ".$month."<br>";
                }
            }
            Cache::put("home_charts_$daterange"."_chart63", $chart63, 20);
            return $chart63;
        }
    }

    public static function chart68($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart68")) {
            return Cache::get("home_charts_$daterange"."_chart68");
        } else {
            $chart_68_escalated = array();
            $chart_68_notescalated = array();
            foreach ($services_obj as $service) {
                $hospital_state = Hospitals::where('id', $service->hospital_id)->value('state');
                $service_region = find_region($hospital_state);
                if ($service->escalation_count == 0 ||  is_null($service->escalation_count)) {
                    array_push($chart_68_notescalated, $service_region);
                } else {
                    array_push($chart_68_escalated, $service_region);
                }
            }
            $chart68 = [
                'Escalated' => [count(array_keys($chart_68_escalated, "north")),
                        count(array_keys($chart_68_escalated, "east")),
                        count(array_keys($chart_68_escalated, "south")),
                        count(array_keys($chart_68_escalated, "west"))],
                'NotEscalated' => [count(array_keys($chart_68_notescalated, "north")),
                        count(array_keys($chart_68_notescalated, "east")),
                        count(array_keys($chart_68_notescalated, "south")),
                        count(array_keys($chart_68_notescalated, "west"))]
            ];
            Cache::put("home_charts_$daterange"."_chart68", $chart68, 20);
            return $chart68;
        }
    }

    public static function chart70($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart70")) {
            return Cache::get("home_charts_$daterange"."_chart70");
        } else {
            $chart_70_r2a = $chart_70_a2c = $chart_70_r2a_north = $chart_70_r2a_east = $chart_70_r2a_south = $chart_70_r2a_west = $chart_70_a2c_north = $chart_70_a2c_east = $chart_70_a2c_south = $chart_70_a2c_west = array();
            foreach ($services_obj as $service) {
                $region = $service->region;
                $temp_data = Timeline::select('id', 'request_id', 'status', 'created_at')->where('request_id', $service->id)->whereIn('status', ['Received','Assigned','Closed'])->get();
                if ($temp_data->contains('status', 'Received') && $temp_data->contains('status', 'Assigned') && $temp_data->contains('status', 'Closed')) {
                    $temp_received_1 = $temp_data->firstWhere('status', 'Received');
                    $temp_assigned_1 = $temp_data->firstWhere('status', 'Assigned');
                    $temp_closed_1 = $temp_data->firstWhere('status', 'Closed');
                    array_push(${"chart_70_r2a_".$region}, $temp_received_1['created_at']->diffInHours($temp_assigned_1['created_at']));
                    array_push(${"chart_70_a2c_".$region}, $temp_assigned_1['created_at']->diffInHours($temp_closed_1['created_at']));
                } elseif ($temp_data->contains('status', 'Received') && $temp_data->contains('status', 'Assigned') && !$temp_data->contains('status', 'Closed')) {
                    $temp_received_1 = $temp_data->firstWhere('status', 'Received');
                    $temp_assigned_1 = $temp_data->firstWhere('status', 'Assigned');
                    array_push(${"chart_70_r2a_".$region}, $temp_received_1['created_at']->diffInHours($temp_assigned_1['created_at']));
                }
            }

            $chart70 = [
                'R2A' => [
                        calculate_ratio($chart_70_r2a_north), calculate_ratio($chart_70_r2a_east), calculate_ratio($chart_70_r2a_south), calculate_ratio($chart_70_r2a_west) ],
                'A2C' => [
                        calculate_ratio($chart_70_a2c_north), calculate_ratio($chart_70_a2c_east), calculate_ratio($chart_70_a2c_south), calculate_ratio($chart_70_a2c_west) ]
            ];
            Cache::put("home_charts_$daterange"."_chart70", $chart70, 2880);
            return $chart70;
        }
    }

    public static function chart71($daterange, $services_obj, $dept_obj){
        if (Cache::has("home_charts_$daterange"."_chart71")) {
            return Cache::get("home_charts_$daterange"."_chart71");
        } else {
            $chart71 = [
                'Gastroenterology' => [0,0,0,0],
                'Respiratory' => [0,0,0,0],
                'GeneralSurgery' => [0,0,0,0],
                'Urology' => [0,0,0,0],
                'Gynaecology' => [0,0,0,0],
                'ENT' => [0,0,0,0],
                'Other' => [0,0,0,0],
                'BioMedical' => [0,0,0,0],
                'Average' => [0,0,0,0]
            ];

            $chart71_Gastroenterology_1 = $chart71_Gastroenterology_2 = $chart71_Gastroenterology_3 = $chart71_Gastroenterology_4 = $chart71_Respiratory_1 = $chart71_Respiratory_2 = $chart71_Respiratory_3 = $chart71_Respiratory_4 = $chart71_GeneralSurgery_1 = $chart71_GeneralSurgery_2 = $chart71_GeneralSurgery_3 = $chart71_GeneralSurgery_4 = $chart71_Urology_1 = $chart71_Urology_2 = $chart71_Urology_3 = $chart71_Urology_4 = $chart71_Gynaecology_1 = $chart71_Gynaecology_2 = $chart71_Gynaecology_3 = $chart71_Gynaecology_4 = $chart71_ENT_1 = $chart71_ENT_2 = $chart71_ENT_3 = $chart71_ENT_4 = $chart71_Others_1 = $chart71_Others_2 = $chart71_Others_3 = $chart71_Others_4 = $chart71_BioMedical_1 = $chart71_BioMedical_2 = $chart71_BioMedical_3 = $chart71_BioMedical_4 = $chart71_Average_1 = $chart71_Average_2 = $chart71_Average_3 = $chart71_Average_4 = array();

            foreach ($services_obj as $request) {
                if (!is_null($request->feedback_id)) {
                    $feedback = Feedback::where('id', $request->feedback_id)->get()[0];

                    $dept = str_replace(' ', '', $dept_obj[$request->dept_id-1]->name);

                    array_push($chart71_Average_1, $feedback['response_speed']);
                    array_push(${'chart71_'.$dept.'_1'}, $feedback['response_speed']);
                    array_push($chart71_Average_2, $feedback['quality_of_response']);
                    array_push(${'chart71_'.$dept.'_2'}, $feedback['quality_of_response']);
                    array_push($chart71_Average_3, $feedback['app_experience']);
                    array_push(${'chart71_'.$dept.'_3'}, $feedback['app_experience']);
                    array_push($chart71_Average_4, $feedback['olympus_staff_performance']);
                    array_push(${'chart71_'.$dept.'_4'}, $feedback['olympus_staff_performance']);
                }
            }

            $chart71 = [
                'Gastroenterology' =>[
                    calculate_ratio($chart71_Gastroenterology_1), calculate_ratio($chart71_Gastroenterology_2), calculate_ratio($chart71_Gastroenterology_3), calculate_ratio($chart71_Gastroenterology_4) ],
                'Respiratory' =>[
                    calculate_ratio($chart71_Respiratory_1), calculate_ratio($chart71_Respiratory_2), calculate_ratio($chart71_Respiratory_3), calculate_ratio($chart71_Respiratory_4) ],
                'GeneralSurgery' =>[
                    calculate_ratio($chart71_GeneralSurgery_1), calculate_ratio($chart71_GeneralSurgery_2), calculate_ratio($chart71_GeneralSurgery_3), calculate_ratio($chart71_GeneralSurgery_4) ],
                'Urology' =>[
                    calculate_ratio($chart71_Urology_1), calculate_ratio($chart71_Urology_2), calculate_ratio($chart71_Urology_3), calculate_ratio($chart71_Urology_4) ],
                'Gynaecology' =>[
                    calculate_ratio($chart71_Gynaecology_1), calculate_ratio($chart71_Gynaecology_2), calculate_ratio($chart71_Gynaecology_3), calculate_ratio($chart71_Gynaecology_4) ],
                'ENT' =>[
                    calculate_ratio($chart71_ENT_1), calculate_ratio($chart71_ENT_2), calculate_ratio($chart71_ENT_3), calculate_ratio($chart71_ENT_4) ],
                'Other' =>[
                    calculate_ratio($chart71_Others_1), calculate_ratio($chart71_Others_2), calculate_ratio($chart71_Others_3), calculate_ratio($chart71_Others_4) ],
                'BioMedical' =>[
                    calculate_ratio($chart71_BioMedical_1), calculate_ratio($chart71_BioMedical_2), calculate_ratio($chart71_BioMedical_3), calculate_ratio($chart71_BioMedical_4) ],
                'Average' =>[
                    calculate_ratio($chart71_Average_1), calculate_ratio($chart71_Average_2), calculate_ratio($chart71_Average_3), calculate_ratio($chart71_Average_4) ]
            ];
            Cache::put("home_charts_$daterange"."_chart71", $chart71, 2880);
            return $chart71;
        }
    }

    public static function chart72($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart72")) {
            return Cache::get("home_charts_$daterange"."_chart72");
        } else {
            $chart72 = [
                'North' => [0,0,0,0],
                'East' => [0,0,0,0],
                'South' => [0,0,0,0],
                'West' => [0,0,0,0],
                'Average' => [0,0,0,0]
            ];
            $chart72_north_1 = $chart72_north_2 = $chart72_north_3 = $chart72_north_4 = $chart72_east_1 = $chart72_east_2 = $chart72_east_3 = $chart72_east_4 = $chart72_south_1 = $chart72_south_2 = $chart72_south_3 = $chart72_south_4 = $chart72_west_1 = $chart72_west_2 = $chart72_west_3 = $chart72_west_4 = $chart72_average_1 = $chart72_average_2 = $chart72_average_3 = $chart72_average_4 = array();
            foreach ($services_obj as $request) {
                if (!is_null($request->feedback_id)) {
                    $feedback = Feedback::where('id', $request->feedback_id)->get();
                    $feedback = $feedback[0];
                    array_push($chart72_average_1, $feedback['response_speed']);
                    array_push(${'chart72_'.$request->region.'_1'}, $feedback['response_speed']);
                    array_push($chart72_average_2, $feedback['quality_of_response']);
                    array_push(${'chart72_'.$request->region.'_2'}, $feedback['quality_of_response']);
                    array_push($chart72_average_3, $feedback['app_experience']);
                    array_push(${'chart72_'.$request->region.'_3'}, $feedback['app_experience']);
                    array_push($chart72_average_4, $feedback['olympus_staff_performance']);
                    array_push(${'chart72_'.$request->region.'_4'}, $feedback['olympus_staff_performance']);
                }
            }
            $chart72 = [
                'North' => [
                    calculate_ratio($chart72_north_1), calculate_ratio($chart72_north_2), calculate_ratio($chart72_north_3), calculate_ratio($chart72_north_4)],
                'East' => [
                    calculate_ratio($chart72_east_1), calculate_ratio($chart72_east_2), calculate_ratio($chart72_east_3), calculate_ratio($chart72_east_4)],
                'South' => [
                    calculate_ratio($chart72_south_1), calculate_ratio($chart72_south_2), calculate_ratio($chart72_south_3), calculate_ratio($chart72_south_4)],
                'West' => [
                    calculate_ratio($chart72_west_1), calculate_ratio($chart72_west_2), calculate_ratio($chart72_west_3), calculate_ratio($chart72_west_4)],
                'Average' => [
                    calculate_ratio($chart72_average_1), calculate_ratio($chart72_average_2), calculate_ratio($chart72_average_3),  calculate_ratio($chart72_average_4)]
            ];
            Cache::put("home_charts_$daterange"."_chart72", $chart72, 1440);
            return $chart72;
        }
    }

    public static function chart73($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart73")) {
            return Cache::get("home_charts_$daterange"."_chart73");
        } else {
            $chart73 = [
                'North' => [0,0,0,0,0,0,0,0],
                'East' => [0,0,0,0,0,0,0,0],
                'South' => [0,0,0,0,0,0,0,0],
                'West' => [0,0,0,0,0,0,0,0]
            ];
            $chart73_north_Received = $chart73_north_Assigned = $chart73_north_Received_At_Repair_Center = $chart73_north_Quotation_Prepared = $chart73_north_PO_Received = $chart73_north_Repair_Started = $chart73_north_Repair_Completed = $chart73_north_Ready_To_Dispatch = $chart73_north_Dispatched = $chart73_east_Received = $chart73_east_Assigned = $chart73_east_Received_At_Repair_Center = $chart73_east_Quotation_Prepared = $chart73_east_PO_Received = $chart73_east_Repair_Started = $chart73_east_Repair_Completed = $chart73_east_Ready_To_Dispatch = $chart73_east_Dispatched = $chart73_south_Received = $chart73_south_Assigned = $chart73_south_Received_At_Repair_Center = $chart73_south_Quotation_Prepared = $chart73_south_PO_Received = $chart73_south_Repair_Started = $chart73_south_Repair_Completed = $chart73_south_Ready_To_Dispatch = $chart73_south_Dispatched = $chart73_west_Received = $chart73_west_Assigned = $chart73_west_Received_At_Repair_Center = $chart73_west_Quotation_Prepared = $chart73_west_PO_Received = $chart73_west_Repair_Started = $chart73_west_Repair_Completed = $chart73_west_Ready_To_Dispatch = $chart73_west_Dispatched = 0;
            foreach ($services_obj as $request) {
                if ($request->status != 'Closed' && $request->request_type == 'service') {
                    if ($request->status == 'Re-assigned' || $request->status == 'Attended') {
                        $current_status = 'Assigned';
                    } else {
                        $current_status = $request->status;
                    }
                    $var_name = 'chart73_'.$request->region.'_'.$current_status;
                    ${$var_name}++;
                }
            }
            $chart73 = [
                'North' => [$chart73_north_Received, $chart73_north_Assigned, $chart73_north_Received_At_Repair_Center, $chart73_north_Quotation_Prepared, $chart73_north_PO_Received, $chart73_north_Repair_Started, $chart73_north_Repair_Completed, $chart73_north_Ready_To_Dispatch, $chart73_north_Dispatched
                ],
                'East' => [$chart73_east_Received, $chart73_east_Assigned, $chart73_east_Received_At_Repair_Center, $chart73_east_Quotation_Prepared, $chart73_east_PO_Received, $chart73_east_Repair_Started, $chart73_east_Repair_Completed, $chart73_east_Ready_To_Dispatch, $chart73_east_Dispatched
                ],
                'South' => [$chart73_south_Received, $chart73_south_Assigned, $chart73_south_Received_At_Repair_Center, $chart73_south_Quotation_Prepared, $chart73_south_PO_Received, $chart73_south_Repair_Started, $chart73_south_Repair_Completed, $chart73_south_Ready_To_Dispatch, $chart73_south_Dispatched
                ],
                'West' => [$chart73_west_Received, $chart73_west_Assigned, $chart73_west_Received_At_Repair_Center, $chart73_west_Quotation_Prepared, $chart73_west_PO_Received, $chart73_west_Repair_Started, $chart73_west_Repair_Completed, $chart73_west_Ready_To_Dispatch, $chart73_west_Dispatched
                ]
            ];
            Cache::put("home_charts_$daterange"."_chart73", $chart73, 20);
            return $chart73;
        }
    }

    public static function chart74($daterange, $services_obj){
        if (Cache::has("home_charts_$daterange"."_chart74")) {
            return Cache::get("home_charts_$daterange"."_chart74");
        } else {
            $chart74 = [
                '0' => [0,0,0,0,0,0,0,0],
                '1' => [0,1,0,0,0,0,0,0],
                '2' => [3,0,0,0,0,0,0,0],
                '3' => [0,6,0,0,0,0,0,1],
                '4' => [0,0,0,0,0,0,0,0],
                '5' => [0,0,0,4,0,0,0,0],
                '6' => [0,0,0,0,2,0,0,0],
                '7' => [0,0,1,0,0,2,0,0]
            ];
            $chart74_Received_1 = $chart74_Assigned_1 = $chart74_Received_At_Repair_Center_1 = $chart74_Quotation_Prepared_1 = $chart74_PO_Received_1 = $chart74_Repair_Started_1 = $chart74_Repair_Completed_1 = $chart74_Ready_To_Dispatch_1 = $chart74_Dispatched_1 = $chart74_Received_2 = $chart74_Assigned_2 = $chart74_Received_At_Repair_Center_2 = $chart74_Quotation_Prepared_2 = $chart74_PO_Received_2 = $chart74_Repair_Started_2 = $chart74_Repair_Completed_2 = $chart74_Ready_To_Dispatch_2 = $chart74_Dispatched_2 = $chart74_Received_3 = $chart74_Assigned_3 = $chart74_Received_At_Repair_Center_3 = $chart74_Quotation_Prepared_3 = $chart74_PO_Received_3 = $chart74_Repair_Started_3 = $chart74_Repair_Completed_3 = $chart74_Ready_To_Dispatch_3 = $chart74_Dispatched_3 = $chart74_Received_4 = $chart74_Assigned_4 = $chart74_Received_At_Repair_Center_4 = $chart74_Quotation_Prepared_4 = $chart74_PO_Received_4 = $chart74_Repair_Started_4 = $chart74_Repair_Completed_4 = $chart74_Ready_To_Dispatch_4 = $chart74_Dispatched_4 = $chart74_Received_5 = $chart74_Assigned_5 = $chart74_Received_At_Repair_Center_5 = $chart74_Quotation_Prepared_5 = $chart74_PO_Received_5 = $chart74_Repair_Started_5 = $chart74_Repair_Completed_5 = $chart74_Ready_To_Dispatch_5 = $chart74_Dispatched_5 = $chart74_Received_6 = $chart74_Assigned_6 = $chart74_Received_At_Repair_Center_6 = $chart74_Quotation_Prepared_6 = $chart74_PO_Received_6 = $chart74_Repair_Started_6 = $chart74_Repair_Completed_6 = $chart74_Ready_To_Dispatch_6 = $chart74_Dispatched_6 = $chart74_Received_7 = $chart74_Assigned_7 = $chart74_Received_At_Repair_Center_7 = $chart74_Quotation_Prepared_7 = $chart74_PO_Received_7 = $chart74_Repair_Started_7 = $chart74_Repair_Completed_7 = $chart74_Ready_To_Dispatch_7 = $chart74_Dispatched_7 = $chart74_Received_8 = $chart74_Assigned_8 = $chart74_Received_At_Repair_Center_8 = $chart74_Quotation_Prepared_8 = $chart74_PO_Received_8 = $chart74_Repair_Started_8 = $chart74_Repair_Completed_8 = $chart74_Ready_To_Dispatch_8 = $chart74_Dispatched_8 = 0;
            foreach ($services_obj as $request) {
                if ($request->status != 'Closed' && $request->request_type == 'service') {
                    if ($request->status == 'Re-assigned' || $request->status == 'Attended') {
                        $current_status = 'Assigned';
                    } else {
                        $current_status = $request->status;
                    }
                    $var_name = 'chart74_'.$current_status.'_'.$request->dept_id;
                    ${$var_name}++;
                }
            }
            $chart74 = [
                '0' => [$chart74_Received_1, $chart74_Assigned_1, $chart74_Received_At_Repair_Center_1, $chart74_Quotation_Prepared_1, $chart74_PO_Received_1, $chart74_Repair_Started_1, $chart74_Repair_Completed_1, $chart74_Ready_To_Dispatch_1, $chart74_Dispatched_1
                ],
                '1' => [$chart74_Received_2, $chart74_Assigned_2, $chart74_Received_At_Repair_Center_2, $chart74_Quotation_Prepared_2, $chart74_PO_Received_2, $chart74_Repair_Started_2, $chart74_Repair_Completed_2, $chart74_Ready_To_Dispatch_2, $chart74_Dispatched_2
                ],
                '2' => [$chart74_Received_3, $chart74_Assigned_3, $chart74_Received_At_Repair_Center_3, $chart74_Quotation_Prepared_3, $chart74_PO_Received_3, $chart74_Repair_Started_3, $chart74_Repair_Completed_3, $chart74_Ready_To_Dispatch_3, $chart74_Dispatched_3
                ],
                '3' => [$chart74_Received_4, $chart74_Assigned_4, $chart74_Received_At_Repair_Center_4, $chart74_Quotation_Prepared_4, $chart74_PO_Received_4, $chart74_Repair_Started_4, $chart74_Repair_Completed_4, $chart74_Ready_To_Dispatch_4, $chart74_Dispatched_4
                ],
                '4' => [$chart74_Received_5, $chart74_Assigned_5, $chart74_Received_At_Repair_Center_5, $chart74_Quotation_Prepared_5, $chart74_PO_Received_5, $chart74_Repair_Started_5, $chart74_Repair_Completed_5, $chart74_Ready_To_Dispatch_5, $chart74_Dispatched_5
                ],
                '5' => [$chart74_Received_6, $chart74_Assigned_6, $chart74_Received_At_Repair_Center_6, $chart74_Quotation_Prepared_6, $chart74_PO_Received_6, $chart74_Repair_Started_6, $chart74_Repair_Completed_6, $chart74_Ready_To_Dispatch_6, $chart74_Dispatched_6
                ],
                '6' => [$chart74_Received_7, $chart74_Assigned_7, $chart74_Received_At_Repair_Center_7, $chart74_Quotation_Prepared_7, $chart74_PO_Received_7, $chart74_Repair_Started_7, $chart74_Repair_Completed_7, $chart74_Ready_To_Dispatch_7, $chart74_Dispatched_7
                ],
                '7' => [$chart74_Received_8, $chart74_Assigned_8, $chart74_Received_At_Repair_Center_8, $chart74_Quotation_Prepared_8, $chart74_PO_Received_8, $chart74_Repair_Started_8, $chart74_Repair_Completed_8, $chart74_Ready_To_Dispatch_8, $chart74_Dispatched_8
                ]
            ];
            Cache::put("home_charts_$daterange"."_chart74", $chart74, 20);
            return $chart74;
        }
    }

    public static function chart81_combined($daterange, $direct_requests, $services_obj, $months12, $chart_months, $date_to){
        if (Cache::has("home_charts_$daterange"."_chart81_combined")) {
            return Cache::get("home_charts_$daterange"."_chart81_combined");
        } else {
            $chart81 = [];
            foreach (["Direct","MyVoice"] as $request_type) {
                for ($month=0; $month < count($months12); $month++) {
                    $chart81[$request_type][$month]=0;
                }
            }

            // Compute Direct
            foreach ($direct_requests as $request) {
                $month = array_search ($request->created_at->format("M-y"), $chart_months);
                if (strlen($month)) {
                    $chart81["Direct"][$month]++;
                }
            }

            // Compute MyVoice from services_obj
            foreach ($services_obj as $request) {
                $month = array_search ($request->created_at->format("M-y"), $chart_months);
                if (strlen($month)) {
                    $chart81["MyVoice"][$month]++;
                }
            }

            Cache::put("home_charts_$daterange"."_chart81_combined", $chart81, 1);
            return $chart81;
        }
    }

    public static function chart82($daterange, $direct_requests, $months12, $chart_months, $date_to){
        if (Cache::has("home_charts_$daterange"."_chart82")) {
            return Cache::get("home_charts_$daterange"."_chart82");
        } else {
            foreach (["North","East","South","West","Unknown"] as $region) {
                for ($month=0; $month < count($months12); $month++) {
                    $chart82[$region][$month]=0;
                }
            }
            foreach ($direct_requests as $request) {
                $month = array_search ($request->created_at->format("M-y"), $chart_months);
                if (strlen($month)) {
                    if (strlen($request->zone)) {
                        $chart82[ucfirst($request->zone)][$month]++;
                    } else {
                        $chart82["Unknown"][$month]++;
                    }
                }else{
                    // echo "Unknown ".$request->id." : ".$request->created_at." : ".$month."<br>";
                }
            }
            Cache::put("home_charts_$daterange"."_chart82", $chart82, 1);
            return $chart82;
        }
    }

    public static function chart83($daterange, $direct_requests, $months12, $chart_months, $date_to){
        if (Cache::has("home_charts_$daterange"."_chart83")) {
            return Cache::get("home_charts_$daterange"."_chart83");
        } else {
            $chart83 = [];
            foreach (["North","East","South","West","Unknown"] as $region) {
                for ($month=0; $month < count($months12); $month++) {
                    $chart83[$region][$month]=0;
                }
            }
            foreach ($direct_requests as $request) {
                $month = array_search ($request->created_at->format("M-y"), $chart_months);
                if (strlen($month)) {
                    if (strlen($request->zone)) {
                        $chart83[ucfirst($request->zone)][$month]++;
                    } else {
                        $chart83["Unknown"][$month]++;
                    }
                }else{
                    // echo "Unknown ".$request->id." : ".$request->created_at." : ".$month."<br>";
                }
            }
            Cache::put("home_charts_$daterange"."_chart83", $chart83, 1);
            return $chart83;
        }
    }

    public static function chart91($services_obj){
        $dafo = $idngei = $nrfo = $other = 0;
        foreach($services_obj as $request){
            if(!is_null($request->escalation_count) && $request->request_type == "service"){
                foreach(explode(",", $request->escalation_reasons) as $reason){
                    switch (trim(strtolower($reason))) {
                        case "delayed action from olympus": $dafo++; break;
                        case "i did not get enough information": $idngei++; break;
                        case "no response from olympus": $nrfo++; break;
                        case "other": $other++; break;
                        default: break;
                    }
                }
            }
        }
        $chart91 = [$dafo,$idngei,$nrfo,$other];
        return $chart91;
    }

    public static function getTTtimeline($tt){
        $td = [];
        $td['year'] = $tt['received']->year;
        $td['received2assigned'] = $tt['received']->diffInDays($tt['assigned']);
        $td['assinged2brough2sc'] = $tt['assigned']->diffInDays($tt['brought2sc']);
        $td['brought2sc2quotation_submitted'] = $tt['brought2sc']->diffInDays($tt['quotation_submitted']);
        $td['quotation_submitted2repair_started'] = $tt['quotation_submitted']->diffInDays($tt['repair_started']);
        $td['repair_started2repair_completed'] = $tt['repair_started']->diffInDays($tt['repair_completed']);
        $td['repair_completed2ready2dispatch'] = $tt['repair_completed']->diffInDays($tt['ready2dispatch']);
        $td['ready2dispatch2closed'] = $tt['ready2dispatch']->diffInDays($tt['closed']);
        $td['closed2feedback_received'] = $tt['closed']->diffInDays($tt['feedback_received']);
        return $td;
    }

    public static function chart92($daterange, $services_obj, $months12, $chart_months, $date_to1,  $years){
        try{
            $chart92 = [];
            $chart92["years"] = $years;
            foreach (["received2assigned", "assigned2brough2sc", "brought2sc2quotation_submitted", "quotation_submitted2repair_started", "repair_started2repair_completed", "repair_completed2ready2dispatch", "ready2dispatch2closed", "closed2feedback_received"] as $request_type) {
                foreach($years as $yearkey=>$yearvalue){
                    $chart92[$yearkey][]=0;
                }
            }

            $chart9_service_ids = collect($services_obj->where('request_type', 'service')->where('status', 'Closed')->all())->pluck("id");
            $temp_data = Timeline::select('id', 'request_id', 'status', 'created_at')
                ->whereIn('request_id', $chart9_service_ids)
                ->whereIn('status', ["Received","Assigned","Received_At_Repair_Center","Quotation_Prepared","Repair_Started","Repair_Completed","Ready_To_Dispatch","Closed"])
                ->get();
            $feedback_data = FeedBack::select('id', 'request_id', 'created_at')
                ->whereIn('request_id', $chart9_service_ids)
                ->get();
            $temp_data = $temp_data->groupBy('request_id');
            $td_data = [];
            $counter = 0;
            foreach($temp_data as $request_id=>$tl){
                // dd($request_id, $tl);
                $tt = [];
                $tt['received'] = $tl->firstWhere('status', 'Received');
                $tt['assigned'] = $tl->firstWhere('status', 'Assigned');
                $tt['brought2sc'] = $tl->firstWhere('status', 'Received_At_Repair_Center');
                $tt['quotation_submitted'] = $tl->firstWhere('status', 'Quotation_Prepared');
                $tt['repair_started'] = $tl->firstWhere('status', 'Repair_Started');
                $tt['repair_completed'] = $tl->firstWhere('status', 'Repair_Completed');
                $tt['ready2dispatch'] = $tl->firstWhere('status', 'Ready_To_Dispatch');
                $tt['closed'] = $tl->firstWhere('status', 'Closed');
                $tt['feedback_received'] = $feedback_data->firstWhere('request_id',$request_id);
                if(!is_null($tt['received']) && !is_null($tt['assigned']) && !is_null($tt['brought2sc']) && !is_null($tt['quotation_submitted']) && !is_null($tt['repair_started']) && !is_null($tt['repair_completed']) && !is_null($tt['ready2dispatch']) && !is_null($tt['closed']) && !is_null($tt['feedback_received'])){
                    $counter++;
                    $tc = [];
                    $tc['received'] = $tt['received']->created_at;
                    $tc['assigned'] = $tt['assigned']->created_at;
                    $tc['brought2sc'] = $tt['brought2sc']->created_at;
                    $tc['quotation_submitted'] = $tt['quotation_submitted']->created_at;
                    $tc['repair_started'] = $tt['repair_started']->created_at;
                    $tc['repair_completed'] = $tt['repair_completed']->created_at;
                    $tc['ready2dispatch'] = $tt['ready2dispatch']->created_at;
                    $tc['closed'] = $tt['closed']->created_at;
                    $tc['feedback_received'] = $tt['closed']->created_at;
                    array_push($td_data, GetCharts::getTTtimeline($tc));
                }
            }
            foreach($td_data as $ta){
                $year = $ta['year'];
                $chart92[$year][0] = $chart92[$year][0] + $ta['received2assigned'];
                $chart92[$year][1] = $chart92[$year][1] + $ta['assinged2brough2sc'];
                $chart92[$year][2] = $chart92[$year][2] + $ta['brought2sc2quotation_submitted'];
                $chart92[$year][3] = $chart92[$year][3] + $ta['quotation_submitted2repair_started'];
                $chart92[$year][4] = $chart92[$year][4] + $ta['repair_started2repair_completed'];
                $chart92[$year][5] = $chart92[$year][5] + $ta['repair_completed2ready2dispatch'];
                $chart92[$year][6] = $chart92[$year][6] + $ta['ready2dispatch2closed'];
                $chart92[$year][7] = $chart92[$year][7] + $ta['closed2feedback_received'];

            }
            foreach($years as $yearkey=>$yearvalue){
                $year_counter = 0;
                foreach($td_data as $ta){
                    if($ta['year'] == $yearkey){
                        $year_counter++;
                    }
                }
                for($i=0; $i < 8 ; $i++) {
                    if($year_counter > 0){
                        $chart92[$yearkey][$i] = round($chart92[$yearkey][$i] / $year_counter, 1);
                    } else {
                        $chart92[$yearkey][$i] = 0;
                    }
                }
            }
            // dd($chart92);
            return $chart92;
        }catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public static function chart93($daterange, $services_obj, $months12, $chart_months, $date_to1){
        foreach (["dafo", "idngei", "nrfo", "other"] as $request_type) {
            for ($month=0; $month < count($months12); $month++) {
                $chart93[$request_type][$month]=0;
            }
        }

        foreach ($services_obj as $request) {
            $month = array_search ($request->created_at->format("M-y"), $chart_months);
            if ($month !== false) {
                if(!is_null($request->escalation_count) && $request->request_type == "service"){
                    foreach(explode(",", $request->escalation_reasons) as $reason){
                        switch (trim(strtolower($reason))) {
                            case "delayed action from olympus": $chart93["dafo"][$month]++; break;
                            case "i did not get enough information": $chart93["idngei"][$month]++; break;
                            case "no response from olympus": $chart93["nrfo"][$month]++; break;
                            case "other": $chart93["other"][$month]++; break;
                            default: break;
                        }
                    }
                }
            }
        }
        return $chart93;
    }
}
