<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\ServiceRequests;
use App\Customers;

class SendOtpToCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_sms_notification:cron';

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
        Log::channel('acknowledgement_sms')->info('SendOtpToCustomer cron started.');

        $chk_sms_send = ServiceRequests::where(['is_sms_send' => 1, 'is_happy_code' => 1])->whereNotNull('happy_code')->get();  
        foreach($chk_sms_send as $chk_sms_sends){
            Log::channel('acknowledgement_sms')->info('Processing request ID: ' . $chk_sms_sends->id);
            
            $service_req = ServiceRequests::where('sfdc_id', $chk_sms_sends->sfdc_id)->first();   
            $customer = Customers::where('id', $chk_sms_sends->customer_id)->first();    
            
            if (!$service_req || !$customer) {
                Log::channel('acknowledgement_sms')->warning('Service request or customer not found for request ID: ' . $chk_sms_sends->id);
                continue;
            }
            
            $status = send_sms_request_acknowledged($customer, $service_req);      
            
            if($status['Status'] == "Success"){ 
                ServiceRequests::where('id', $service_req->id)->update([
                    'is_sms_send' => 2
                ]);  
                Log::channel('acknowledgement_sms')->info('SMS sent successfully for request ID: ' . $service_req->id . ' AND OTP Is '. $service_req->happy_code);
            } else {
                Log::channel('acknowledgement_sms')->error('SMS sending failed for request ID: ' . $service_req->id, ['response' => $status]);
            }
        } 

        Log::channel('acknowledgement_sms')->info('SendOtpToCustomer cron finished.');
    }
}
