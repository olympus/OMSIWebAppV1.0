<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CustomerTemp; 
use App\HospitalTemp; 
use Carbon\Carbon;
use DateTime; 

class DeleteTempCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteTempCustomer:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will delete record more than 2 days from customer temp or hospital temp table.';

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
        
        $customers = CustomerTemp::where('created_at', '<=', Carbon::now()->subdays(2)->format('Y-m-d h:i:s'))->count(); 
        if($customers > 0){
            CustomerTemp::where('created_at', '<=', Carbon::now()->subdays(2)->format('Y-m-d h:i:s'))->delete(); 
        }else{ 
            //
        }

        $hospitals = HospitalTemp::where('created_at', '<=', Carbon::now()->subdays(2)->format('Y-m-d h:i:s'))->count();  
        if($hospitals > 0){
            HospitalTemp::where('created_at', '<=', Carbon::now()->subdays(2)->format('Y-m-d h:i:s'))->delete(); 
        }else{ 
            //
        }
    }
}
