<?php
namespace App\Http\Controllers;

use App\Autoemail_Setting as Setting;
use App\DataTables\AutoEmailsDataTable;
use App\Models\AutoEmails;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class AutoEmailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index_service(AutoEmailsDataTable $dataTable)
    {
        // dd($dataTable->render('autoemails.service.index'));
        return $dataTable->render('autoemails.service.index');
    }

    public function index()
    {
        return view('autoemails.service.index');
    }

    public function autoEmailServiceList(Request $request)
    {
        try {
            $columns = array(
                0 => 'id',
                1 => 'states',
                2 => 'departments',
                3 => 'to_emails',
                4 => 'cc_emails',
                5 => 'created_at',
                6 => 'updated_at',
            );

            $totalData = AutoEmails::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = "desc";

            if(empty($request->input('search.value')))
            {
                $posts = AutoEmails::offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->select('id','states','departments','to_emails','cc_emails','created_at','updated_at')
                ->get();
            }
            else{
                $search = $request->input('search.value');
                $posts =  AutoEmails::
                select('id','states','departments','to_emails','cc_emails','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('states', 'LIKE',"%{$search}%")
                ->orWhere('departments', 'LIKE',"%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir)
                ->get();

                $totalFiltered = AutoEmails::
                select('id','states','departments','to_emails','cc_emails','created_at','updated_at')
                ->where('id','LIKE',"%{$search}%")
                ->orWhere('states', 'LIKE',"%{$search}%")
                ->orWhere('departments', 'LIKE',"%{$search}%")
                ->count();
            }

            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $current_user) {
                    $edit =  url('admin/emailsmaster/'.$current_user->id.'/edit');
                    $nestedData['id'] = $current_user->id;
                    $nestedData['states'] = $current_user->states;
                    $nestedData['departments'] = $current_user->departments;
                    $nestedData['to_emails'] = $current_user->to_emails;
                    $nestedData['cc_emails'] = $current_user->cc_emails;
                    $nestedData['created_at'] = date('j M Y h:i a',strtotime($current_user->created_at));
                    $nestedData['updated_at'] = date('j M Y h:i a',strtotime($current_user->updated_at));
                    $nestedData['options'] = "
                        <a style='width: 100%;' class='btn btn-xs btn-info' href='{$edit}'  title='Edit'>Edit</a>
                        <a style='width: 100%;'
                         class='btn btn-xs btn-danger delete' onclick='return confirm(`Are you sure you want to delete`)' href='emails-master-service/deletes/{$current_user->id}'>
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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index_enquiry(AutoEmailsDataTable $dataTable)
    {
        // dd($dataTable->render('admins.index'));
        return $dataTable->render('autoemails.enquiry.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index_academic(AutoEmailsDataTable $dataTable)
    {
        // dd($dataTable->render('admins.index'));
        return $dataTable->render('autoemails.academic.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('autoemails.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            // 'sub_type' => 'required',
            // 'request_type' => 'required',
            'states' => 'required',
            'departments' => 'required',
            'to_emails' => 'required',
            'cc_emails' => 'required',
        ]);
        $autoemail = new AutoEmails;
        if ($request->filled('sub_type')) {
            $autoemail->sub_type = $request->sub_type;
        }
        $autoemail->request_type = $request->request_type;
        $autoemail->states = implode(',', $request->states);
        $autoemail->departments = implode(',', $request->departments);
        $autoemail->to_emails = implode(',', $request->to_emails);
        $autoemail->cc_emails = implode(',', $request->cc_emails);

        $autoemail->save();
        if ($request->request_type=='service') {
            return redirect(asset('/admin/emailsmaster-service'));
        } elseif ($request->request_type =='academic') {
            return redirect(asset('/admin/emailsmaster-academic'));
        } else {
            return redirect(asset('/admin/emailsmaster-enquiry'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
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
        $autoemail = AutoEmails::findOrFail($id);
        if ($autoemail->request_type=='enquiry') {
            return view('autoemails.enquiry.edit', ['autoemail'=>$autoemail]);
        } elseif ($autoemail->request_type=='service') {
            return view('autoemails.service.edit', ['autoemail'=>$autoemail]);
        } elseif ($autoemail->request_type=='academic') {
            return view('autoemails.academic.edit', ['autoemail'=>$autoemail]);
        }
        else{
            return "Page not found for $autoemail->request_type";
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
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
        $autoemail = AutoEmails::destroy($id);

        return redirect(url()->previous());
    }

    public function deletes($id)
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
        $autoemail = AutoEmails::destroy($id);

        return redirect(url()->previous());
    }

    public function settings($team)
    {
        $validator = Validator::make(
          [
            'team' => $team,
          ],[
            'team' => 'required|string',
          ]
        );

        if ($validator->fails()) {
            return  $validator->messages()->first();
        }
        if ($team == 'service' || $team == 'enquiry' || $team == 'academic') {
            return view('autoemails.settings')->with(['team' => $team]);
        } else {
            abort(404, 'Page not found');
        }
    }

    public function settings_post(Request $request)
    {
        $team = $request['team'];
        $req2 = $request->all();
        $data = array_filter($req2, function ($key) {
            return strpos($key, 'esc') === 0;
        }, ARRAY_FILTER_USE_KEY);

        Setting::where('team', $team)->delete();
        foreach ($data as $keyd => $valued) {
            foreach ($valued as $userd) {

                // print_r($userd);
                $user = new Setting;
                // dd($keyd);
                $user->esc_level = substr($keyd, 3, 1);
                $user->user_level = substr($keyd, 6, 1);
                $user->user_email = $userd;
                $user->team = $team;
                // dd($user);

                $user->save();
            }
        }
        // dd($request);
        // return view('autoemails.settings');
        return redirect('/admin/emailsmaster-settings/'.$team)->with('message', 'Updated Successfully');
    }
}
