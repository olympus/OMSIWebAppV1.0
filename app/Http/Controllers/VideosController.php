<?php

namespace App\Http\Controllers;

use App\DataTables\VideosDataTable;
use App\Models\Customers;
use App\Models\Video;
use App\Rules\YoutubeURL;
use Artisan;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Validator;


class VideosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(VideosDataTable $dataTable)
    {
        return $dataTable->render('videos.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('videos.create');
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
            'title' => 'required|string|regex:/^[a-zA-Z0-9\s]*$/',
            'url' => ['required', new YoutubeURL],
            'description' => 'required|string|regex:/^[a-zA-Z0-9\s]*$/',
            'nt_title' => 'string|regex:/^[a-zA-Z0-9\s]*$/',
            'nt_description' => 'string|regex:/^[a-zA-Z0-9\s]*$/',
        ]);

        $video = new Video;
        $video->title = $request->title;
        $video->nt_title = $request->nt_title;
        $video->url = $request->url;
        $video->description = $request->description;
        $video->nt_description = $request->nt_description;
        $video->enabled = 1;
        $video->save();
        return redirect('/admin/videos')->with('message', 'New video created');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Video $video
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
        $video = Video::withoutGlobalScope('enabled')->findOrFail($id);
        return view('videos.edit', ['video'=>$video]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Video $video
     * @return Response
     */
    public function update(Request $request,$id)
    {
        $video = Video::withoutGlobalScope('enabled')->findOrFail($id);
        $this->validate($request, [
            'title' => 'required|string|regex:/^[a-zA-Z0-9\s]*$/',
            'url' => ['required', new YoutubeURL],
            'description' => 'required|string|regex:/^[a-zA-Z0-9\s]*$/',
            'nt_title' => 'string|regex:/^[a-zA-Z0-9\s]*$/',
            'nt_description' => 'string|regex:/^[a-zA-Z0-9\s]*$/',
        ]);

        // $this->validate($request, [
        //     'title' => 'required',
        //     'url' => ['required', new YoutubeURL],
        //     'description' => 'required',
        // ]);

        $video->title = $request->title;
        $video->nt_title = $request->nt_title;
        $video->url = $request->url;
        $video->description = $request->description;
        $video->nt_description = $request->nt_description;
        $video->save();
        return redirect('/admin/videos/'.$video->id.'/edit')->with('message', 'Video updated');
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
            return $validator->messages()->first();
        }
        $video = Video::withoutGlobalScope('enabled')->findOrFail($id);
        $data = $video->load(['customers'=>function($q){
            $q->select('customers.id','title','first_name','last_name','email');
        }]);
        return view('videos.show', ['video'=>$data]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Video $video
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
        $video = Video::withoutGlobalScope('enabled')->findOrFail($id);
        $video->customers()->detach();
        $video->delete();
        return redirect('/admin/videos')->with('message', 'Video deleted');
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
        $video = Video::withoutGlobalScope('enabled')->whereId($id)->update(['enabled' => $status]);
        $status_text = ($status == 1) ? "Enabled" : "Disabled" ;
        return redirect('/admin/videos')->with('message', "Video $id $status_text");
    }

    public function index_api()
    {
        return Video::latest()->paginate(20);
    }

    public function show_api(Video $video)
    {
        return $video;
    }

    public function watched(Request $request, Video $video, Customers $customer)
    {
        $video->customers()->syncWithoutDetaching([$customer->id]);
        return response()->json(['status'=>'success']);
    }

    public function array_flatten($array,$return) {
        for($x = 0; $x <= count($array); $x++) {
            if(is_array($array[$x])) {
                $return = $this->array_flatten($array[$x], $return);
            }
            else {
                if(isset($array[$x])) {
                    $return[] = $array[$x];
                }
            }
        }
        return $return;
    }

    public function flattenSheetData($videos)
    {
        foreach ($videos as $video) {
            $vid_data = Arr::except($video,'customers');
            $vid_raw = [];
            foreach ($video['customers'] as $customer) {
                $customer_data = Arr::except($customer,'pivot');
                $all_vid[] = array_merge(
                    $vid_data,
                    ['viewed_at'=>$customer['pivot']['created_at']],
                    $customer_data
                );
            }
        }
        return $all_vid;
    }

    public function export()
    {
        $videos = Video::withoutGlobalScope('enabled')
            ->with(['customers'=>function($q){
                $q->select('customers.id as customer_id',DB::raw('CONCAT(title, \' \', first_name, \' \', last_name) as customer_name'),'email','mobile_number');
            }])
            ->select('id','id as video_id','title','url','description','enabled')
            ->withCount('customers as views')
            ->get()->makeHidden(['created_at_readable','id'])->toArray();
        $videos = $this->flattenSheetData($videos);

        Excel::create('All Videos', function ($excel) use ($videos) {
            $excel->sheet('Sheet1', function ($sheet) use ($videos) {
                if (count($videos) != 0) {
                    $sheet->fromArray($videos);

                    foreach ($videos as $key => $value) {
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
                    $sheet->getStyle('A1:AM1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                } else {
                    $sheet->setCellValue('A1', 'No requests to display');
                    $sheet->row(1, function ($row) {
                        $row->setBackground('#4f81bd');
                    });
                    $sheet->getStyle('A1:A1')->applyFromArray(array('font' => array('color' => array('rgb' => 'FFFFFF'))));
                }
            });
        })->export('xls');
        return redirect('/admin/videos');
    }

    public function sendnotification($id)
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
        Artisan::call('command:videonotification', [
            'video' => $id,
        ]);
        return redirect('/admin/videos')->with('message', "Notifications queued for $id");
    }
}
