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



class PasswordExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PasswordExpired:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'when customer password will expired in 90 or more days then this command will run and send a notification to customer.';

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
     * @return int
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
            if($days == 90){   
                Customers::where('id',$customers->id)->update([ 
                    'is_expired' => 1,
                ]);
                NotifyCustomer::send_notification('password_expired','', $customers);  
                Mail::to($customers->email)->send(new \App\Mail\SendPasswordExpired($customer, $type)); 
            }  
        } 

        $admin = User::where('password_updated_at', '!=', null)->where('is_expired', 0)->get();
        foreach($admin as $admins){
            $fdate = $admins->password_updated_at;
            $tdate = Carbon::now(); 
            $datetime1 = new DateTime($fdate);
            $datetime2 = new DateTime($tdate);
            $interval = $datetime1->diff($datetime2);
            $days = $interval->format('%a');
            $type = 0; 
            if($days == 90){  
                $customer = User::where('id', $admins->id)->first();
                User::where('id',$admins->id)->update([ 
                    'is_expired' => 1,
                ]); 
                Mail::to($admins->email)->send(new \App\Mail\SendPasswordExpired($customer, $type)); 
            };   
        }

        // $admins = User::where('id', 1)->first();
        // //foreach($admin as $admins){
        //     $fdate = $admins->password_updated_at;
        //     $tdate = Carbon::now(); 
        //     $datetime1 = new DateTime($fdate);
        //     $datetime2 = new DateTime($tdate);
        //     $interval = $datetime1->diff($datetime2);
        //     $days = $interval->format('%a');
        //     $type = 0; 
        //     //if($days == 90){  
        //         $customer = User::where('id', $admins->id)->first();
        //         User::where('id',$admins->id)->update([ 
        //             'is_expired' => 1,
        //         ]); 
        //         Mail::to($admins->email)->send(new \App\Mail\SendPasswordExpired($customer, $type)); 
        //     //};   
        // //} 
    }
}
