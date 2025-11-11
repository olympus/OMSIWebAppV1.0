<?php

namespace App\Console\Commands;
 
use Illuminate\Console\Command;
use App\Customers;
use App\User;
use App\Employee;
use Carbon\Carbon;
use DateTime;
use App\NotifyCustomer;
use Mail;
class RemindPasswordExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RemindPasswordExpired:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'when customer password will expired in 90 then this command will run to remind before 7 days password expiration and send a notification to customer';

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
        
        $customer = Customers::where('password_updated_at', '!=', null)->where('is_expired', 0)->get();
        foreach($customer as $customers){
            $fdate = $customers->password_updated_at;
            $tdate = Carbon::now(); 
            $datetime1 = new DateTime($fdate);
            $datetime2 = new DateTime($tdate);
            $interval = $datetime1->diff($datetime2);
            $days = $interval->format('%a');  
            $type = 1;
            if($days == 80){    
                $day = 10;
                NotifyCustomer::send_notification('remind_password_expired_before_10_days','', $customers);  
                Mail::to($customers->email)->send(new \App\Mail\RemindSendPasswordExpired($customer, $type, $day)); 
            }else if($days == 87){
                $day = 3;    
                NotifyCustomer::send_notification('remind_password_expired_before_3_days','', $customers);  
                Mail::to($customers->email)->send(new \App\Mail\RemindSendPasswordExpired($customer, $type, $day)); 
            }  
        } 

        /*$admin = User::where('password_updated_at', '!=', null)->where('is_expired', 0)->get();
        foreach($admin as $admins){
            $fdate = $admins->password_updated_at;
            $tdate = Carbon::now(); 
            $datetime1 = new DateTime($fdate);
            $datetime2 = new DateTime($tdate);
            $interval = $datetime1->diff($datetime2);
            $days = $interval->format('%a');
            $type = 0;
            if($days == 83){  
                $customer = User::where('id', $admins->id)->first(); 
                Mail::to($admins->email)->send(new \App\Mail\RemindSendPasswordExpired($customer, $type)); 
            };   
        }*/
    }
}
