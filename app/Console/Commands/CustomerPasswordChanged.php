<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customers;
use App\Hospitals;
use App\Departments;
use Illuminate\Support\Facades\Hash;
use Response;
use Mail;
use Validator;
use App\NotifyCustomer;
use Carbon\Carbon;
class CustomerPasswordChanged extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CustomerPasswordChanged:cron';

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

        $customer = Customers::where(['is_password_changed' => 0])->where('id', '<', 9290)->whereNull('deleted_at')->orderBy('id','desc')->get(); 
        foreach($customer as $customers){
            if($customers) {
                $old_pass = $customers->password;
                Customers::where('id', $customers->id)->update([
                    'old_password' => $old_pass,
                    'password' => Hash::make('Test@12356'),
                    'is_password_changed' => 1
                ]);   

                 
                
                try{
                     

                    Mail::to($customers->email)->send(new \App\Mail\SendPasswordChanged($customers));  
                }catch (Exception $e) {
                    return $e;
                }
            } 
        }   
    }
}
