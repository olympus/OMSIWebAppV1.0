<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ServiceRequests; 
use App\ArchiveServiceRequests;
use File;
use DB;
class ArchiveServiceRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ArchiveServiceRequest:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', -1);
        
        $month = date('m');
        //dd($month);
        if($month >= 4){
            $y = "2017";
            $pt = date('Y', strtotime('0 year'));
            $fy = $y."-04-01".":".$pt."-03-31"; 

            $start_date = $y."-04-01";
            $end_date = $pt."-03-31";

            //dd($start_date, $end_date); 
            //dd($y, $pt, $fy);

            ServiceRequests::query() 
            ->where('created_at','>=',$start_date.' 00:00:00')
            ->where('created_at','<=', $end_date.' 23:59:59') 
            ->where('status', '=' ,'Closed') 
            ->each(function ($oldRecord) {
                $newPost = $oldRecord->replicate();
                $newPost->id = $oldRecord->id;  
                $newPost->created_at = $oldRecord->created_at;  
                $newPost->updated_at = $oldRecord->updated_at;   
                $newPost->setTable('archive_service_requests');
                $newPost->save(); 
                $oldRecord->delete();
            });

        }else{
            // $y = date('Y', strtotime('-6 year'));
            // $pt = date('Y',strtotime('0 year'));
            // $fy = $y."-04-01".":".$pt."-03-31"; 
            
            // //dd($y, $pt, $fy,'r');

            // //dd($fy); die; 
            // $start_date = $y."-04-01";
            // $end_date = $pt."-03-31";

            $y = "2017";
            $pt = date('Y', strtotime('-1 year'));
            $fy = $y."-04-01".":".$pt."-03-31"; 

            $start_date = $y."-04-01";
            $end_date = $pt."-03-31";

            //dd($start_date, $end_date); 
            //dd($y, $pt, $fy);

            ServiceRequests::query() 
            ->where('created_at','>=',$start_date.' 00:00:00')
            ->where('created_at','<=', $end_date.' 23:59:59') 
            ->where('status', '=' ,'Closed') 
            ->each(function ($oldRecord) {
                $newPost = $oldRecord->replicate();
                $newPost->id = $oldRecord->id;  
                $newPost->created_at = $oldRecord->created_at;  
                $newPost->updated_at = $oldRecord->updated_at;   
                $newPost->setTable('archive_service_requests');
                $newPost->save(); 
                $oldRecord->delete();
            }); 
        }
    }
}
