<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use Illuminate\Http\Request;
use App\Promailer;
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
                      //$un_publish =  url('promailers/updatestatus/'.$current_user->id.'/1'); 
                    }else{
                        $status = 'Published'; 
                        //$un_publish =  url('promailers/updatestatus/'.$current_user->id.'/0'); 
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
                    if(Auth::user()->isA('superadministrator|administrator')){
                        //Publish Button
                        if($status == 1){
                            // if($current_user->status == 0){
                            //     $status = 'Unpublished';
                            //     $un_publish =  url('promailers/updatestatus/'.$current_user->id.'/1'); 
                            // }else{
                                //$status = 'Published'; 
                                $un_publish =  url('promailers/updatestatus/'.$current_user->id.'/0'); 
                            //}

                            $nestedData['options'] = " 
                                <a style='width: 100%;' class='btn btn-xs btn-success' href='{$un_publish}'>Publish</a>
                                <a style='width: 100%;' class='btn btn-xs btn-default' onclick='return confirm(`Confirm? \nSendnotification for $current_user->id`)' href='{$notification}'>Send Notification</a>
                                <a style='width: 100%;' class='btn btn-xs btn-primary' href='{$edit}'  title='Edit'>Edit</a> 
                                <a style='width: 100%;'
                                 class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure you want to delete`)' href='promailers/deletes/{$current_user->id}'>
                                    Delete
                                </a>
                            "; 
                        }
                        else{ 
                            //$status = 'Unpublished';
                            $un_publish =  url('promailers/updatestatus/'.$current_user->id.'/1');
                            $nestedData['options'] = " 
                                <a style='width: 100%;' class='btn btn-xs btn-success' href='{$un_publish}'>Publish</a>
                                <a style='width: 100%;' class='btn btn-xs btn-default' onclick='return confirm(`Confirm? \nSendnotification for $current_user->id`)' href='{$notification}'>Send Notification</a>
                                <a style='width: 100%;' class='btn btn-xs btn-primary' href='{$edit}'  title='Edit'>Edit</a> 
                                <a style='width: 100%;'
                                 class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure you want to delete`)' href='promailers/deletes/{$current_user->id}'>
                                    Delete
                                </a>
                            "; 
                        }  
                    }
                    else{
                        $nestedData['options'] = " "; 
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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('promailers.create');
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
            return  $validator->messages()->first(); 
        }  
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
            return  $validator->messages()->first(); 
        }  
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
            'title' => 'required|string',
            'body' => 'required|string',
            'nt_body' => 'required|string',
            'nt_title' => 'required|string',
            'abbreviation' => 'required|string',
            //'frontimage' => 'required'
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
            'title' => 'required|string',
            'body' => 'required|string',
            'nt_title' => 'required|string',
            'nt_body' => 'required|string',
            'abbreviation' => 'required|string'
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
        Promailer::where('id', $id)->delete();//Delete user
        return redirect('/admin/promailers')->with('message', 'Mailer deleted');
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
        Promailer::where('id', $id)->delete();//Delete user
        return redirect('/admin/promailers')->with('message', 'Mailer deleted');
    }





    public function promailersLatest()
    {
        // config('app.url')./storage/promailers/
        $promailers = Promailer::select('id', 'title', 'frontimage', 'abbreviation', 'status', 'created_at', 'updated_at')->where('status',1)->orderBy('id', 'desc')->limit(10)->get();
        foreach ($promailers as $promailer) {
            $promailer->frontimage = str_replace(storage_path('app/public'), config('app.url')."/storage/", $promailer->frontimage);
        }
        return Response::json(['status'=>200,'count'=>count($promailers),'data'=>$promailers]);
    }

    public function getPromailer(Request $request)
    {
        // config('app.url')./storage/promailers/
        $rules = [  
            'id' => 'required|exists:promailers,id', 
            //'device_token' => 'required',
            //'platform' => 'required',
            //'app_version' => 'required', 
        ]; 
        $messages = [ 
            'id.exists' => 'promailer not match.',
        ]; 
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() ) 
        {     
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);  
        }else{
            $promailer = Promailer::where('id', $request->id)->first();
            if(is_null($promailer)){
                return Response::json(['status'=>404,'data'=>'Data not found']);
            }else{
                $promailer->frontimage = str_replace(storage_path('app/public'), config('app.url')."/storage/", $promailer->frontimage);
                return Response::json(['status'=>200,'data'=>$promailer]);
            }
        }
    }
}
