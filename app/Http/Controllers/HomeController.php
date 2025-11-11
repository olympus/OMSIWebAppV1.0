<?php

namespace App\Http\Controllers;

use App\AcademicRequests;
use App\Charts\GetCharts;
use App\Models\Departments;
use App\Mail\Mis;
use App\Mail\MisPANIndia;
use App\Mail\MisRegional;
use App\Models\Customers;
use App\Models\Feedback;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\Reportsetting;
use App\StatusTimeline as Timeline;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Mail;
use PDF;
use Spatie\Activitylog\Models\Activity;
use Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     // $this->middleware('auth');
    //     $this->middleware('auth', ['except' => ['mis_report', 'regionmis_report']]);
    // }

    public function regionsettings($region)
    {
        $validator = Validator::make(
          [
            'region' => $region,
          ],[
            'region' => 'required|string|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        $emaildata = array();
        $data = Reportsetting::where('name', 'autoemail_'.$region)->get()->toArray();
        if (empty(array_filter($data))) {
            $to_emails = '';
            $cc_emails = '';
        } else {
            $data = $data[0];
            $to_emails = explode(',', $data['to_emails']);
            $cc_emails = explode(',', $data['cc_emails']);
        }

        return view('dashboardregion.settings', [
            'region'=>$region,
            'to_emails'=>$to_emails,
            'cc_emails'=>$cc_emails
        ]);
    }

    public function regionsettings_update(Request $request, $region)
    {
        $validator = Validator::make(
          [
            'region' => $region,
          ],[
            'region' => 'required|string|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        return response('Page Disabled. It will be enabled after adding all employees to table.', 403)
                  ->header('Content-Type', 'text/html');
        $to_emails = implode(",", $request->to_emails);
        $cc_emails = implode(",", $request->cc_emails);
        $exists = Reportsetting::where('name', 'autoemail_'.$region)->get()->toArray();
        if (empty(array_filter($exists))) {
            $data = new Reportsetting;
            $data->name = 'autoemail_'.$region;
            $data->to_emails = $to_emails;
            $data->cc_emails = $cc_emails;
            $data->save();
        } else {
            $data = Reportsetting::find($exists[0]['id']);
            $data->update([
                'to_emails' => $to_emails,
                'cc_emails' => $cc_emails
            ]);
        }
        return redirect('/dashboard/settings/'.$region)->with('message', 'Data successfully updated');
    }

    public function activity()
    {
        //$lastActivity = Activity::orderBy("created_at", "DESC")->paginate(15);
        return view('activity_log.index');
    }

    public function activityListOld(Request $request)
    {
        try{
            $columns = array(
                0 => 'causer_id',
                1 => 'description',
                2 => 'subject_type',
                3 => 'subject_id',
                4 => 'causer_type',
                5 => 'created_at'
            );

            $totalData = Activity::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = Activity::offset($start)
                ->limit($limit)
                ->orderBy('created_at',$dir)
                ->select('*')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  Activity::
                select('*')
                ->where('causer_type','LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy('created_at',$dir)
                ->get();

                $totalFiltered = Activity::
                select('*')
                ->where('causer_type','LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $current_user) {
                    $nestedData['causer_id'] = $current_user->causer_id ?? 'API' ;
                    $nestedData['description'] = $current_user->description;
                    $nestedData['subject_type'] = $current_user->subject_type;
                    $nestedData['subject_id'] = $current_user->subject_id;
                    $nestedData['causer_type'] = $current_user->causer_type ?? 'API' ;
                    $nestedData['created_at'] = date("Y-m-d H:i:s", strtotime($current_user->created_at));
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

    public function activityList(Request $request)
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
        try{
            $columns = array(
                0 => 'id',
                1 => 'causer_id',
                2 => 'description',
                3 => 'subject_type',
                4 => 'subject_id',
                5 => 'causer_type',
                6 => 'created_at'
            );

            $totalData = Activity::whereBetween('created_at', [$current_date, $old_date])->count();

            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = Activity::offset($start)
                ->whereBetween('created_at', [$current_date, $old_date])
                ->limit($limit)
                ->orderBy('id',$dir)
                ->select('*')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  Activity::
                whereBetween('created_at', [$current_date, $old_date])
                ->where('causer_type','LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy('id',$dir)
                ->get();

                $totalFiltered = Activity::
                whereBetween('created_at', [$current_date, $old_date])
                ->where('causer_type','LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $current_user) {
                    $nestedData['id'] = $current_user->id;
                    $nestedData['causer_id'] = $current_user->causer_id ?? 'API' ;
                    $nestedData['description'] = $current_user->description;
                    $nestedData['subject_type'] = $current_user->subject_type;
                    $nestedData['subject_id'] = $current_user->subject_id;
                    $nestedData['causer_type'] = $current_user->causer_type ?? 'API' ;
                    $nestedData['created_at'] = date("Y-m-d H:i:s", strtotime($current_user->created_at));
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

    public function home_dashboard($daterange='')
    {
        // $validator = Validator::make(
        //   [
        //     'daterange' => $daterange,
        //   ],[
        //     'daterange' => 'nullable|date_format:Y-m-d_Y-m-d',
        //   ]
        // );

        // if ($validator->fails()) {
        //     return  $validator->messages()->first();
        // }

        if(empty($daterange)){
            $current_date = Carbon::now()->format('Y-m-d');
            $old_date = Carbon::now()->subMonth(3)->format('Y-m-d');
            //$daterange = '2022-10-01_'.date('Y-m-d', strtotime("today"));
            $daterange = $old_date.'_'.$current_date;
        }
        $filtereddata = GetCharts::home($daterange);
        $pending_service_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'service')->where('status', 'Received')->count();
        $pending_enquiry_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'enquiry')->where('status', 'Received')->count();
        $pending_academic_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'academic')->where('status', 'Received')->count();
        $new_customers = Customers::count();
        return view('home.index', [
            'pending_service_requests'=>$pending_service_requests_count,
            'pending_enquiry_requests'=>$pending_enquiry_requests_count,
            'pending_academic_requests'=>$pending_academic_requests_count,
            'new_customers'=>$new_customers,
            'filterdate'=> $filtereddata['filterdate'],
            'chart_months'=>$filtereddata['chart_months'],
            'chart56'=>$filtereddata['chart56'],
            'chart63'=>$filtereddata['chart63'],
            'chart71'=>$filtereddata['chart71'],
            'chart72'=>$filtereddata['chart72'],
            'chart81'=>$filtereddata['chart81'],
            'chart83'=>$filtereddata['chart83'],
            'chart91'=>$filtereddata['chart91'],
            'chart92'=>$filtereddata['chart92'],
            'chart93'=>$filtereddata['chart93']
        ]);
    }


    /**
     * Show the all India dashboard.
     *
     * @return Response
     */
    public function all_india_dashboard($daterange='')
    {
        if(empty($daterange)){
            // $daterange = date('Y-m-d', strtotime("-12 months")).'_'.date('Y-m-d', strtotime("today"));
            $daterange = '2018-08-01_'.date('Y-m-d', strtotime("today"));
        }
        $filtereddata = GetCharts::all_india($daterange);
        $pending_service_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'service')->where('status', 'Received')->count();
        $pending_enquiry_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'enquiry')->where('status', 'Received')->count();
        $pending_academic_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'academic')->where('status', 'Received')->count();
        $date_minus_7_days = date('Y-m-d H:i:s', strtotime('-7 days'));
        $new_customers = Customers::count();
        return view('home', [
            'pending_service_requests'=>$pending_service_requests_count,
            'pending_enquiry_requests'=>$pending_enquiry_requests_count,
            'pending_academic_requests'=>$pending_academic_requests_count,
            'new_customers'=>$new_customers,
            'filterdate'=> $filtereddata['filterdate'],
            'chart1'=>$filtereddata['chart1'],
            'chart2'=>$filtereddata['chart2'],
            'chart3'=>$filtereddata['chart3'],
            'chart4'=>$filtereddata['chart4'],
            'chart5'=>$filtereddata['chart5'],
            'chart6'=>$filtereddata['chart6'],
            'chart7'=>$filtereddata['chart7'],
            'chart8'=>$filtereddata['chart8'],
            'chart9'=>$filtereddata['chart9'],
            'chart_months'=>$filtereddata['chart_months'],
            'chart53'=>$filtereddata['chart53'],
            'chart54'=>$filtereddata['chart54'],
            'chart55'=>$filtereddata['chart55'],
            'chart56'=>$filtereddata['chart56'],
            'chart59'=>$filtereddata['chart59'],
            'chart60'=>$filtereddata['chart60'],
            'chart61'=>$filtereddata['chart61'],
            'chart62'=>$filtereddata['chart62'],
            'chart63'=>$filtereddata['chart63'],
            'chart68'=>$filtereddata['chart68'],
            'chart70'=>$filtereddata['chart70'],
            'chart71'=>$filtereddata['chart71'],
            'chart72'=>$filtereddata['chart72'],
            'chart73'=>$filtereddata['chart73'],
            'chart74'=>$filtereddata['chart74'],
            'chart23'=>$filtereddata['chart23'],
            'chart24'=>$filtereddata['chart24'],
            'chart25'=>$filtereddata['chart25'],
            'chart28'=>$filtereddata['chart28'],
            'chart81'=>$filtereddata['chart81']
        ]);
    }

    /**
     * Show the MIS report.
     *
     * @return Response
     */
    public function mis_report()
    {
        $firstdate= "01-08-2018";
        // $firstdate= date('d-m-Y',strtotime('first day of last month', time()));
        $lastdate=  date('d-m-Y', strtotime('last day of last month', time()));
        $daterange = $firstdate."_".$lastdate;
        $filtereddata = (Cache::has('chartsdata_mis_panindia') ? Cache::get('chartsdata_mis_panindia') : $this->generatecharts($daterange) );

        // $filtereddata = GetCharts::all_india($daterange);
        $pdfname = 'Pan India Report - 15-Aug-2018'.'_'.date('d-M-Y', strtotime('last day of last month', time())).'.pdf';
        // $pdfname = 'Pan India Report - '.date('d-M', strtotime('first day of last month', time())).'_'.date('d-M-Y', strtotime('last day of last month', time())).'.pdf';
        // dd(
        //     $filtereddata['chart24'],
        //     $filtereddata['chart25'],
        //     $filtereddata['chart28']
        // );
        // return view('mis',[
        //     'daterange'=>$daterange,
        //     'filterdate'=> $filtereddata['filterdate'],
        //     'chart_months'=>$filtereddata['chart_months'],
        //     'chart1'=>$filtereddata['chart1'],
        //     'chart2'=>$filtereddata['chart2'],
        //     'chart3'=>$filtereddata['chart3'],
        //     'chart4'=>$filtereddata['chart4'],
        //     'chart5'=>$filtereddata['chart5'],
        //     'chart6'=>$filtereddata['chart6'],
        //     'chart7'=>$filtereddata['chart7'],
        //     'chart8'=>$filtereddata['chart8'],
        //     'chart9'=>$filtereddata['chart9'],
        //     'chart53'=>$filtereddata['chart53'],
        //     'chart54'=>$filtereddata['chart54'],
        //     'chart55'=>$filtereddata['chart55'],
        //     'chart56'=>$filtereddata['chart56'],
        //     'chart59'=>$filtereddata['chart59'],
        //     'chart60'=>$filtereddata['chart60'],
        //     'chart61'=>$filtereddata['chart61'],
        //     'chart62'=>$filtereddata['chart62'],
        //     'chart63'=>$filtereddata['chart63'],
        //     'chart68'=>$filtereddata['chart68'],
        //     'chart70'=>$filtereddata['chart70'],
        //     'chart71'=>$filtereddata['chart71'],
        //     'chart72'=>$filtereddata['chart72'],
        //     'chart73'=>$filtereddata['chart73'],
        //     'chart74'=>$filtereddata['chart74'],
        //     'chart23'=>$filtereddata['chart23'],
        //     'chart24'=>$filtereddata['chart24'],
        //     'chart25'=>$filtereddata['chart25'],
        //     'chart28'=>$filtereddata['chart28']
        // ]);

        $pdf = PDF::loadView('mis', [
            'daterange'=>$daterange,
            'filterdate'=> $filtereddata['filterdate'],
            'chart_months'=>$filtereddata['chart_months'],
            'chart1'=>$filtereddata['chart1'],
            'chart2'=>$filtereddata['chart2'],
            'chart3'=>$filtereddata['chart3'],
            'chart4'=>$filtereddata['chart4'],
            'chart5'=>$filtereddata['chart5'],
            'chart6'=>$filtereddata['chart6'],
            'chart7'=>$filtereddata['chart7'],
            'chart8'=>$filtereddata['chart8'],
            'chart9'=>$filtereddata['chart9'],
            'chart53'=>$filtereddata['chart53'],
            'chart54'=>$filtereddata['chart54'],
            'chart55'=>$filtereddata['chart55'],
            'chart56'=>$filtereddata['chart56'],
            'chart59'=>$filtereddata['chart59'],
            'chart60'=>$filtereddata['chart60'],
            'chart61'=>$filtereddata['chart61'],
            'chart62'=>$filtereddata['chart62'],
            'chart63'=>$filtereddata['chart63'],
            'chart68'=>$filtereddata['chart68'],
            'chart70'=>$filtereddata['chart70'],
            'chart71'=>$filtereddata['chart71'],
            'chart72'=>$filtereddata['chart72'],
            'chart73'=>$filtereddata['chart73'],
            'chart74'=>$filtereddata['chart74'],
            'chart23'=>$filtereddata['chart23'],
            'chart24'=>$filtereddata['chart24'],
            'chart25'=>$filtereddata['chart25'],
            'chart28'=>$filtereddata['chart28']
        ])->setPaper('a4')->setOrientation('landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'enable-javascript' => true,
            'javascript-delay' => 5000,
            // 'images' => true,
            // 'enable-smart-shrinking' => true,
            'no-stop-slow-scripts' => true
        ]);

        $pdf->setOption('footer-right', '(Page [page])');
        $pdf->setOption('footer-html', "<!DOCTYPE html><head><script>
        function subst() {
          var vars={};
          var x=document.location.search.substring(1).split('&');
          for (var i in x) {var z=x[i].split('=',2);vars[z[0]] = unescape(z[1]);}
          var x=['frompage','topage','page','webpage','section','subsection','subsubsection'];
          for (var i in x) {
            var y = document.getElementsByClassName(x[i]);
            for (var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];
          }
        }
        </script></head><body style='border:0; margin: 0;' onload='subst()'>
        <img src='".asset('/Omsi_footer.png')."'/>
        <table style='border-bottom: 1px solid black; width: 100%'>
          <tr>
            <td class='section'></td>
            <td style='text-align:right'>
              Page <span class='page'></span> of <span class='topage'></span>
            </td>
          </tr>
        </table>
        </body></html>");
        $pdf->setOption('margin-top', 20);
        $pdf->setOption('margin-bottom', 30);
        // return $pdf->inline();
        // return $pdf->download('Pan India Report -'.date('F-Y',strtotime('last month', time())).'.pdf');

        $pdfpath = storage_path().'/exports/'.'PanIndia'.$daterange.'.pdf';

        if (file_exists($pdfpath)) {
            unlink($pdfpath);
            $pdf->save($pdfpath);
        } else {
            $pdf->save($pdfpath);
        }

        $to_emails = Reportsetting::where('name', 'autoemail_panindia')->value('to_emails');
        $to_emails = explode(',', $to_emails);
        for ($i=0; $i < sizeof($to_emails); $i++) {
            $to_final[]['email'] = $to_emails[$i];
        }

        $cc_emails = Reportsetting::where('name', 'autoemail_panindia')->value('cc_emails');
        $cc_emails = explode(',', $cc_emails);
        for ($i=0; $i < sizeof($cc_emails); $i++) {
            $cc_final[]['email'] = $cc_emails[$i];
        }

        Mail::to($to_final)->cc($cc_final)
            ->send(new MisPANIndia($pdfname, $pdfpath, $daterange));
        unlink($pdfpath);
        return 'success';
        // return $pdf->download('Pan India Report -'.date('F-Y',strtotime('last month', time())).'.pdf');

        // return view('mis',[
        //     'pending_service_requests'=>$pending_service_requests_count,
        //     'pending_enquiry_requests'=>$pending_enquiry_requests_count,
        //     'pending_academic_requests'=>$pending_academic_requests_count,
        //     'new_customers'=>$new_customers,
        //     'filterdate'=> $filtereddata['filterdate'],
        //     'chart1'=>$filtereddata['chart1'],
        //     'chart2'=>$filtereddata['chart2'],
        //     'chart3'=>$filtereddata['chart3'],
        //     'chart4'=>$filtereddata['chart4'],
        //     'chart5'=>$filtereddata['chart5'],
        //     'chart6'=>$filtereddata['chart6'],
        //     'chart7'=>$filtereddata['chart7'],
        //     'chart8'=>$filtereddata['chart8'],
        //     'chart9'=>$filtereddata['chart9'],
        //     'chart10'=>$filtereddata['chart10']
        // ]);
    }
    /**
     * Show the MIS report.
     *
     * @return Response
     */
    public function export_report_dashboard($daterange)
    {
        $filtereddata = GetCharts::all_india($daterange);
        $pending_service_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'service')->where('status', 'Received')->count();
        $pending_enquiry_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'enquiry')->where('status', 'Received')->count();
        $pending_academic_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'academic')->where('status', 'Received')->count();
        $date_minus_7_days = date('Y-m-d H:i:s', strtotime('-30 days'));
        $new_customers = Customers::where('created_at', '>=', $date_minus_7_days)->count();

        $file_date = date("d-M-Y", strtotime(explode("_", $filtereddata['filterdate'])[0]))."_".date("d-M-Y", strtotime(explode("_", $filtereddata['filterdate'])[1]));

        $pdf = PDF::loadView('mis', [
        // return view('mis', [
            'pending_service_requests'=>$pending_service_requests_count,
            'pending_enquiry_requests'=>$pending_enquiry_requests_count,
            'pending_academic_requests'=>$pending_academic_requests_count,
            'new_customers'=>$new_customers,
            'filterdate'=> $filtereddata['filterdate'],
            'chart1'=>$filtereddata['chart1'],
            'chart2'=>$filtereddata['chart2'],
            'chart3'=>$filtereddata['chart3'],
            'chart4'=>$filtereddata['chart4'],
            'chart5'=>$filtereddata['chart5'],
            'chart6'=>$filtereddata['chart6'],
            'chart7'=>$filtereddata['chart7'],
            'chart8'=>$filtereddata['chart8'],
            'chart9'=>$filtereddata['chart9'],
            'chart_months'=>$filtereddata['chart_months'],
            'chart53'=>$filtereddata['chart53'],
            'chart54'=>$filtereddata['chart54'],
            'chart55'=>$filtereddata['chart55'],
            'chart56'=>$filtereddata['chart56'],
            'chart59'=>$filtereddata['chart59'],
            'chart60'=>$filtereddata['chart60'],
            'chart61'=>$filtereddata['chart61'],
            'chart62'=>$filtereddata['chart62'],
            'chart63'=>$filtereddata['chart63'],
            'chart68'=>$filtereddata['chart68'],
            'chart70'=>$filtereddata['chart70'],
            'chart71'=>$filtereddata['chart71'],
            'chart72'=>$filtereddata['chart72'],
            'chart73'=>$filtereddata['chart73'],
            'chart74'=>$filtereddata['chart74'],
            'chart23'=>$filtereddata['chart23'],
            'chart24'=>$filtereddata['chart24'],
            'chart25'=>$filtereddata['chart25'],
            'chart28'=>$filtereddata['chart28']
        // ]);
        ])->setPaper('a4')->setOrientation('landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'enable-javascript' => true,
            'javascript-delay' => 2000,
            'no-stop-slow-scripts' => true
        ]);
        // dd(
        //     json_encode($filtereddata['chart23']),
        //     $filtereddata['chart25']
        // );
        $pdf->setOption('footer-right', '(Page [page])');

        $pdf->setOption('footer-html', "<!DOCTYPE html><head><script>
        function subst() {
          var vars={};
          var x=document.location.search.substring(1).split('&');
          for (var i in x) {var z=x[i].split('=',2);vars[z[0]] = unescape(z[1]);}
          var x=['frompage','topage','page','webpage','section','subsection','subsubsection'];
          for (var i in x) {
            var y = document.getElementsByClassName(x[i]);
            for (var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];
          }
        }
        </script></head><body style='border:0; margin: 0;' onload='subst()'>
        <img src='".asset('/Omsi_footer.png')."'/>
        <table style='border-bottom: 1px solid black; width: 100%'>
          <tr>
            <td class='section'></td>
            <td style='text-align:right'>
              Page <span class='page'></span> of <span class='topage'></span>
            </td>
          </tr>
        </table>
        </body></html>");
        $pdf->setOption('margin-top', 20);
        $pdf->setOption('margin-bottom', 30);

        return $pdf->download('Pan India Report - '.$file_date.'.pdf');
        // return $pdf->inline();



        // return view('mis',[
        //     'pending_service_requests'=>$pending_service_requests_count,
        //     'pending_enquiry_requests'=>$pending_enquiry_requests_count,
        //     'pending_academic_requests'=>$pending_academic_requests_count,
        //     'new_customers'=>$new_customers,
        //     'filterdate'=> $filtereddata['filterdate'],
        //     'chart1'=>$filtereddata['chart1'],
        //     'chart2'=>$filtereddata['chart2'],
        //     'chart3'=>$filtereddata['chart3'],
        //     'chart4'=>$filtereddata['chart4'],
        //     'chart5'=>$filtereddata['chart5'],
        //     'chart6'=>$filtereddata['chart6'],
        //     'chart7'=>$filtereddata['chart7'],
        //     'chart8'=>$filtereddata['chart8'],
        //     'chart9'=>$filtereddata['chart9'],
        //     'chart10'=>$filtereddata['chart10']
        // ]);
    }
    /**
     * Export the Regional report.
     *
     * @return Response
     */
    public function exportregion_report($region, $daterange)
    {
         $validator = Validator::make(
          [
            'region' => $region,
          ],[
            'region' => 'string|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        // $daterange = date('Y-m-d',strtotime("-30 days")).'_'.date('Y-m-d',strtotime("+1 day"));
        $filtereddata = $this->regiondashboard($region, $daterange);
        $pending_service_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'service')->where('status', 'Received')->count();
        $pending_enquiry_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'enquiry')->where('status', 'Received')->count();
        $pending_academic_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'academic')->where('status', 'Received')->count();
        $date_minus_7_days = date('Y-m-d H:i:s', strtotime('-30 days'));
        $new_customers = Customers::where('created_at', '>=', $date_minus_7_days)->count();

        $file_date = date("d-M-Y", strtotime(explode("_", $filtereddata['filterdate'])[0]))."_".date("d-M-Y", strtotime(explode("_", $filtereddata['filterdate'])[1]));

        // return view('regionmis', [
        //     'region'=>$filtereddata['region'],
        //     'chart_months'=>$filtereddata['chart_months'],
        //     'pending_service_requests'=>$filtereddata['pending_service_requests'],
        //     'pending_enquiry_requests'=>$filtereddata['pending_enquiry_requests'],
        //     'pending_academic_requests'=>$filtereddata['pending_academic_requests'],
        //     'new_customers'=>$filtereddata['new_customers'],
        //     'filterdate'=> $filtereddata['filterdate'],
        //     'chart31'=>$filtereddata['chart31'],
        //     'chart32'=>$filtereddata['chart32'],
        //     'chart33'=>$filtereddata['chart33'],
        //     'chart34'=>$filtereddata['chart34'],
        //     'chart35'=>$filtereddata['chart35'],
        //     'chart36'=>$filtereddata['chart36'],
        //     'chart37'=>$filtereddata['chart37'],
        //     'chart38'=>$filtereddata['chart38'],
        //     'chart39'=>$filtereddata['chart39'],
        //     'chart40'=>$filtereddata['chart40'],
        //     'chart41'=>$filtereddata['chart41'],
        //     'chart42'=>$filtereddata['chart42'],
        //     'chart43'=>$filtereddata['chart43'],
        // ]);

        $pdf = PDF::loadView('regionmis', [
            'region'=>$filtereddata['region'],
            'chart_months'=>$filtereddata['chart_months'],
            'pending_service_requests'=>$filtereddata['pending_service_requests'],
            'pending_enquiry_requests'=>$filtereddata['pending_enquiry_requests'],
            'pending_academic_requests'=>$filtereddata['pending_academic_requests'],
            'new_customers'=>$filtereddata['new_customers'],
            'filterdate'=> $filtereddata['filterdate'],
            'chart31'=>$filtereddata['chart31'],
            'chart32'=>$filtereddata['chart32'],
            'chart33'=>$filtereddata['chart33'],
            'chart34'=>$filtereddata['chart34'],
            'chart35'=>$filtereddata['chart35'],
            'chart36'=>$filtereddata['chart36'],
            'chart37'=>$filtereddata['chart37'],
            'chart38'=>$filtereddata['chart38'],
            'chart39'=>$filtereddata['chart39'],
            'chart40'=>$filtereddata['chart40'],
            'chart41'=>$filtereddata['chart41'],
            'chart42'=>$filtereddata['chart42'],
            'chart43'=>$filtereddata['chart43'],
        ])->setPaper('a4')->setOrientation('landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'enable-javascript' => true,
            'javascript-delay' => 5000,
            'no-stop-slow-scripts' => true
        ]);

        $pdf->setOption('footer-right', '(Page [page])');
        $pdf->setOption('footer-html', "<!DOCTYPE html><head><script>
		function subst() {
		  var vars={};
		  var x=document.location.search.substring(1).split('&');
		  for (var i in x) {var z=x[i].split('=',2);vars[z[0]] = unescape(z[1]);}
		  var x=['frompage','topage','page','webpage','section','subsection','subsubsection'];
		  for (var i in x) {
		    var y = document.getElementsByClassName(x[i]);
		    for (var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];
		  }
		}
		</script></head><body style='border:0; margin: 0;' onload='subst()'>
		<img src='".asset('/Omsi_footer.png')."'/>
		<table style='border-bottom: 1px solid black; width: 100%'>
		  <tr>
		    <td class='section'></td>
		    <td style='text-align:right'>
		      Page <span class='page'></span> of <span class='topage'></span>
		    </td>
		  </tr>
		</table>
		</body></html>");
        $pdf->setOption('margin-top', 20);
        $pdf->setOption('margin-bottom', 30);
        return $pdf->download('Regional Report '.ucfirst($region).' - '.$file_date.'.pdf');
        // return $pdf->inline();
    }
    /**
     * Export the Regional report.
     *
     * @return Response
     */
    public function regionmis_report($region)
    {
         $validator = Validator::make(
          [
            'region' => $region,
          ],[
            'region' => 'string|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        // $firstdate= date('d-m-Y',strtotime('-8 days', time()));
        // $lastdate=  date('d-m-Y',strtotime('-1 day', time()));
        $firstdate= "01-08-2018";
        // $firstdate= date('d-m-Y', strtotime('first day of last month', time()));
        $lastdate=  date('d-m-Y', strtotime('last day of last month', time()));
        $daterange = $firstdate."_".$lastdate;
        $pdfname = 'Regional Report -'.ucfirst($region).' - 15-Aug-2018'.'_'.date('d-M-Y', strtotime('last day of last month', time())).'.pdf';

        // dd($firstdate,$lastdate,$daterange,$pdfname);



        // $daterange = date('Y-m-d',strtotime("-30 days")).'_'.date('Y-m-d',strtotime("+1 day"));
        $filtereddata = $this->regiondashboard($region, $daterange);

        // return view('regionmis', [
        //     'region'=>$filtereddata['region'],
        //     'chart_months'=>$filtereddata['chart_months'],
        //     'pending_service_requests'=>$filtereddata['pending_service_requests'],
        //     'pending_enquiry_requests'=>$filtereddata['pending_enquiry_requests'],
        //     'pending_academic_requests'=>$filtereddata['pending_academic_requests'],
        //     'new_customers'=>$filtereddata['new_customers'],
        //     'filterdate'=> $filtereddata['filterdate'],
        //     'chart31'=>$filtereddata['chart31'],
        //     'chart32'=>$filtereddata['chart32'],
        //     'chart33'=>$filtereddata['chart33'],
        //     'chart34'=>$filtereddata['chart34'],
        //     'chart35'=>$filtereddata['chart35'],
        //     'chart36'=>$filtereddata['chart36'],
        //     'chart37'=>$filtereddata['chart37'],
        //     'chart38'=>$filtereddata['chart38'],
        //     'chart39'=>$filtereddata['chart39'],
        //     'chart40'=>$filtereddata['chart40'],
        //     'chart41'=>$filtereddata['chart41'],
        //     'chart42'=>$filtereddata['chart42'],
        //     'chart43'=>$filtereddata['chart43'],
        // ]);

        $pdf = PDF::loadView('regionmis', [
            'daterange'=>$daterange,
            'region'=>$filtereddata['region'],
            'chart_months'=>$filtereddata['chart_months'],
            'filterdate'=> $filtereddata['filterdate'],
            'chart31'=>$filtereddata['chart31'],
            'chart32'=>$filtereddata['chart32'],
            'chart33'=>$filtereddata['chart33'],
            'chart34'=>$filtereddata['chart34'],
            'chart35'=>$filtereddata['chart35'],
            'chart36'=>$filtereddata['chart36'],
            'chart37'=>$filtereddata['chart37'],
            'chart38'=>$filtereddata['chart38'],
            'chart39'=>$filtereddata['chart39'],
            'chart40'=>$filtereddata['chart40'],
            'chart41'=>$filtereddata['chart41'],
            'chart42'=>$filtereddata['chart42'],
            'chart43'=>$filtereddata['chart43'],
        ])->setPaper('a4')->setOrientation('landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'enable-javascript' => true,
            'javascript-delay' => 5000,
            'no-stop-slow-scripts' => true
        ]);

        $pdf->setOption('footer-right', '(Page [page])');
        $pdf->setOption('footer-html', "<!DOCTYPE html><head><script>
        function subst() {
          var vars={};
          var x=document.location.search.substring(1).split('&');
          for (var i in x) {var z=x[i].split('=',2);vars[z[0]] = unescape(z[1]);}
          var x=['frompage','topage','page','webpage','section','subsection','subsubsection'];
          for (var i in x) {
            var y = document.getElementsByClassName(x[i]);
            for (var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];
          }
        }
        </script></head><body style='border:0; margin: 0;' onload='subst()'>
        <img src='".asset('/Omsi_footer.png')."'/>
        <table style='border-bottom: 1px solid black; width: 100%'>
          <tr>
            <td class='section'></td>
            <td style='text-align:right'>
              Page <span class='page'></span> of <span class='topage'></span>
            </td>
          </tr>
        </table>
        </body></html>");
        $pdf->setOption('margin-top', 20);
        $pdf->setOption('margin-bottom', 30);

        // return $pdf->inline();

        $pdfpath = storage_path().'/exports/'.ucfirst($region).'Report'.$daterange.'.pdf';

        if (file_exists($pdfpath)) {
            unlink($pdfpath);
            $pdf->save($pdfpath);
        } else {
            $pdf->save($pdfpath);
        }


        $to_emails = Reportsetting::where('name', 'autoemail_'.$region)->value('to_emails');
        $to_emails = explode(',', $to_emails);
        for ($i=0; $i < sizeof($to_emails); $i++) {
            $to_final[]['email'] = $to_emails[$i];
        }

        $cc_emails = Reportsetting::where('name', 'autoemail_'.$region)->value('cc_emails');
        $cc_emails = explode(',', $cc_emails);
        for ($i=0; $i < sizeof($cc_emails); $i++) {
            $cc_final[]['email'] = $cc_emails[$i];
        }
        Mail::to($to_final)->cc($cc_final)
            ->send(new MisRegional($pdfname, $pdfpath, $region, $daterange));
        unlink($pdfpath);


        return 'success';

        // return $pdf->download('Regional Report '.ucfirst($region).' -'.date('F-Y',strtotime('last month', time())).'.pdf');
        // return $pdf->inline();
    }
    public function regiondashboard($region, $daterange)
    {
        $validator = Validator::make(
          [
            'region' => $region,
          ],[
            'region' => 'string|regex:/^[a-zA-Z\s]*$/',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        if (!in_array($region, ['north','east','south','west','panindia'])) {
            abort(404);
        }
        if ($region=='panindia') {
            $region =  ['north','east','south','west'];
            $region_name = 'panindia';
        } else {
            $region_name = $region;
        }
        if (!isset($daterange)) {
            $daterange = date('Y-m-d', strtotime("-3 months")).'_'.date('Y-m-d', strtotime("today"));
        }
        $filtereddata = (Cache::has('chartsdata_mis_'.$daterange.'_'.$region) ? Cache::get('chartsdata_mis_'.$daterange.'_'.$region) : $this->regioncharts($region, $daterange, $region_name) );
        $filtereddata['total_customers'] = Customers::count();
        //dd($filtereddata['chart33'] ?? []);
        return view('dashboardregion.main', [
            'region'=>$region_name,
            'chart_months'=>$filtereddata['chart_months'],
            'pending_service_requests'=>$filtereddata['pending_service_requests_count'],
            'pending_enquiry_requests'=>$filtereddata['pending_enquiry_requests_count'],
            'pending_academic_requests'=>$filtereddata['pending_academic_requests_count'],
            'new_customers'=>$filtereddata['new_customers'],
            'total_customers'=>$filtereddata['total_customers'],
            'filterdate'=> $filtereddata['filterdate'],
            'chart31'=>$filtereddata['chart31'],
            'chart32'=>$filtereddata['chart32'],
            'chart33'=>$filtereddata['chart33'] ?? [],
            'chart34'=>$filtereddata['chart34'],
            'chart35'=>$filtereddata['chart35'],
            'chart36'=>$filtereddata['chart36'],
            'chart37'=>$filtereddata['chart37'],
            'chart38'=>$filtereddata['chart38'],
            'chart39'=>$filtereddata['chart39'],
            'chart40'=>$filtereddata['chart40'],
            'chart41'=>$filtereddata['chart41'],
            'chart42'=>$filtereddata['chart42'],
            'chart43'=>$filtereddata['chart43'],
        ]);
    }
    // Hereeeeeeeeeeeeeeee
    public function regioncharts($region, $daterange, $region_name)
    {
        $validator = Validator::make(
            [
                'region' => $region,
                'region_name' => $region_name,
            ],[
                'region' => 'string|regex:/^[a-zA-Z\s]*$/',
                'region_name' => 'string|regex:/^[a-zA-Z\s]*$/',
            ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        // Recreate date format
        $date_from = new Carbon(explode("_", $daterange)[0]);
        $date_to = new Carbon(explode("_", $daterange)[1]);
        $date_to1 = new Carbon(explode("_", $daterange)[1]);

        $datet1 = explode("-", explode("_", $daterange)[0]);
        $datet2 = explode("-", explode("_", $daterange)[1]);
        $filterdate = $datet1[2].'-'.$datet1[1].'-'.$datet1[0].'_'.$datet2[2].'-'.$datet2[1].'-'.$datet2[0];

        $indian_all_states  = \Config('oly.indian_all_states');

        $services_obj = ServiceRequests::where('service_requests.is_practice', false)
        ->whereBetween('service_requests.created_at', [$date_from, $date_to])
        ->with('hospital')
        ->get();

        foreach ($services_obj as $key) {
            $hospital_state = $key->hospital->state;
            $service_region = find_region($hospital_state);
            $key->region = $service_region;
        }
        $services_obj = $services_obj->whereIn('region', $region);

        $customers_obj = Customers::whereBetween('created_at', [$date_from, $date_to])
            ->where('email', 'NOT LIKE', '%olympus-ap.com%')
            ->get();
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
        $customers_obj = $customers_obj->whereIn('region', $region);
        $dept_obj = Departments::get();

        $period = CarbonPeriod::create($date_from->firstOfMonth(), '1 month', $date_to);
        foreach ($period as $dt) {
            $chart_months[] = $dt->format("M-y");
            $months12[] = $dt->format("m");
        }
        $chart_months = array_reverse($chart_months);
        $months12 = array_reverse($months12);
        $months_count = count($months12);

        $pending_service_requests_count = $services_obj->where('request_type', 'service')->where('status', 'Received')->whereIn('region', $region)->count();
        $pending_enquiry_requests_count = $services_obj->where('request_type', 'enquiry')->where('status', 'Received')->whereIn('region', $region)->count();
        $pending_academic_requests_count = $services_obj->where('request_type', 'academic')->where('status', 'Received')->whereIn('region', $region)->count();
        $new_customers = $customers_obj->whereIn('region', $region)->count();




        // Start generating charts here
        // Chart 31 Data
        $serreq_count = $services_obj->where('request_type', 'service')->whereIn('region', $region)->count();
        $enqreq_count = $services_obj->where('request_type', 'enquiry')->whereIn('region', $region)->count();
        $acadreq_count = $services_obj->where('request_type', 'academic')->whereIn('region', $region)->count();
        $chart31 = [
            'Service'=>$serreq_count,
            'Enquiry'=>$enqreq_count,
            'Academic'=>$acadreq_count
        ];

        // Chart 32 Data
        $chart32 = [
            'Service'=>[
                $services_obj->where('request_type', 'service')->where('dept_id', 1)->whereIn('region', $region)->count(), //Gastroenterology
                $services_obj->where('request_type', 'service')->where('dept_id', 2)->whereIn('region', $region)->count(), //Respiratory
                $services_obj->where('request_type', 'service')->where('dept_id', 3)->whereIn('region', $region)->count(), //General Surgery
                $services_obj->where('request_type', 'service')->where('dept_id', 4)->whereIn('region', $region)->count(), //Urology
                $services_obj->where('request_type', 'service')->where('dept_id', 5)->whereIn('region', $region)->count(), //Gynaecology
                $services_obj->where('request_type', 'service')->where('dept_id', 6)->whereIn('region', $region)->count(), //ENT
                $services_obj->where('request_type', 'service')->where('dept_id', 7)->whereIn('region', $region)->count(), //Others
                $services_obj->where('request_type', 'service')->where('dept_id', 8)->whereIn('region', $region)->count(), //BioMedical
            ],
            'Enquiry'=>[
                $services_obj->where('request_type', 'enquiry')->where('dept_id', 1)->whereIn('region', $region)->count(), //Gastroenterology
                $services_obj->where('request_type', 'enquiry')->where('dept_id', 2)->whereIn('region', $region)->count(), //Respiratory
                $services_obj->where('request_type', 'enquiry')->where('dept_id', 3)->whereIn('region', $region)->count(), //General Surgery
                $services_obj->where('request_type', 'enquiry')->where('dept_id', 4)->whereIn('region', $region)->count(), //Urology
                $services_obj->where('request_type', 'enquiry')->where('dept_id', 5)->whereIn('region', $region)->count(), //Gynaecology
                $services_obj->where('request_type', 'enquiry')->where('dept_id', 6)->whereIn('region', $region)->count(), //ENT
                $services_obj->where('request_type', 'enquiry')->where('dept_id', 7)->whereIn('region', $region)->count(), //Others
                $services_obj->where('request_type', 'enquiry')->where('dept_id', 8)->whereIn('region', $region)->count(), //BioMedical
            ],
            'Academic'=>[
                $services_obj->where('request_type', 'academic')->where('dept_id', 1)->whereIn('region', $region)->count(), //Gastroenterology
                $services_obj->where('request_type', 'academic')->where('dept_id', 2)->whereIn('region', $region)->count(), //Respiratory
                $services_obj->where('request_type', 'academic')->where('dept_id', 3)->whereIn('region', $region)->count(), //General Surgery
                $services_obj->where('request_type', 'academic')->where('dept_id', 4)->whereIn('region', $region)->count(), //Urology
                $services_obj->where('request_type', 'academic')->where('dept_id', 5)->whereIn('region', $region)->count(), //Gynaecology
                $services_obj->where('request_type', 'academic')->where('dept_id', 6)->whereIn('region', $region)->count(), //ENT
                $services_obj->where('request_type', 'academic')->where('dept_id', 7)->whereIn('region', $region)->count(), //Others
                $services_obj->where('request_type', 'academic')->where('dept_id', 8)->whereIn('region', $region)->count(), //BioMedical
            ]
        ];

        // Chart 33
        $chart_33 = $chart33 = [];
        foreach (["service","academic","enquiry"] as $type) {
            for ($month=0; $month < count($months12); $month++) {
                $chart_33[$type][$month]=0;
            }
        }
        foreach ($services_obj as $request) {
            $month = array_search ($request->created_at->format("M-y"), $chart_months);
            if(strlen($month)){
                //$chart_33[$request->request_type][$month]++;
            }
        }
        for ($i=0; $i < count($months12); $i++) {
            $chart33['Service'][$i] = $chart_33["service"][$i];
            $chart33['Academic'][$i] = $chart_33["academic"][$i];
            $chart33['Enquiry'][$i] = $chart_33["enquiry"][$i];
        }
        // Chart 33

        // Chart 34
        $chart_34 = $chart34 = [];
        foreach (["Gastroenterology","Respiratory","GeneralSurgery","Urology","Gynaecology","ENT","Others","BioMedical"] as $type) {
            for ($month=0; $month < count($months12); $month++) {
                $chart_34[$type][$month]=0;
            }
        }
        foreach ($services_obj as $request) {
            $month = array_search ($request->created_at->format("M-y"), $chart_months);
            if(strlen($month)){
                $dept = $dept_obj[$request->dept_id-1]->name;
                $chart_34[str_replace(' ', '', $dept)][$month]++;
            }
        }
        for ($i=0; $i < count($months12); $i++) {
            $chart34['Gastroenterology'][$i] = $chart_34["Gastroenterology"][$i];
            $chart34['Respiratory'][$i] = $chart_34["Respiratory"][$i];
            $chart34['General Surgery'][$i] = $chart_34["GeneralSurgery"][$i];
            $chart34['Urology'][$i] = $chart_34["Urology"][$i];
            $chart34['Gynaecology'][$i] = $chart_34["Gynaecology"][$i];
            $chart34['ENT'][$i] = $chart_34["ENT"][$i];
            $chart34['Other'][$i] = $chart_34["Others"][$i];
            $chart34['BioMedical'][$i] = $chart_34["BioMedical"][$i];
        }
        // Chart 34


        // Chart 35 Data
        $chart35['Received'] = $chart35['Assigned'] = $chart35['Under_Repair'] = $chart35['Closed'] = $chart35['Escalated'] = 0;
        foreach ($services_obj as $request) {
            if (!empty($request->status)) {
                $current_status = $request->status;
                if ($current_status == 'Assigned' || $current_status == 'Re-assigned' || $current_status == 'Attended') {
                    $chart35['Assigned']++;
                } elseif ($current_status == 'Received_At_Repair_Center' || $current_status == 'Quotation_Prepared' || $current_status == 'PO_Received' || $current_status == 'Repair_Started' || $current_status == 'Repair_Completed' || $current_status == 'Ready_To_Dispatch' || $current_status == 'Dispatched') {
                    $chart35['Under_Repair']++;
                } else {
                    $chart35[$current_status]++;
                }
            }else{
                //dd($request->id);
            }
        }

        // Chart 36 Data
        $chart36 = [
            'Received'=>[
                $services_obj->where('dept_id', 1)->where('status', 'Received')->count(), //Gastroenterology
                $services_obj->where('dept_id', 2)->where('status', 'Received')->count(), //Respiratory
                $services_obj->where('dept_id', 3)->where('status', 'Received')->count(), //General Surgery
                $services_obj->where('dept_id', 4)->where('status', 'Received')->count(), //Urology
                $services_obj->where('dept_id', 5)->where('status', 'Received')->count(), //Gynaecology
                $services_obj->where('dept_id', 6)->where('status', 'Received')->count(), //ENT
                $services_obj->where('dept_id', 7)->where('status', 'Received')->count(), //Others
                $services_obj->where('dept_id', 8)->where('status', 'Received')->count(), //BioMedical
            ],
            'Assigned'=>[
                $services_obj->where('dept_id', 1)->whereIn('status', ['Assigned','Re-assigned','Attended'])->count(), //Gastroenterology
                $services_obj->where('dept_id', 2)->whereIn('status', ['Assigned','Re-assigned','Attended'])->count(), //Respiratory
                $services_obj->where('dept_id', 3)->whereIn('status', ['Assigned','Re-assigned','Attended'])->count(), //General Surgery
                $services_obj->where('dept_id', 4)->whereIn('status', ['Assigned','Re-assigned','Attended'])->count(), //Urology
                $services_obj->where('dept_id', 5)->whereIn('status', ['Assigned','Re-assigned','Attended'])->count(), //Gynaecology
                $services_obj->where('dept_id', 6)->whereIn('status', ['Assigned','Re-assigned','Attended'])->count(), //ENT
                $services_obj->where('dept_id', 7)->whereIn('status', ['Assigned','Re-assigned','Attended'])->count(), //Others
                $services_obj->where('dept_id', 8)->whereIn('status', ['Assigned','Re-assigned','Attended'])->count(), //BioMedical
            ],
            'Under_Repair'=>[
                $services_obj->where('dept_id', 1)->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->count(), //Gastroenterology
                $services_obj->where('dept_id', 2)->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->count(), //Respiratory
                $services_obj->where('dept_id', 3)->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->count(), //General Surgery
                $services_obj->where('dept_id', 4)->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->count(), //Urology
                $services_obj->where('dept_id', 5)->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->count(), //Gynaecology
                $services_obj->where('dept_id', 6)->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->count(), //ENT
                $services_obj->where('dept_id', 7)->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->count(), //Others
                $services_obj->where('dept_id', 8)->whereIn('status', ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'])->count(), //BioMedical
            ],
            'Closed'=>[
                $services_obj->where('dept_id', 1)->where('status', 'Closed')->count(), //Gastroenterology
                $services_obj->where('dept_id', 2)->where('status', 'Closed')->count(), //Respiratory
                $services_obj->where('dept_id', 3)->where('status', 'Closed')->count(), //General Surgery
                $services_obj->where('dept_id', 4)->where('status', 'Closed')->count(), //Urology
                $services_obj->where('dept_id', 5)->where('status', 'Closed')->count(), //Gynaecology
                $services_obj->where('dept_id', 6)->where('status', 'Closed')->count(), //ENT
                $services_obj->where('dept_id', 7)->where('status', 'Closed')->count(), //Others
                $services_obj->where('dept_id', 8)->where('status', 'Closed')->count(), //BioMedical
            ]
        ];

        // Chart 37
        $chart37_Dr = $chart37_Mr = $chart37_Ms = 0;
        foreach ($customers_obj as $customer) {
            if ($customer['title'] == 'Dr.') {
                $chart37_Dr++;
            }
            if ($customer['title'] == 'Mr.') {
                $chart37_Mr++;
            }
            if ($customer['title'] == 'Ms.') {
                $chart37_Ms++;
            }
        }
        $chart37 = [
            'Dr'=>$chart37_Dr,
            'Mr'=>$chart37_Mr,
            'Ms'=>$chart37_Ms
        ];
        // Chart 37

        // Chart 38
        $chart_38 = $chart38 = [];
        foreach (["Dr","Mr","Ms"] as $type) {
            for ($month=0; $month < count($months12); $month++) {
                $chart_38[$type][$month]=0;
            }
        }
        foreach ($customers_obj as $customer) {
            $month = array_search ($customer->created_at->format("M-y"), $chart_months);
            if(strlen($month)){
                $chart_38[str_replace('.', '', $customer->title)][$month]++;
            }
        }
        for ($i=0; $i < count($months12); $i++) {
            $chart38['Dr'][$i] = $chart_38["Dr"][$i];
            $chart38['Mr'][$i] = $chart_38["Mr"][$i];
            $chart38['Ms'][$i] = $chart_38["Ms"][$i];
        }
        // Chart 38


        // Chart 39
        $Hospitals=Hospitals::whereBetween('created_at', [$date_from, $date_to1])->whereIn('customer_id', Customers::select('id')->get()->toArray())->select('id', 'dept_id', 'state')->get()->all();
        $Departments = [];
        $dept_north = [];
        $dept_east = [];
        $dept_south = [];
        $dept_west = [];
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
        $chart6_north = array_count_values($dept_north);
        $chart6_east = array_count_values($dept_east);
        $chart6_south = array_count_values($dept_south);
        $chart6_west = array_count_values($dept_west);
        for ($i=1; $i <= 8; $i++) {
            if (!array_key_exists($i, $chart6_north)) {
                $chart6_north[$i] = 0;
            }
            if (!array_key_exists($i, $chart6_east)) {
                $chart6_east[$i] = 0;
            }
            if (!array_key_exists($i, $chart6_south)) {
                $chart6_south[$i] = 0;
            }
            if (!array_key_exists($i, $chart6_west)) {
                $chart6_west[$i] = 0;
            }
        }

        $chart39 = [];
        for ($i=1; $i <= 8; $i++) {
            if ($region_name != 'panindia') {
                $chart39[$i] = ${"chart6_".$region_name}[$i];
            } else {
                $chart39[$i] = $chart6_north[$i] + $chart6_east[$i] + $chart6_south[$i] + $chart6_west[$i];
            }
        }
        // Chart 39



        // Chart 40
        $escalated_count40 = [];
        $notescalated_count40 = [];
        $request_types = ['academic', 'enquiry', 'service'];
        foreach ($request_types as $request) {
            $data = $services_obj->where('request_type', $request);
            $esc_count = 0;
            $not_esc_count = 0;
            foreach ($data as $request1) {
                $esc_status = Timeline::where('request_id', $request1['id'])->where('status', 'Escalated')->count();
                if (!empty($esc_status) && $esc_status >0) {
                    $esc_count = $esc_count+1;
                } else {
                    $not_esc_count = $not_esc_count+1;
                }
            }
            array_push($escalated_count40, $esc_count);
            array_push($notescalated_count40, $not_esc_count);
        }
        $chart40 = [
            'Escalated'=>$escalated_count40,
            'NotEscalated'=>$notescalated_count40,
        ];
        // Chart 40

        // Chart 41
        $escalated_count41= [];
        $notescalated_count41= [];
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
            array_push($escalated_count41, $esc_count);
            array_push($notescalated_count41, $not_esc_count);
        }
        $chart41 = [
            'Escalated'=>$escalated_count41,
            'NotEscalated'=>$notescalated_count41,
        ];


        // Chart42
        // Chart 9 Data
        $chart42_academic = $services_obj->where('request_type', 'academic')->all();
        $chart42_enquiry = $services_obj->where('request_type', 'enquiry')->all();
        $chart42_service = $services_obj->where('request_type', 'service')->all();

        $r2a = [];
        $types = [$chart42_academic, $chart42_enquiry, $chart42_service];
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
        $chart42 = [
            'r2a'=> $r2a
        ];

        // Chart 43
        $chart43_Gastroenterology_1 = $chart43_Gastroenterology_2 = $chart43_Gastroenterology_3 = $chart43_Gastroenterology_4 = $chart43_Respiratory_1 = $chart43_Respiratory_2 = $chart43_Respiratory_3 = $chart43_Respiratory_4 = $chart43_GeneralSurgery_1 = $chart43_GeneralSurgery_2 = $chart43_GeneralSurgery_3 = $chart43_GeneralSurgery_4 = $chart43_Urology_1 = $chart43_Urology_2 = $chart43_Urology_3 = $chart43_Urology_4 = $chart43_Gynaecology_1 = $chart43_Gynaecology_2 = $chart43_Gynaecology_3 = $chart43_Gynaecology_4 = $chart43_ENT_1 = $chart43_ENT_2 = $chart43_ENT_3 = $chart43_ENT_4 = $chart43_Others_1 = $chart43_Others_2 = $chart43_Others_3 = $chart43_Others_4 = $chart43_BioMedical_1 = $chart43_BioMedical_2 = $chart43_BioMedical_3 = $chart43_BioMedical_4 = $chart43_Average_1 = $chart43_Average_2 = $chart43_Average_3 = $chart43_Average_4 = array();

        foreach ($services_obj as $request) {
            if (!is_null($request->feedback_id)) {
                $feedback = Feedback::where('id', $request->feedback_id)->get();
                $feedback = $feedback[0];
                $dept = str_replace(' ', '', $dept_obj[$request->dept_id-1]->name);

                array_push($chart43_Average_1, $feedback['response_speed']);
                array_push(${'chart43_'.$dept.'_1'}, $feedback['response_speed']);
                array_push($chart43_Average_2, $feedback['quality_of_response']);
                array_push(${'chart43_'.$dept.'_2'}, $feedback['quality_of_response']);
                array_push($chart43_Average_3, $feedback['app_experience']);
                array_push(${'chart43_'.$dept.'_3'}, $feedback['app_experience']);
                array_push($chart43_Average_4, $feedback['olympus_staff_performance']);
                array_push(${'chart43_'.$dept.'_4'}, $feedback['olympus_staff_performance']);
            }
        }

        $chart43 = [
            'Gastroenterology' =>[
                calculate_ratio($chart43_Gastroenterology_1),
                calculate_ratio($chart43_Gastroenterology_2),
                calculate_ratio($chart43_Gastroenterology_3),
                calculate_ratio($chart43_Gastroenterology_4)
            ],
            'Respiratory' =>[
                calculate_ratio($chart43_Respiratory_1),
                calculate_ratio($chart43_Respiratory_2),
                calculate_ratio($chart43_Respiratory_3),
                calculate_ratio($chart43_Respiratory_4)
            ],
            'GeneralSurgery' =>[
                calculate_ratio($chart43_GeneralSurgery_1),
                calculate_ratio($chart43_GeneralSurgery_2),
                calculate_ratio($chart43_GeneralSurgery_3),
                calculate_ratio($chart43_GeneralSurgery_4)
            ],
            'Urology' =>[
                calculate_ratio($chart43_Urology_1),
                calculate_ratio($chart43_Urology_2),
                calculate_ratio($chart43_Urology_3),
                calculate_ratio($chart43_Urology_4)
            ],
            'Gynaecology' =>[
                calculate_ratio($chart43_Gynaecology_1),
                calculate_ratio($chart43_Gynaecology_2),
                calculate_ratio($chart43_Gynaecology_3),
                calculate_ratio($chart43_Gynaecology_4)
            ],
            'ENT' =>[
                calculate_ratio($chart43_ENT_1),
                calculate_ratio($chart43_ENT_2),
                calculate_ratio($chart43_ENT_3),
                calculate_ratio($chart43_ENT_4)
            ],
            'Other' =>[
                calculate_ratio($chart43_Others_1),
                calculate_ratio($chart43_Others_2),
                calculate_ratio($chart43_Others_3),
                calculate_ratio($chart43_Others_4)
            ],
            'BioMedical' =>[
                calculate_ratio($chart43_BioMedical_1),
                calculate_ratio($chart43_BioMedical_2),
                calculate_ratio($chart43_BioMedical_3),
                calculate_ratio($chart43_BioMedical_4)
            ],
            'Average' =>[
                calculate_ratio($chart43_Average_1),
                calculate_ratio($chart43_Average_2),
                calculate_ratio($chart43_Average_3),
                calculate_ratio($chart43_Average_4)
            ]
        ];
        // Chart 43



        $filtereddata = [
            'filterdate'=>$filterdate,
            'chart_months'=>$chart_months,
            'pending_service_requests_count'=>$pending_service_requests_count,
            'pending_enquiry_requests_count'=>$pending_enquiry_requests_count,
            'pending_academic_requests_count'=>$pending_academic_requests_count,
            'new_customers'=>$new_customers,
            'chart31'=>$chart31,
            'chart32'=>$chart32,
            'chart33'=>$chart33,
            'chart34'=>$chart34,
            'chart35'=>$chart35,
            'chart36'=>$chart36,
            'chart37'=>$chart37,
            'chart38'=>$chart38,
            'chart39'=>$chart39,
            'chart40'=>$chart40,
            'chart41'=>$chart41,
            'chart42'=>$chart42,
            'chart43'=>$chart43,
        ];
        Cache::put('chartsdata_mis_'.$daterange.'_'.$region, $filtereddata, 20);
        return $filtereddata;
    }
};
