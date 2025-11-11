<?php


use App\Filament\Resources\ArchiveServiceRequests\ArchiveServiceRequestsResource;
use App\Filament\Resources\ServiceRequests\ServiceRequestsResource;
use Illuminate\Support\Facades\Route;
//use App\Filament\Resources\ServiceRequests\Pages\ServiceRequestViewPage;
use App\Http\Controllers\NewReportsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\AdditionalController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
     return abort(404);
});

//Route::get('/admin/service-requests/{record}/edit', [ServiceRequestsResource::class, 'edit'])->name('filament.resources.service-requests.edit');
//Route::get('/admin/archive-service-requests/{record}/edit', [ArchiveServiceRequestsResource::class, 'edit'])->name('filament.resources.archive-service-requests.edit');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|

*/
// use Illuminate\Http\Request;
//To hash the password

Route::get('/pendingtoday/report', [NewReportsController::class, 'pending_requests_report']);
Route::get('/pendingweeklate/report', [NewReportsController::class, 'pending_weeklate_report']);
Route::get('/updateNotification', [AdditionalController::class, 'notifyToUpdate']);

// Customer Monthly Reports
Route::get('/customermonthly', [NewReportsController::class, 'monthly_customer_all']);
Route::get('/customermonthly/{region}', [NewReportsController::class, 'monthly_customer_region']);

// Feedback Monthly Reports
Route::get('/feedbackmonthly/report', [NewReportsController::class, 'monthly_report_feedback']);
Route::get('/feedbackmonthly/report/{region}', [NewReportsController::class, 'monthly_report_feedback_regional']);

// MIS Reports
Route::get('/mis', [HomeController::class, 'mis_report'])->name('mis');
Route::get('/mis/{region}', [HomeController::class, 'regionmis_report'])->name('regionmis');

// Weekly Reports
Route::get('/weeklyescalation/report', [NewReportsController::class, 'weekly_escalations_report']);
Route::get('/weeklymis', [NewReportsController::class, 'weekly_report_all'])->name('weekly_report');
Route::get('/weeklymis/{region}', [NewReportsController::class, 'weekly_report_region']);



/*----------------------------------Extra Commented Routes Start-------------------------------------*/
///*
//Route::get("data-check",function(){
//    $data = ServiceRequests::Join('status_timelines','service_requests.id','status_timelines.request_id')->whereBetween('service_requests.created_at', ['2022-01-01 00:00:00', '2022-05-31 23:59:59'])->OrderBy('status_timelines.request_id','asc')->select('status_timelines.request_id', 'service_requests.request_type','status_timelines.status','status_timelines.created_at')->groupBy('status_timelines.request_id')->get();
//    foreach($data as $datas){
//        $received  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'received'])->pluck('created_at')->first();
//        if(!empty($received) || $received){
//            $datas->received = $received->format('d-m-y');
//        }else{
//            $datas->received = '';
//        }
//        $assigned  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'assigned'])->pluck('created_at')->first();
//        if(!empty($assigned) || $assigned){
//            $datas->assigned = $assigned->format('d-m-y');
//        }else{
//            $datas->assigned = '';
//        }
//        $attended  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'attended'])->pluck('created_at')->first();
//        if(!empty($attended) || $attended){
//            $datas->attended = $attended->format('d-m-y');
//        }else{
//            $datas->attended = '';
//        }
//        $quotation_prepared  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'Quotation_Prepared'])->pluck('created_at')->first();
//        if(!empty($quotation_prepared) || $quotation_prepared){
//            $datas->quotation_prepared = $quotation_prepared->format('d-m-y');
//        }else{
//            $datas->quotation_prepared = '';
//        }
//        $po_received  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'PO_Received'])->pluck('created_at')->first();
//        if(!empty($po_received) || $po_received){
//            $datas->po_received = $po_received->format('d-m-y');
//        }else{
//            $datas->po_received = '';
//        }
//        $dispatched  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'dispatched'])->pluck('created_at')->first();
//        if(!empty($dispatched) || $dispatched){
//            $datas->dispatched = $dispatched->format('d-m-y');
//        }else{
//            $datas->dispatched = '';
//        }
//        $received_at_repair_center  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'received_at_repair_center'])->pluck('created_at')->first();
//        if(!empty($received_at_repair_center) || $received_at_repair_center){
//            $datas->received_at_repair_center = $received_at_repair_center->format('d-m-y');
//        }else{
//            $datas->received_at_repair_center = '';
//        }
//        $repair_started  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'repair_started'])->pluck('created_at')->first();
//        if(!empty($repair_started) || $repair_started){
//            $datas->repair_started = $repair_started->format('d-m-y');
//        }else{
//            $datas->repair_started = '';
//        }
//
//        $repair_completed  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'repair_completed'])->pluck('created_at')->first();
//        if(!empty($repair_completed) || $repair_completed){
//            $datas->repair_completed = $repair_completed->format('d-m-y');
//        }else{
//            $datas->repair_completed = '';
//        }
//
//        $ready_to_dispatch  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'ready_to_dispatch'])->pluck('created_at')->first();
//        if(!empty($ready_to_dispatch) || $ready_to_dispatch){
//            $datas->ready_to_dispatch = $ready_to_dispatch->format('d-m-y');
//        }else{
//            $datas->ready_to_dispatch = '';
//        }
//
//        $closed  = StatusTimeline::where(['request_id'=>$datas->request_id, 'status' => 'closed'])->pluck('created_at')->first();
//        if(!empty($closed) || $closed){
//            $datas->closed = $closed->format('d-m-y');
//        }else{
//            $datas->closed = '';
//        }
//    }
//    return(new FastExcel($data))->download('file.xlsx', function ($new_data) {
//        return [
//            'Request Id' => $new_data->request_id,
//            'Request Type' => $new_data->request_type,
//            'Received'=>$new_data->received,
//            'Assigned'=>$new_data->assigned,
//            'Attended'=>$new_data->attended,
//            'Received_At_Repair_Center'=>$new_data->received_at_repair_center,
//            'Quotation_Prepared'=>$new_data->quotation_prepared,
//            'PO_Received'=>$new_data->po_received,
//            'Repair_Started'=>$new_data->repair_started,
//            'Repair_Completed'=>$new_data->repair_completed,
//            'Ready_To_Dispatch'=>$new_data->ready_to_dispatch,
//            'Dispatched'=>$new_data->dispatched,
//            'Closed'=>$new_data->closed,
//        ];
//    });
//});
//
//// Route::get("test1", 'ReportsController@aa');
//
///*Route::get('test', function(){
//    $counter = 1;
//    $ids = [15317,13274,15337,15320,14076,15181,14666,15222,14903,14303,14721,13835,15058,14410,10979,11845,14168,14318,14816,15188,9332,14763,13676,14166,14815,15258,15215,15101,15090,14953,15039,14498,14764,14997,14561,14310,15026,14921,14433,13473,6003,14083,14193,15229,14854,15045,14994,14945,14571,13734,13913,14685,15206,14861,15321,15169,15053,14898,5719,9942,15034,14938,13688,14874,14334,14393,15359,15175,9755,15355,15299,13241,15140,14456,15191,15093,14409,13565,14518,14278,14794,13787,15000,15333,15096,12165,14983,12065,15151,14071,15008,13301,15021,11726,15227,12952,14870,14703,15141,15361,11727,15205,15297,13721,14933,15014,15374,14463,15287,13280,15346,15349,15336,15331,14856,15335,15282,15228,13479,15323,13872,15308,15309,11027,15080,15079,14922,15298,15286,15289,15293,9688,15210,9996,15267,15262,15235,14982,14968,15245,14723,15237,15234,15231,15220,14767,15221,15194,15154,14081,15077,13471,13477,13475,15197,14014,14791,14905,14008,14520,15163,14836,15170,15160,15131,15067,15117,15143,15017,15108,14737,15139,14955,15103,15111,15116,15095,15126,15123,15046,14002,14228,15072,14975,14884,15048,15041,15027,15009,15031,15013,14832,14889,14985,14455,14993,14600,14962,14937,14969,14971,14920,14775,14773,14784,13842,14943,14881,14487,14918,14741,14924,14469,14587,14908,14894,14875,14857,14264,14470,14827,14818,14757,14696,14842,14747,14746,14374,14552,12807,14822,14287,14023,13674,14407,14817,14771,14778,14800,14788,14745,14796,14539,14720,14792,14500,14783,14740,14758,14642,14686,14744,14695,14358,14743,14284,14734,14224,12323,14731,14728,14694,14704,14702,14701,14700,14681,14058,14692,14635,14680,14590,14652,14548,14632,12860,14628,14627,14629,14607,14596,14609,14553,14599,14564,14591,13918,13959,14035,14544,14580,14532,14554,14567,14566,14531,14507,14493,14513,14516,12810,14502,14457,14282,14283,14501,14420,14509,14489,14496,14478,14411,14482,14476,14397,14445,14426,14438,14051,14001,14401,14419,14402,14396,14099,14184,14365,14368,11886,14371,13113,14265,14332,14353,14349,14341,14340,14333,14325,14306,14299,14262,14263,13203,14279,14274,14286,14290,14269,14277,14258,14260,14217,14256,12094,14230,14203,13177,14244,14229,14223,14165,13114,14198,13193,11689,13017,14124,14129,14136,14117,14112,13349,13793,14029,13996,13424,14106,13413,13855,13767,14101,14098,13921,14103,14089,14027,14077,14080,14067,14034,14052,14028,14031,14005,14006,13998,13962,13947,13978,13849,13923,13817,13718,13934,13784,13707,13900,13910,13874,13875,13864,13857,13852,13836,13808,13780,13783,13774,13753,13751,13755,13157,12553,12308,13531,13344,13499,12834,12817,13745,13736,13729,11652,13722,13724,13679,13682,13639,13645,13635,13562,13620,13260,13612,13613,13014,13570,13600,13594,13558,13544,13557,13541,13545,13532,13359,13550,13461,13469,13524,13512,13358,13503,13319,13435,13470,13420,13464,13224,13446,13406,13429,13419,13433,13408,13194,13414,13403,13385,13351,11766,13071,12651,12112,9774,12054,13331,13330,13146,13222,12612,12271,12861,12628,11913,12960,13261,13249,13204,13234,13229,13217,13106,12622,12959,12900,12922,12790,12603,12708,13063,13225,13218,13190,13184,13216,13185,13175,13189,13166,13153,13176,13141,13165,13169,13164,13064,12186,13149,13046,13124,13086,13097,13096,13107,13105,13111,13088,13089,13095,13093,13087,13058,13076,13061,13050,13034,13036,13037,12772,12884,12326,13012,12996,13007,12978,12993,12994,12976,12895,12187,12446,12539,12926,12919,12441,12924,12282,12034,12406,12354,12098,11856,12585,12764,12913,12912,12898,12905,12876,12891,12859,12867,12866,12853,12851,12832,12847,12349,12762,12789,12801,12800,12794,11795,12172,12066,12760,12614,12301,12256,12757,12732,12725,12754,12739,12729,12559,12716,12213,12699,12653,12695,12692,12677,12609,12639,12643,12641,12640,12588,12582,12607,12615,10652,11157,12580,12549,11873,11226,12550,12558,12555,12545,12517,12516,12193,12530,12488,12427,12513,12519,11823,12023,12489,12502,12493,12415,12447,12492,12452,12486,12483,12477,12467,11819,12474,12466,12465,10895,11523,10787,12336,12414,12416,12417,12418,12419,12379,12422,12385,12407,12400,12399,10833,12225,12384,12393,12391,12149,12376,12368,12332,11794,12177,11626,12322,12358,12347,12341,12338,12309,12327,12328,12313,9289,10902,10429,12291,12307,12304,12255,12296,12272,12279,12278,12277,10642,12249,12246,12245,12244,12088,12238,11155,12174,11673,11217,12176,12185,12164,12057,12145,12111,12120,12129,12140,12108,12136,12127,10600,12119,12093,10810,12100,12103,12084,11963,11900,11959,11428,11931,11932,12039,12019,11281,12030,9914,12020,11970,11879,11824,11966,11981,12002,11996,11977,11994,11976,11982,11882,11806,11815,11978,11437,11972,11971,11961,11946,11868,11938,11937,11917,10984,11865,11924,11867,11908,11915,11898,11903,11896,11890,11883,11880,11847,10846,11231,11105,10713,11591,11551,11368,11828,10746,11841,11400,11118,10322,8107,11721,8215,11820,11792,11796,11807,11797,11813,11812,11804,11407,11699,11665,11331,11765,11770,11743,11753,11749,11742,11717,11734,11708,11722,11725,11704,11703,11677,11696,11692,9761,11684,11681,11676,11672,11654,11653,11633,11625,11634,11619,11296,11029,10800,11607,11045,10748,10781,10927,10417,10473,10256,10894,10587,10425,10266,10036,9210,11565,11491,11598,11562,11454,11577,11583,11582,11546,11568,11566,11503,11556,11557,11541,11553,11532,11242,11519,11386,11515,10270,9836,8646,11490,9305,11462,11479,11267,11457,10524,10847,11439,11429,11435,11434,11427,11433,11424,11395,11411,11410,11398,11392,9373,11379,11221,11346,10336,10413,10341,10194,11244,10459,10453,9165,9578,11319,11316,10634,11299,9374,11295,10566,11286,11292,10647,11277,9994,9961,10519,8818,9690,10837,11125,10908,10854,11127,11135,11276,10993,11224,11149,11210,8336,10711,11239,11197,10613,11227,11200,11201,9328,11173,11148,10730,10714,10707,11020,10541,10001,10829,11065,11163,11179,11180,11176,11177,11169,11001,10898,10731,11167,11143,10595,11151,11120,11042,11038,11132,11126,11085,11087,11110,11088,10625,11104,11086,11062,11102,11040,11044,11036,11057,11056,11023,9188,11047,11015,10768,11030,11028,5201,10963,11012,11011,10975,10961,10117,10960,10952,10953,10932,10931,10943,10942,10893,10937,10901,10900,9612,8803,10920,10513,10921,10919,9254,9557,10430,10870,10885,10911,10865,10878,10877,10867,10660,10789,10869,10864,9185,10857,10841,10836,10799,10108,7979,10677,10835,10830,9438,10448,10085,9610,10217,9326,9939,9626,9507,9503,8496,9646,8136,9948,10464,10208,10312,9529,7250,7443,7442,9603,10821,10488,10815,10395,10796,10786,10773,10290,10762,10761,10690,10449,10261,10775,10772,10591,10521,10324,10315,10632,10763,10630,10584,10644,10727,10726,10623,10729,10664,9401,9638,10645,8737,8857,8822,8807,8842,8825,8844,8871,8796,10342,10226,10680,10673,10373,9756,10244,9722,9623,9721,9726,9862,9870,9735,9737,10612,10656,10618,9995,10070,9800,9301,9298,9299,9063,10352,10343,10095,10144,10110,10149,10131,10130,10086,10083,10028,10051,10049,9997,9951,9892,9893,9867,9888,9792,9773,9485,9817,9783,9555,9655,9629,9553,9112,9054,9429,9408,9460,9293,9396,9221,9276,9121,9008,9045,7157,7966,9286,9389,8168,9760,9563,9513,9363,8943,9330,8555,8917,8669,8400,8297,7400,7508,6812,5264,9525,10231,10170,10094,9519,9785,9707,9659,9684,9680,9637,9482,8244,9466,9447,8325,9080,8994,9148,9150,8513,8933,8520,8148,8049,7782,9992,9632,9631,9551,9044,8673,7964,7962,10589,10596,10579,10563,10401,10575,10567,10559,10558,10538,10532,10528,10499,10480,10444,10432,10422,10416,10412,10394,10361,10351,10330,10299,10387,10363,10273,10251,10367,10281,10316,9962,10321,10332,10333,10271,10294,10274,10258,10207,10206,10166,10196,10179,10174,10159,10142,10150,10141,10132,10116,10118,10097,10084,10054,10053,10042,8576,10003,10024,10025,8937,7674,10010,10008,9881,8958,9856,8661,9181,8246,9845,9841,9969,9751,9912,8744,8782,8368,7636,9974,9889,9641,9835,8328,9006,9929,9234,8451,8594,8208,9581,9876,9599,9533,8624,9919,9136,9808,9903,9898,9861,9240,8512,7513,7544,9550,9812,9786,9784,9135,8567,8164,7193,9831,8639,9778,8964,7930,9296,9076,8616,9725,9685,8705,9671,9584,9585,9451,9077,9329,9093,9082,9203,9033,9083,7392,9064,8429,9617,9674,5241,9633,9640,9622,8997,8452,9419,9570,9569,9412,8605,9496,9520,8366,6638,8299,9467,9268,9477,9250,9087,9092,9422,9426,8866,8947,9407,9318,9200,8928,9336,6055,9285,8530,8002,9227,8750,8022,9207,9214,9187,9147,9154,9086,9124,9119,9131,7272,7277,8157,8233,8258,8394,9048,9026,9051,9052,9013,6060,7896,7720,7391,9032,7390,6582,9011,8438,8960,8948,7864,8407,8210,7598,8602,8598,7518,8791,8792,8793,8763,8508,8312,7769,7239,8751,8259,8690,8687,8696,8622,7135,8593,8626,8453,8552,8621,7106,6964,5512,8516,8321,8485,7566,6765,6149,5855,8389,8391,7237,7289,7270,6830,6768,6294,8320,8281,7963,7816,8163,5552,8162,8156,4975,8112,8048,8047,8042,7000,7155,7013,6842,8026,8006,7978,7965,6857,6594,5860,7833,7848,7404,7583,7808,7799,7762,6521,7763,7141,6895,6653,6648,7748,5851,7618,7444,6589,6021,7459,7406,7367,4630,7317,7290,7297,7292,6954,7032,6022,6702,6520,6277,6673,7166,7120,7156,7151,7015,7072,7059,7046,6972,6997,5852,6937,6933,6793,5663,6801,5783,5612,3550,5042,6659,6606,6605,6535,4891,6319,5087,6311,6153,6341,5953,5816,6320,6207,5401,5160,4276,3852,3539,3187,2743,2462,2324,2245,1939,799,1169,311,5943,5313,5337,4633,5126,5134,4464,4739,4469,3906,3498,4106,3896,3682,3341,3407,3611,3319,1675,2416,1256,6082,5970,6007,5771,5158,3122,5640,5410,5210,4461,4795,4540,4533,4321,4354,4256,4307,4278,4216,4253,3694,5509,3547,5275,5220,5144,5013,4940,4927,4848,4808,4777,4706,4576,4071,4396,4296,4051,4086,3965,3692,3574,3602,3625,3542,3548,3426,3455,3151,2953,2860,2632,2640,2452,2474,2549,2353,2445];
//
//    $requests = ServiceRequests::whereIn('id', $ids)->whereNotIn('status', ['Closed'])->get();
//    foreach($requests as $request){
//            echo "$request->id, ";
//            $counter++;
//    }
//});*/
//
//
//// Route::get('/test/{id}', function($id){
////     // 10679,10650,10636,10632,10631,10630,10626,10619
////     // Push requests by ID to SFDC again
////     $service= \App\ServiceRequests::findOrFail($id);
//
////     $hospitals = \App\Hospitals::find($service->hospital_id);
////     $customer = \App\Customers::findOrFail($service->customer_id);
//
//// // dd($service, $hospitals, $customer);
////     $SFDCCreateRequest = \App\SFDC::createRequest($service, $customer, $hospitals, "");
////     if(isset($SFDCCreateRequest->success)){
////         if($SFDCCreateRequest->success == "true" && isset($SFDCCreateRequest->id)){
////             $service->sfdc_id = $SFDCCreateRequest->id;
////             $service->save();
////             return $service->sfdc_id.' Updated';
////         }
////     }else{
////         \Log::info("\n===SFDCCreateRequest Error"."\n\n");
////         \Log::info($SFDCCreateRequest);
////         return $SFDCCreateRequest;
////     }
//
//
//
//
////     $product_types = ["accessory", "capital", "other"];
////     // $requestdata = array(
////     //     'service' => array ('BreakDown Call','Service Support'),
////     //     'academic' => array ('Conference','Clinical Info','Training'),
////     //     'enquiry' => array ('Demonstration','Quotation','Quotations','Product Info'),
////     // );
////     $requestdata = array(
////         'service' => array (''),
////         'academic' => array (''),
////         'enquiry' => $product_types
////         // array ('Demonstration','Quotation','Quotations','Product Info'),
////     );
////     $states = array_keys(\Config('oly.responsible_branches'));
////     // $states1 = ['Haryana', 'Himachal Pradesh', 'Jammu and Kashmir', 'Madhya Pradesh', 'Punjab', 'Rajasthan', 'Uttarakhand', 'Uttaranchal', 'Uttar Pradesh', 'Chandigarh', 'Delhi', 'Chhattisgarh','Arunachal Pradesh', 'Assam', 'Bihar', 'Jharkhand', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Orissa', 'Sikkim', 'Tripura', 'West Bengal','Andhra Pradesh', 'Karnataka', 'Kerala', 'Tamil Nadu', 'Telangana', 'Andaman and Nicobar Islands', 'Lakshadweep', 'Puducherry', 'Pondicherry','Goa', 'Gujarat', 'Maharashtra', 'Dadra and Nagar Haveli', 'Daman and Diu'];
////     // dd(array_diff($states1,$states));
////     $departments = [
////         "1"=>"Gastroenterology",
////         "2"=>"Respiratory",
////         "3"=>"General Surgery",
////         "4"=>"Urology",
////         "5"=>"Gynaecology",
////         "6"=>"ENT",
////         "7"=>"Others",
////         "8"=>"Bio Medical",
////     ];
////     $success = 0;
////     $failed = 0;
////     foreach($requestdata as $type => $subtypes){
////         foreach($subtypes as $subtype){
////             foreach($states as $state){
////                 foreach($departments as $department){
////                     if ($type=='enquiry') {
////                         $rule = \App\AutoEmails::where('request_type', 'enquiry')->where('sub_type', $subtype)->whereRaw("find_in_set('".$state."',states)")->whereRaw("find_in_set('".$department."',departments)")->first();
////                     }else{
////                         $rule = \App\AutoEmails::where('request_type', $type)->whereRaw("find_in_set('".$state."',states)")->whereRaw("find_in_set('".$department."',departments)")->first();
////                     }
////                     if(!is_null($rule)){
////                         $success++;
////                     }else{
////                         $data[] = [
////                             'request_type'=>$type,
////                             'subtype'=>$subtype,
////                             'state'=>$state,
////                             'department'=>$department
////                         ];
////                         $failed++;
////                     }
////                 }
////             }
////         }
////     }
////     return view('delete1',['data'=>$data,'success'=>$success,'failed'=>$failed]);
//// });
//
//// Use if dashboard or charts page is not working
//// Route::get('/findfeedbackerrors', function () {
////     $feedbacks = \App\Feedback::all();
////     foreach($feedbacks as $feedback){
////         $request = \App\ServiceRequests::where('id', $feedback->request_id)->first();
////         if ($request == null) {
////             echo "Request $feedback->request_id not found for Feedback $feedback->id".'<br>';
////         }
////     }
////     $requests = \App\ServiceRequests::all();
////     foreach($requests as $request){
////         if (!is_null($request->feedback_id)) {
////             $feedback = \App\Feedback::where('id', $request->feedback_id)->first();
////             if ($feedback == null) {
////                 echo "Feedback $request->feedback_id not found for Request $request->id".'<br>';
////             }
////         }
////     }
//// });
//
//// Reset a user password
//// Route::get('/resetpassword/{id}', function ($id) {
////         $password = 'password';
////         $user = Customers::where('id', $id)->first();
////         $user->password = Hash::make($password); //hash password
//// });
//
//
//// Bulk delete requests by ID
//// Route::get('/deleterequests', function () {
////     $ids = array("1912","2038","2043","2044","2086","2178","2194");
////     $count = 1;
////     foreach ($ids as $id) {
////         $request = ServiceRequests::where('id', $id)->first();
////         echo $count.' : '.$request->id.'<br>';
////         }
////         $request->delete();
////     }
//// });
//
//// // Use if dashboard or charts page is not working
////         if ($request == null) {
////         }
////     }
////     $requests = \App\ServiceRequests::all();
////     foreach($requests as $request){
////             }
////         }
////     }
//// });
//
//
//
//// // Cleanup hospital table
//// Route::get('/hospitalerrors', function () {
////     // Check if hospital is deleted but used in a request
////     $requests = \App\ServiceRequests::all();
////     foreach($requests as $request){
////         $hospital = \App\Hospitals::where('id', $request->hospital_id)->first();
////         if (is_null($hospital)) {
////             echo "Hospital $request->hospital_id not found for Request $request->id"." Customer: $request->customer_id<br>";
////         }
////     }
////     $hospitals = \App\Hospitals::all();
////     foreach($hospitals as $hospital){
////         $customer = \App\Customers::where('id', $hospital->customer_id)->first();
////         if ($customer == null) {
////             echo "Customer $hospital->customer_id not found for Hospital $hospital->id".'<br>';
////         }
////     }
////     $count = 0;
////     foreach($hospitals as $hospital){
////         $customer = \App\Customers::where('id', $hospital->customer_id)->first();
////         if (!is_null($customer)) {
////             $customer_hospitals = explode(',', $customer->hospital_id);
////             // print_r($customer_hospitals);
////             if(!in_array($hospital->id, $customer_hospitals)){
////                 // if($customer->id == 1629 || $customer->id == 1630 || $customer->id == 2426){
////                     // $hospital->delete();
////                 }
////             // }
////         }
////         $count++;
////     }
////     $customers = \App\Customers::all();
////     foreach($customers as $customer){
////         if (!is_null($customer->hospital_id)) {
////             $hospital_ex = explode(',', $customer->hospital_id);
////             foreach ($hospital_ex as $hospital) {
////                $hospital = \App\Hospitals::where('id', $hospital)->first();
////                 if ($hospital == null) {
////                     echo "Hospital $customer->hospital_id not found for Customer $customer->id".'<br>';
////                 }
////             }
//
////         }
////     }
//
//// });
//
//
//// Find duplicate requests
//// Route::get('/dupes', function () {
////     $service_requests = ServiceRequests::where('id', '>', 4500)->get();
////     $customer_id = "";
////     $remarks = "";
//
////     foreach ($service_requests as $request) {
//
////         // echo $request->id;
////         if ($customer_id == $request->customer_id && $remarks == $request->remarks) {
////             $this_date = \Carbon\Carbon::parse($request->created_at);
////             echo $request->id."/".$notdupe."/".$request->created_at."/".$lastdate->diffInSeconds($this_date)."<br>";
////             // ServiceRequests::find($request->id)->delete();
////         } else {
////             $notdupe = $request->id;
////             $lastdate = $datework = \Carbon\Carbon::parse($request->created_at);
////         }
////         $customer_id = $request->customer_id;
////         $remarks  =$request->remarks;
////     }
//// });
//
//// Bulk create admin users
//// Route::get('/adminss', function () {
////  $data = '[
////   {"Name": "FirstName LastName", "Email": "xyz@olympus.com", "Password": "Ajsdjf"},
////   {"Name": "FirstName LastName", "Email": "xyz@olympus.com", "Password": "Ajsdjf"},
////   {"Name": "FirstName LastName", "Email": "xyz@olympus.com", "Password": "Ajsdjf"},
////   {"Name": "FirstName LastName", "Email": "xyz@olympus.com", "Password": "Ajsdjf"}
//
//// ]';
//// $data = json_decode($data);
////     foreach ($data as $userdata) {
////      echo $userdata->Name."<br>";
////      $user = new User;
////         $user->name = $userdata->Name;
////         $user->email = $userdata->Email;
////         $user->password = Hash::make($userdata->Password); //hash password
////     }
//// });
//
//// Create different permissions using a given string
//// Route::get('/manage', function () {
//// $runfor = '';
////  $owner = new App\Permission();
////  $owner->name         = 'create-'.$runfor;
////  $owner->display_name = 'Can create '.$runfor; // optional
////  $owner->description  = 'User can create '.$runfor.' in this project'; // optional
////  $owner->save();
//
////  $owner = new App\Permission();
////  $owner->name         = 'read-'.$runfor;
////  $owner->display_name = 'Can read '.$runfor; // optional
////  $owner->description  = 'User can read '.$runfor.' in this project'; // optional
////  $owner->save();
//
////  $owner = new App\Permission();
////  $owner->name         = 'update-'.$runfor;
////  $owner->display_name = 'Can update '.$runfor; // optional
////  $owner->description  = 'User can update '.$runfor.' in this project'; // optional
////  $owner->save();
//
////  $owner = new App\Permission();
////  $owner->name         = 'delete-'.$runfor;
////  $owner->display_name = 'Can delete '.$runfor; // optional
////  $owner->description  = 'User can delete '.$runfor.' in this project'; // optional
////  $owner->save();
//// return 'success';
////     // return view('delete');
//// });
//
///*----------------------------------Extra Commented Routes End-------------------------------------*/
//
//Route::get('/request_info/{id}', function($id){
//    $servicerequest = ServiceRequests::find($id);
//    // dd($servicerequest);
//    $hospitals = \App\Hospitals::find($servicerequest->hospital_id);
//    $departments = \App\Departments::find($hospitals->dept_id);
//
//    if ($servicerequest->request_type=='enquiry') {
//        $product_category_arr = explode(',', $servicerequest->product_category);
//        for ($i=0; $i < sizeof($product_category_arr); $i++) {
//            if (trim($product_category_arr[$i])=='Accessory') {
//                $rules_list = \App\AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'accessory')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
//                $to_emails[$i] = explode(',', $rules_list->to_emails);
//                $cc_emails[$i] = explode(',', $rules_list->cc_emails);
//            } elseif (trim($product_category_arr[$i])=='Capital Product') {
//                $rules_list = \App\AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'capital')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
//                $to_emails[$i] = explode(',', $rules_list->to_emails);
//                $cc_emails[$i] = explode(',', $rules_list->cc_emails);
//            } elseif (trim($product_category_arr[$i])=='Other') {
//                $rules_list = \App\AutoEmails::where('request_type', 'enquiry')->where('sub_type', 'other')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
//                $to_emails[$i] = explode(',', $rules_list->to_emails);
//                $cc_emails[$i] = explode(',', $rules_list->cc_emails);
//            }
//        }
//        $to_emails_final['email'] = array_unique(array_flatten($to_emails));
//        $cc_emails_final['email'] = array_unique(array_flatten($cc_emails));
//    } elseif ($servicerequest->request_type=='others') {
//        $rules_list = \App\AutoEmails::where('request_type', 'academic')->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
//        $to_emails_final['email'] = explode(',', $rules_list->to_emails);
//        $cc_emails_final['email'] = explode(',', $rules_list->cc_emails);
//    } else {
//        $rules_list = \App\AutoEmails::where('request_type', $servicerequest->request_type)->whereRaw("find_in_set('".$hospitals->state."',states)")->whereRaw("find_in_set('".$departments->name."',departments)")->first();
//        $to_emails_final['email'] = explode(',', $rules_list->to_emails);
//        $cc_emails_final['email'] = explode(',', $rules_list->cc_emails);
//    }
//    if ($servicerequest->request_type!='service') {
//        $cc_emails_final['email'] = array_merge($cc_emails_final,\Config('oly.enq_acad_coordinator_email'));
//    }
//    $users = collect(array_flatten($to_emails_final['email']))->flatten();
//    $cc = collect(array_flatten($cc_emails_final['email']))->flatten();
//
//    return "<h3>Email matrix when request $id received</h3><br>Request Id: $servicerequest->id<br>".
//        "Request Type: $servicerequest->request_type<br>".
//        "Request Subtype: $servicerequest->sub_type<br>".
//        "Hospital : $hospitals->hospital_name ($hospitals->id - $hospitals->state)<br>".
//        "Department : $departments->id ($departments->name)<br>".
//        "to_emails : ".implode(',',$users->toArray())."<br>".
//        "cc_emails : ".implode(',',$cc->toArray())."<br>";
//});
//
//
//Route::get('/', function () {
//    return view('welcome');
//});
//
//Route::get("/capture_screenshot/{id}/{type}", [ServiceRequestController::class, 'capture_screenshot']);
//Route::get("/capture_screenshot_version_two/{id}/{type}", [ServiceRequestControllerV2::class, 'capture_screenshot_version_two']);
//Route::get("capture_emails/{id}/{type}", [ServiceRequestController::class, 'capture_emails']);
//Route::get("capture_emails_version_two/{id}/{type}", [ServiceRequestControllerV2::class, 'capture_emails_version_two']);
//
//Route::get('/test_notifications/{id}', function ($id) {
//    $servicerequest = ServiceRequests::findOrFail($id);
//    $customer = \App\Customers::findOrFail($servicerequest->customer_id);
//    // NotifyCustomer::send_notification('request_escalate', $servicerequest, $customer);
//    // NotifyCustomer::send_notification('request_update', $servicerequest, $customer);
//    // NotifyCustomer::send_notification('request_type_changed', $servicerequest, $customer);
//    // NotifyCustomer::send_notification('request_technical_report', $servicerequest, $customer);
//    // NotifyCustomer::send_notification('app_update_available', $servicerequest, $customer);
//    // NotifyCustomer::send_notification('request_technical_report', $servicerequest, $customer);
//    // NotifyCustomer::send_notification('request_create', $servicerequest, $customer);
//    // NotifyCustomer::send_notification('account_update', '', $customer);
//    NotifyCustomer::send_notification('promailer_publish', '', $customer);
//});
//
//
//
//
//
//// Auth Routes - Removed Register Route
//Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
//Route::post('login', [LoginController::class, 'login']);
//Route::post('logout', [LoginController::class, 'logout'])->name('logout');
//// Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
//// Route::post('register', 'Auth\RegisterController@register');
//Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
//Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
//Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
//Route::post('password/reset', [ResetPasswordController::class, 'reset']);
//
//Route::get('forget-password', [AdminsController::class, 'showForgetPasswordForm'])->name('forget.password.get');
//Route::post('forget-password', [AdminsController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
//Route::get('reset-password/{token}', [AdminsController::class, 'showResetPasswordForm'])->name('reset.password.get');
//Route::post('reset-password', [AdminsController::class, 'submitResetPasswordForm'])->name('reset.password.post');
//
//// new route for login or password reset
//Route::get('logout', [AdminsController::class, 'logout']);
//Route::post('password/reset_url', [AdminsController::class, 'password_reset']);
//Route::post('login_submit', [AdminsController::class, 'loginSubmit']);
//Route::get('testContent', [TestController::class, 'testContent']);
//
//
//// Temporary disable IP validation for developer
//Route::get('/temp-authorize', [AdditionalController::class, 'khuljasimsim']);
//
//Route::get('/app-redirect', [AdditionalController::class, 'app_redirect']);
//
//
//// Assign Requests
//Route::get('/assign_request/{token}/edit', [AssignRequestsController::class, 'edit']);
//Route::post('/assign_request/{token}/update', [AssignRequestsController::class, 'update']);
//
//
//// Email Paths
//Route::get('/assign_request_mail/{id}', [AssignRequestsController::class, 'sendMail']);
//
//Route::get('/export/feedbackexcel', [FeedbackController::class, 'export']);
//Route::get('/feedbackmonthly/report', [ReportsController::class, 'monthly_report_feedback']);
//Route::get('/feedbackmonthly/report/{region}', [ReportsController::class, 'monthly_report_feedback_regional']);
//
////Reports
//// middleware(['serverinvoked'])->
//
//Route::middleware(['prevent-back-history','is_expired'])->group(function () {
//
//    Route::get('/pendingtoday/report', [ReportsController::class, 'pending_requests_report']);
//    Route::get('/pendingweeklate/report', [ReportsController::class, 'pending_weeklate_report']);
//    Route::get('/weeklyescalation/report', [ReportsController::class, 'weekly_escalations_report']);
//
//    Route::get('/customermonthly', [ReportsController::class, 'monthly_customer_all']);
//    Route::get('/customermonthly/{region}', [ReportsController::class, 'monthly_customer_region']);
//
//    Route::get('/weeklymis', [ReportsController::class, 'weekly_report_all'])->name('weekly_report');
//    Route::get('/weeklymis/{region}', [ReportsController::class, 'weekly_report_region']);
//
//    // Route::get('/weeklymis', 'ReportsController@weekly_report_all')->name('weekly_report');
//    // Route::get('/weeklymis/{region}', 'ReportsController@weekly_report_region');
//
//    Route::get('/export/olympus-customers', [AdditionalController::class, 'exportOlympusCustomers']);
//
//    Route::get('/exports/{id}', [AdditionalController::class, 'exportsID']);
//
//    Route::get('/mis', [HomeController::class, 'mis_report'])->name('mis');
//    Route::get('/mis/{region}', [HomeController::class, 'regionmis_report'])->name('regionmis');
//
//    Route::get('/updateNotification', [AdditionalController::class, 'notifyToUpdate']);
//
//    Route::post('ajax_upload', [PromailerController::class, 'ajaxsubmit'])->name('promailer_ajaxsubmit');
//    Route::get('promailers/updatestatus/{id}/{status}', [PromailerController::class, 'updatestatus']);
//    Route::get('employee-team/updatestatus/{id}/{status}', [EmployeeTeamController::class, 'updatestatus']);
//    // Make protected route, currently disabled but feature completed
//    // Route::get('reports/employee_requests', 'ReportsController@employee_requests');
//});
//
//
//Route::middleware(['prevent-back-history','is_expired','ipcheck','isallowed','XSS'])->prefix('admin')->group(function () {
//    Route::get('manual2sfdc/{id}', [SFDCController::class, 'manual_push']);
//    // Dashboard
//    Route::get('/home', [HomeController::class, 'home_dashboard'])->name('home');
//    Route::get('/home/{daterange}', [HomeController::class, 'home_dashboard']);
//
//    Route::get('dashboard', [HomeController::class, 'all_india_dashboard']);
//    Route::get('dashboard/{daterange}', [HomeController::class, 'all_india_dashboard']);
//    Route::get('export/dashboard/{daterange}', [HomeController::class, 'export_report_dashboard'])->name('exportreport');
//
//    Route::post('dashboard/settings/{region}/update', [HomeController::class, 'regionsettings_update']);
//    Route::get('dashboard/settings/{region}', [HomeController::class, 'regionsettings']);
//
//    Route::get('dashboard/{region}/{daterange}', [HomeController::class, 'regiondashboard']);
//    Route::get('export/dashboard/{region}/{daterange}', [HomeController::class, 'exportregion_report'])->name('exportregion');
//
//    // Requests
//    Route::resource('requests', RequestsController::class)->except([
//        'create','store',
//    ]);
//    Route::post('request-list', [RequestsController::class, 'requestList']);
//    Route::get('archive-data-filter', [RequestsController::class, 'archiveDataFilter']); //new
//    Route::get('requests/deletes/{id}', [RequestsController::class, 'deletes']);
//
//    Route::get('requests/all/export', [RequestsController::class, 'export']);
//    Route::post('requests/ajax_upload', [RequestsController::class, 'ajaxsubmit'])->name('requests_ajaxsubmit');
//
//    // Route::get('service-requests', 'ServiceController@index');
//    // Route::get('service-requests-list/{type?}', 'ServiceController@serviceList');
//    // Route::get('service-requests/{type}', 'ServiceController@indexByType');
//    Route::get('service-requests/{type}/export', [ServiceController::class, 'export']);
//
//    Route::get('service-requests/{type?}', [ServiceController::class, 'index']);
//    Route::post('service-request-list', [ServiceController::class, 'serviceRequestList']);
//
//    Route::get('academic-requests/{type?}', [AcademicController::class, 'index']);
//    Route::post('academic-request-list', [AcademicController::class, 'academicRequestList']);
//
//    //Route::get('academic-requests/{type}', 'AcademicController@indexByType');
//    Route::get('academic-requests/{type}/export', [AcademicController::class, 'export']);
//
//    Route::get('enquiry-requests/{type?}', [EnquiryController::class, 'index']);
//    Route::post('enquiry-request-list', [EnquiryController::class, 'enquiryRequestList']);
//
//    //Route::get('enquiry-requests/{type}', 'EnquiryController@indexByType');
//    Route::get('enquiry-requests/{type}/export', [EnquiryController::class, 'export']);
//
//    Route::get('installation-requests', [InstallationController::class, 'index']);
//    Route::get('installation-requests/{type}', [InstallationController::class, 'indexByType']);
//    Route::get('installation-requests/{type}/export', [InstallationController::class, 'export']);
//
//    // Feedback
//    Route::resource('feedback', FeedbackController::class)->only(['index','show']);
//    Route::post('feedback-list', [FeedbackController::class, 'feedbackList']);
//
//    // Customers
//    Route::get('customers/export', [CustomersController::class, 'export']);
//    Route::resource('customers', CustomersController::class)->except([
//        'create','store',
//    ]);
//    Route::get('customer-list', [CustomersController::class, 'customerList']);
//    Route::get('customers/deletes/{id}', [CustomersController::class, 'deletes']);
//
//    Route::resource('olympus-customers', CustomersController::class)->except([
//        'create','store',
//    ]);
//
//    // Teams
//    Route::get('email-exists', [AdditionalController::class, 'emailExistsIndex']);
//    Route::post('email-exists-verify', [AdditionalController::class, 'emailExistsVerify']);
//
//    Route::resource('employee-team', EmployeeTeamController::class)->except([
//        'show',
//    ]);
//    Route::get('employee-team/updatestatus/{id}/{status}', [EmployeeTeamController::class, 'updatestatus']);
//    Route::post('employee-team-list', [EmployeeTeamController::class, 'employeeList']);
//    Route::get('employee-team/deletes/{id}', [EmployeeTeamController::class, 'deletes']);
//
//    Route::resource('admins', AdminsController::class)->except([
//        'show',
//    ]);
//    Route::post('admins-list', [AdminsController::class, 'adminList']);
//    Route::get('admins/deletes/{id}', [AdminsController::class, 'deletes']);
//
//    Route::get('activity-log', [HomeController::class, 'activity']);
//    Route::post('activity-list', [HomeController::class, 'activityList']);
//
//    //Auto Emails
//    Route::resource('emailsmaster', AutoEmailsController::class)->except([
//        'index','create',
//    ]);
//    Route::get('emailsmaster-settings/{team}', [AutoEmailsController::class, 'settings']);
//    Route::post('emailsmaster-settings_post', [AutoEmailsController::class, 'settings_post']);
//    Route::get('emailsmaster-service', [AutoEmailsController::class, 'index_service']);
//    Route::post('emails-master-service-list', [AutoEmailsController::class, 'autoEmailServiceList']);
//    Route::get('emails-master-service/deletes/{id}', [AutoEmailsController::class, 'deletes']);
//    Route::get('emailsmaster-service/create', [AutoEmailsController::class, 'create']);
//    Route::get('emailsmaster-enquiry', [AutoEmailsController::class, 'index_enquiry']);
//    Route::get('emailsmaster-enquiry/create', [AutoEmailsController::class, 'create']);
//    Route::get('emailsmaster-academic', [AutoEmailsController::class, 'index_academic']);
//    Route::get('emailsmaster-academic/create', [AutoEmailsController::class, 'create']);
//
//    // Others
//    Route::resource('hospitals', HospitalsController::class);
//    Route::post('hospitals-list', [HospitalsController::class, 'hospitalList']);
//    Route::get('hospitals/deletes/{id}', [HospitalsController::class, 'deletes']);
//    Route::get('/export/hospital-excel', [HospitalsController::class, 'export']);
//
//    Route::resource('directrequests', DirectRequestsController::class)->only(['index','show']);
//    Route::post('direct-request-list', [DirectRequestsController::class, 'directrequestsList']);
//
//    Route::get('sapexcel', [SapController::class, 'index']);
//    Route::post('sapexcel/verify', [SapController::class, 'verify']);
//    Route::post('sapexcel/store', [SapController::class, 'store']);
//    Route::get('sapexcel/imported', [SapController::class, 'imported'])->name('sapimported');
//
//    // Development
//    Route::get('esasexcel', [EsasController::class, 'index']);
//    Route::post('esasexcel/verify', [EsasController::class, 'verify']);
//    Route::post('esasexcel/store', [EsasController::class, 'store']);
//    Route::get('esasexcel/imported', [EsasController::class, 'imported'])->name('esasimported');
//
//
//    Route::resource('promailers', PromailerController::class);
//    Route::post('promailers-list', [PromailerController::class, 'promailersList']);
//    Route::get('promailers/deletes/{id}', [PromailerController::class, 'deletes']);
//
//    Route::get('promailers/sendnotification/{id}', [PromailerController::class, 'sendnotification']);
//
//    Route::get('apirequests', [AdditionalController::class, 'api_requests_index']);
//    Route::post('api-requests-list', [AdditionalController::class, 'apirequestList']);
//    // Route::get('esasimport', 'AdditionalController@esasimport');
//    // Route::post('esasimportstore', 'AdditionalController@esasimportstore');
//
//
//
//    Route::resource('product_info', ProductInfoController::class)->only(['index','show']);
//    Route::post('product-list', [ProductInfoController::class, 'productList']);
//
//
//    Route::resource('videos', VideosController::class);
//    Route::get('videos/updatestatus/{id}/{status}', [VideosController::class, 'updatestatus']);
//    Route::get('videos/all/export', [VideosController::class, 'export']);
//    Route::get('videos/sendnotification/{id}', [VideosController::class, 'sendnotification']);
//
//    Route::get('download-request-data', [TestController::class, 'downloadRequestData'])->name('download-request-data');
//
//});
//Route::get('new-service-request', [TestController::class, 'newServiceRequest']);
//Route::post('send-mail-request-data', [TestController::class, 'sendMailRequestData'])->name('send-mail-request-data');
//Route::post('send-mail-customer-data', [TestController::class, 'sendMailCustomerData'])->name('send-mail-customer-data');
//
//Route::get('get-data', [TestController::class, 'exportNew']);
//Route::get('get-request-status/{id}', [TestController::class, 'getRequestStatus']);
//Route::get('customer-password-update', [CustomerDataController::class, 'customerPasswordUpdate']);
//
//
//Route::get('import-employee', [ImportEmployeeController::class, 'importEmployee']);
//Route::get('change-request-status', [ClosedRequestController::class, 'changeRequestStatus']);
//
//Route::get('/clearcache', function() {
//    Artisan::call('cache:clear');
//    Artisan::call('view:clear');
//    Artisan::call('config:clear');
//    Artisan::call('route:clear');
//    return "All Cache is cleared";
//});
//
//Route::get('ch', function (){
//    //return view('emails.request_escalated_new', ['id'=> 19963]);
//    //dd(bcrypt('123456'));
//    App\Customers::where('id',8987)->update([
//        'password' => bcrypt('123456789')
//    ]);
//});
//
////Route::get('testrequestescalatednew/{id}', 'ServiceRequestController@testrequestescalatednew');
//
//Route::get('message', function(){
//    $apiKey = "6c32b91e-a374-11e8-a895-0200cd936042"; // Replace with your actual 2Factor API Key
//    $otp = 22345;
//    $validity = "5 days";
//    $serviceRequestId = 123456;
//    $phoneNumber = 9340564510;
//
//    $message = "Post Delivery Acknowledgement Code for Service Request ID $serviceRequestId is: $otp This code is valid for $validity";
//
//    $url = "https://2factor.in/API/V1/$apiKey/SMS/$phoneNumber/$otp/OMSINX";
//
//    $curl = curl_init();
//
//    curl_setopt_array($curl, [
//        CURLOPT_URL => $url,
//        CURLOPT_RETURNTRANSFER => true,
//        CURLOPT_CUSTOMREQUEST => "GET",
//        CURLOPT_HTTPHEADER => [
//            "Content-Type: application/json"
//        ],
//        CURLOPT_POSTFIELDS => http_build_query([
//            'msg' => $message,
//            'template_name' => 'Acknowledgement-Happy-Code'
//        ])
//    ]);
//
//    $response = curl_exec($curl);
//    curl_close($curl);
//
//    //dd($response);
//
//});

// Route::get('/service-requests/view/{recordId}', [ServiceRequestViewPage::class, 'mount'])
//      ->name('service-requests.view');
 
Route::get('/temp-authorize', [AdditionalController::class, 'khuljasimsim']);