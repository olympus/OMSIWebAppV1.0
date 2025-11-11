<?php
namespace App\Http\Controllers\API\V2; 
use Exception;
use App\Http\Controllers\Controller;  
use Illuminate\Http\Request;
use App\Promailer;
use App\CustomerShowPromailer;
use App\DataTables\PromailersDataTable;
use Response;
use Validator;
use Artisan;

class PromailerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PromailersDataTable $dataTable)
    {
        return $dataTable->render('promailers.index');
    }

    public function index1()
    {   
        return view('promailers.index'); 
    }

    public function promailersList(Request $request)
    {   
        try{
            $columns = array( 
                0 => 'id', 
                1 => 'title',
                2 => 'abbreviation',
                3 => 'frontimage',
                4 => 'created_at',
                5 => 'status',
                6 => 'updated_at' 
            );
             
            $totalData = Promailer::count();

            $totalFiltered = $totalData; 

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = "desc";

            if(empty($request->input('search.value')))
            {            
                $posts = Promailer::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('id','title','abbreviation','frontimage','created_at','status','updated_at') 
                ->get();
            }
            else{
                $search = $request->input('search.value');  
                $posts =  Promailer::  
                select('id','title','abbreviation','frontimage','created_at','status','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('title', 'LIKE',"%{$search}%")
                ->orWhere('abbreviation', 'LIKE',"%{$search}%")
                ->orWhere('status', 'LIKE',"%{$search}%") 
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();

                $totalFiltered = Promailer:: 
                select('id','title','abbreviation','frontimage','created_at','status','updated_at') 
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('title', 'LIKE',"%{$search}%")
                ->orWhere('abbreviation', 'LIKE',"%{$search}%")
                ->orWhere('status', 'LIKE',"%{$search}%") 
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {   
                foreach ($posts as $current_user) {
                    
                    //$url= asset('storage/'.$current_user->frontimage);
                    $url = $current_user->frontimage;
                    $image = '<img src="'.$url.'" border="0" width="40" class="img-rounded" align="center" />';
                    
                    if($current_user->status == 0){
                        $status = 'Unpublished';
                        $un_publish =  url('promailers/updatestatus/'.$current_user->id.'/1'); 
                    }else{
                        $status = 'Published'; 
                        $un_publish =  url('promailers/updatestatus/'.$current_user->id.'/0'); 
                    }

                    $notification =  url('admin/promailers/sendnotification/'.$current_user->id); 
                    $edit =  url('admin/promailers/'.$current_user->id.'/edit');  
                    //dd($un_publish);
                     

                    $nestedData['id'] = $current_user->id; 
                    $nestedData['title'] = $current_user->title; 
                    $nestedData['abbreviation'] = $current_user->abbreviation;
                    $nestedData['image'] = $image;
                    $nestedData['status'] = $status; 
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($current_user->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($current_user->updated_at)); 
                    $nestedData['options'] = " 
                    <a style='width: 100%;' class='btn btn-xs btn-success' href='{$un_publish}'>Publish</a>
                    <a style='width: 100%;' class='btn btn-xs btn-default' onclick='return confirm(`Confirm? \nSendnotification for $current_user->id`)' href='{$notification}'>Send Notification</a>
                    <a style='width: 100%;' class='btn btn-xs btn-primary' href='{$edit}'  title='Edit'>Edit</a> 
                    <a style='width: 100%;'
                     class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure you want to delete`)' href='promailers/deletes/{$current_user->id}'>
                        Delete
                    </a>
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('promailers.create');
    }

    public function sendnotification($id)
    {
        Artisan::call('promailer:notification', [
	    'id' => $id,
           'all' => true,
        ]);
        return redirect('/admin/promailers')->with('message', "Notifications sent for promailer $id");
    }


    public function ajaxsubmit(Request $request)
    {
        $validation = Validator::make($request->all(), [
      'select_file' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:8192'
     ]);
        if ($validation->passes()) {
            $file = $request->file('select_file');
            $fileOriginalName = $file->getClientOriginalName();
            $new_name = rand() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/promailers'), $new_name);
            return response()->json([
       'message'   => 'File Upload Successfully',
       'uploaded_image' => env('APP_URL')."/storage/promailers/".$new_name,
       'file_name' => $fileOriginalName,
       'class_name'  => 'alert-success'
      ]);
        } else {
            return response()->json([
       'message'   => $validation->errors()->all(),
       'uploaded_image' => '',
       'class_name'  => 'alert-danger'
      ]);
        }
    }

    public function updatestatus($id, $status)
    {
        Promailer::where('id', $id)->update(['status' => $status]);
        $status_text = ($status == 1) ? "Published" : "Unpublished" ;
        return redirect('/admin/promailers')->with('message', "Mailer $id $status_text");
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->toArray());
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'nt_body' => 'required',
            'nt_title' => 'required',
            'abbreviation' => 'required',
            'frontimage' => 'required|image|mimes:jpeg,png,jpg,gif|max:8192'
        ]);
        
        // $attachments = $request->file('frontimage');

        $destinationPath = storage_path('app/public/promailers');
        $publicPath = config('app.url')."/storage/promailers/";
        $allowed_filetypes = array("bmp","gif","jpg","jpeg","png");
        // // array("csv","xls","xlsx","doc","pdf","txt","bmp","gif","jpg","jpeg","png");
        // $files_location = array();
        // if(count($attachments)) {
        //     foreach ($attachments as $attachment) {
        //         $fileOriginalName = $attachment->getClientOriginalName();
        //         $fileName = str_replace(' ','',$attachment->getClientOriginalName());
        //         $fileName = substr($fileName, 0, strrpos($fileName, '.') ) .'_'.time() . substr($fileName, strrpos($fileName, '.'));
        //         $fileExtension = $attachment->getClientOriginalExtension();
        //         $getRealPath = $attachment->getRealPath();
        //         $getSize = $attachment->getSize();
        //         $getMimeType = $attachment->getMimeType();
        //         $fileSavePath = $destinationPath.'/'.$fileName;

        //         echo '<b>File Name:</b> '.$fileName.'<br>'.'<b>File Extension:</b> '.$fileExtension.'<br>'.'<b>File Real Path:</b> '.$getRealPath.'<br>'.'<b>File Size:</b> '.$getSize.'<br>'.'<b>File Mime Type:</b> '.$getMimeType.'<br>'.'<b>File Save Path:</b> '.$fileSavePath.'<br><br>';

        //         if(!in_array($fileExtension, $allowed_filetypes)){
        //             $validator = Validator::make([], []);
        //             $validator->getMessageBag()->add('attachment', 'File Extension <b>'. $fileExtension .'</b> not allowed.'.' <br><b>ALLOWED TYPES:</b> '.implode(', ', $allowed_filetypes));
        //             return \Redirect::back()->withErrors($validator)->withInput();
        //         }
        //         $attachment->move($destinationPath,$fileName); //Move Uploaded File
        //         array_push($files_location, array("storage_path"=>$destinationPath.'/'.$fileName, "public_path"=>$publicPath.$fileName, "Name"=>$fileOriginalName) );
        //     }
        // }

        $frontimage = $request->file('frontimage');
        $fimage_fileName = str_replace(' ', '', $frontimage->getClientOriginalName());
        $fimage_fileName = substr($fimage_fileName, 0, strrpos($fimage_fileName, '.')) .'_'.time() . substr($fimage_fileName, strrpos($fimage_fileName, '.'));
        $frontimage->move($destinationPath, $fimage_fileName);
        $fimage_fileSavePath = $destinationPath.'/'.$fimage_fileName;

        $mail = new Promailer;
        $mail->title = $request->title;
        $mail->frontimage = $fimage_fileSavePath;
        $mail->body = $request->body;
        $mail->nt_title = $request->nt_title;
        $mail->nt_body = $request->nt_body;
        $mail->abbreviation = $request->abbreviation;
        // $mail->status = 0;
        // $mail->attachments = serialize($files_location);
        $mail->save();
        // serialize($array);
        // dd($request->toArray());
        return redirect('/admin/promailers')->with('message', 'New mailer created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('promailers.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // dd(storage_path(''));
        $mailer = Promailer::findOrFail($id);
        return view('promailers.edit', ['mailer'=>$mailer]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->toArray());
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'nt_title' => 'required',
            'nt_body' => 'required',
            'abbreviation' => 'required'
        ]);
        
        $mail = Promailer::find($id);
        // $attachments = $request->file('frontimage');
        if (isset($request->frontimage)) {
            $destinationPath = storage_path('app/public/promailers');
            $publicPath = config('app.url')."/storage/promailers/";
            $allowed_filetypes = array("bmp","gif","jpg","jpeg","png");

            $frontimage = $request->file('frontimage');
            $fimage_fileName = str_replace(' ', '', $frontimage->getClientOriginalName());
            $fimage_fileName = substr($fimage_fileName, 0, strrpos($fimage_fileName, '.')) .'_'.time() . substr($fimage_fileName, strrpos($fimage_fileName, '.'));
            $frontimage->move($destinationPath, $fimage_fileName);
            $fimage_fileSavePath = $destinationPath.'/'.$fimage_fileName;
            
            $mail->frontimage = $fimage_fileSavePath;
        }
        $mail->title = $request->title;
        $mail->body = $request->body;
        $mail->nt_title = $request->nt_title;
        $mail->nt_body = $request->nt_body;
        $mail->abbreviation = $request->abbreviation;
        $mail->save();
        return redirect('/admin/promailers/'.$id.'/edit')->with('message', 'Mailer updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Promailer::where('id', $id)->delete();//Delete user
        return redirect('/admin/promailers')->with('message', 'Mailer deleted');
    }


    public function deletes($id)
    {
        Promailer::where('id', $id)->delete();//Delete user
        return redirect('/admin/promailers')->with('message', 'Mailer deleted');
    } 
    
    public function promailersLatest()
    {
        // config('app.url')./storage/promailers/
        $user = auth('customer-api')->user();   
        if (count((array)$user) > 0) {
            if($user->is_expired == 0){ 
                $customers_id = $user->id;
                $promailers = Promailer::select('id', 'title', 'frontimage', 'abbreviation', 'status', 'created_at', 'updated_at')->where('status',1)->orderBy('id', 'desc')->get();
                $show_promailer = CustomerShowPromailer::where('customers_id', $customers_id)->get();  
                $count_promailers = Promailer::select('id', 'title', 'frontimage', 'abbreviation', 'status', 'created_at', 'updated_at')->where('status',1)->orderBy('id', 'desc')->get();

                if(!empty($show_promailer) || count($show_promailer) > 0){
                    $left_promailer = count($count_promailers) - count($show_promailer);  
                }else{
                    $left_promailer = count($count_promailers);
                }
                
                foreach ($promailers as $promailer) {
                    $promailer->frontimage = str_replace(storage_path('app/public'), config('app.url')."/storage/", $promailer->frontimage);
                    $show_promailer = CustomerShowPromailer::where('promailers_id', $promailer->id )->where('customers_id', $customers_id)->first();  
                    if(!empty($show_promailer)){
                        $promailer->is_read = 1;
                    }else{
                        $promailer->is_read = 0;
                    }
                    
                }
                return Response::json(['status_code'=>200,'count'=> $left_promailer,'data'=>$promailers]);
            }else{
                return response()->json([
                    'status_code' => 407, 
                    'message' => 'password expired', 
                    'is_expired' => $user->is_expired
                ]);
            } 
        }else {
            return response()->json([
                'status_code' => 400, 
                'message' => 'user not found',  
            ]);
        } 
    }

    public function getPromailer(Request $request)
    {
        // config('app.url')./storage/promailers/
        $rules = [  
            'id' => 'required|exists:promailers,id', 
            // 'device_token' => 'required',
            // 'platform' => 'required',
            // 'app_version' => 'required', 
        ]; 
        $messages = [ 
            'id.exists' => 'promailer not match.',
        ]; 
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() ) 
        {     
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);  
        }else{
            $user = auth('customer-api')->user();  
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){ 
                    $customers_id = $user->id; 
                    $promailer = Promailer::where('id', $request->id)->first();
                    if(is_null($promailer)){
                        return Response::json(['status_code'=>404,'data'=>'Data not found']);
                    }else{
                        $show_promailer = CustomerShowPromailer::where('promailers_id', $request->id )->where('customers_id', $customers_id)->first(); 
                        if(empty($show_promailer)){ 
                           $data = new CustomerShowPromailer();
                           $data->promailers_id = $request->id; 
                           $data->customers_id = $customers_id;
                           $data->save();  
                        } 
                        $promailer->frontimage = str_replace(storage_path('app/public'), config('app.url')."/storage/", $promailer->frontimage);

                        // Convert body only if created in new format
                        $cutOffDate = '2025-10-01'; // change as per your requirement

                        if ($promailer->created_at >= $cutOffDate) {

                            // Decode new JSON body
                            $bodyData = json_decode($promailer->body, true);

                            if (is_array($bodyData)) { 
                                $promailer->body = $this->convertPromailerToOldFormat($bodyData);
                            }
                        } else {
                            // Old promailer - decode to keep consistent output
                            $promailer->body = json_decode($promailer->body, true);
                        }

                        $promailer->body = json_encode($promailer->body, true); 

                        return Response::json(['status_code'=>200,'data'=>$promailer]);
                    }
                }else{
                    return response()->json([
                        'status_code' => 407, 
                        'message' => 'password expired', 
                        'is_expired' => $user->is_expired
                    ]);
                } 
            }else {
                return response()->json([
                    'status_code' => 400, 
                    'message' => 'user not found',  
                ]);
            } 
        }
    }

    public function showPromailer(Request $request)
    { 
        $rules = [  
            'promailer_id' => 'required|exists:promailers,id',  
            'customer_id' => 'required|exists:customers,id',  
        ]; 
        $messages = [ 
            'promailer_id.exists' => 'promailer not match.',
            'customer_id.exists' => 'customer not found.',
        ]; 
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() ) 
        {     
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);  
        }else{
            $user = auth('customer-api')->user();  
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){  
                    $promailer = CustomerShowPromailer::where('promailers_id', $request->promailer_id )->where('customers_id', $request->customer_id )->first(); 
                    if(empty($promailer)){ 
                       $data = new CustomerShowPromailer();
                       $data->promailers_id = $request->promailer_id; 
                       $data->customers_id = $request->customer_id;
                       $data->save(); 
                        //if(!empty($data)){
                            return Response::json(['status_code'=>200,'data'=>$data]);
                        // }else{
                        //     return Response::json(['status_code'=>202,'data'=>$data]);
                        // } 
                    }else{ 
                        return Response::json(['status_code'=>200,'data'=>$promailer]);
                    }
                }else{
                    return response()->json([
                        'status_code' => 407, 
                        'message' => 'password expired', 
                        'is_expired' => $user->is_expired
                    ]);
                } 
            }else {
                return response()->json([
                    'status_code' => 400, 
                    'message' => 'user not found',  
                ]);
            } 
        }
    }

    /**
     * Convert new Promailer JSON format to old API format.
     */
    private function convertPromailerToOldFormat(array $newData): array
    {
        $oldData = [];

        foreach ($newData as $item) {
            $fileName = '';

            if ($item['type'] === 'image') {
                $filePath = $item['data']['value'] ?? $item['value'] ?? '';
                $filePathValue = env('APP_URL', 'https://omsi-revamp.lyxelandflamingotech.in').'/storage/promailers/images/'.basename($filePath);

                $fileName = basename($filePath);
                $oldData[] = [
                    'type' => 'image',
                    'value' => $filePathValue,
                    'file_name' => $fileName,
                ];
                continue;
            }

            if ($item['type'] === 'url') {
                $url = $item['data']['value'] ?? $item['value'] ?? '';
                $oldData[] = [
                    'type' => 'url',
                    'value' => $url,
                    'file_name' => '',
                ];
                continue;
            }

            if ($item['type'] === 'paragraph') {
                $text = $item['data']['value'] ?? $item['value'] ?? '';
                $oldData[] = [
                    'type' => 'paragraph',
                    'value' => $text,
                    'file_name' => '',
                ];
            }
        }

        return $oldData;
    }
}
