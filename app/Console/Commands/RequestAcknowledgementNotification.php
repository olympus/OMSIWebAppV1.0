<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; // Assuming OTP is linked to users
use App\ServiceRequests; // Assuming OTP is linked to users
use App\Customers; 
use App\NotifyCustomer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RequestAcknowledgementNotificationThreeDays;
use App\Notifications\RequestAcknowledgementNotificationFiveDays;

class RequestAcknowledgementNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send-acknowledgement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send request acknowledgment notification on the 3rd or 5th day from OTP generation';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {   
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', -1);
        

        $now = Carbon::now();
        $threeDaysAgo = $now->subDays(3)->toDateTimeString();
        $fiveDaysAgo = $now->subDays(2)->toDateTimeString(); // Since we already subtracted 3 days above

        // $usersThreeDays = ServiceRequests::where('id', 20449)->get();
        // $usersFiveDays = ServiceRequests::where('id', 20449)->get();

        $usersThreeDays = ServiceRequests::whereDate('happy_code_delivered_time', $threeDaysAgo)->get();
        $usersFiveDays = ServiceRequests::whereDate('happy_code_delivered_time', $fiveDaysAgo)->get();

        // foreach ($usersThreeDays as $user) {
        //     $customer = Customers::where('id', $user->customer_id)->first(); 
        //     $servicerequest = ServiceRequests::where('id', $user->id)->first();
        //     NotifyCustomer::send_notification('request_acknowledgement_after_3_days', $servicerequest, $customer);    
        //     $this->info("3rd-day acknowledgment notification sent to: " . $user->id);
        // }

        // foreach ($usersFiveDays as $user) {
        //     $customer = Customers::where('id', $user->customer_id)->first(); 
        //     $servicerequest = ServiceRequests::where('id', $user->id)->first();

        //     NotifyCustomer::send_notification('request_acknowledgement_after_5_days', $servicerequest, $customer);    
        //     $this->info("5th-day acknowledgment notification sent to: " . $user->id);
        // }
        
        foreach ($usersThreeDays as $user) {
            $customer = Customers::where('id', $user->customer_id)->first(); 
            $servicerequest = ServiceRequests::where('id', $user->id)->first();
            NotifyCustomer::send_notification('request_acknowledgement_after_3_days', $servicerequest, $customer);    
             
        }

        foreach ($usersFiveDays as $user) {
            $customer = Customers::where('id', $user->customer_id)->first(); 
            $servicerequest = ServiceRequests::where('id', $user->id)->first();

            NotifyCustomer::send_notification('request_acknowledgement_after_5_days', $servicerequest, $customer);    
            //$this->info("5th-day acknowledgment notification sent to: " . $user->id);
        }
    }
}
