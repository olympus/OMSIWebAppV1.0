<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\ServiceRequests;
use App\Customers;
use Mail;
use App\NotifyCustomer;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Console\Commands\ArchiveServiceRequest;
use App\Console\Commands\DeleteTempCustomer;
use App\Console\Commands\SendOtpToCustomer;
use App\Console\Commands\PasswordExpired;
use App\Console\Commands\RemindPasswordExpired; 
use App\Console\Commands\RequestAcknowledgementNotification; 

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ArchiveServiceRequest::class, 
        DeleteTempCustomer::class, 
        SendOtpToCustomer::class, 
        RequestAcknowledgementNotification::class,
        PasswordExpired::class, 
        RemindPasswordExpired::class,   
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
	    $schedule->exec("mysqldump -u 'dev' -p'".env('DB_PASSWORD')."' cvm | gzip >/var/www/backups/dump-$(date +\%F).sql.gz || true")->daily()->runInBackground();

	    $schedule->exec("find /var/www/backups/  -mtime +10 -type f -delete || true")->daily()->runInBackground();

        $schedule->command('promailer:scheduled')
            ->dailyAt('13:45')->runInBackground();

        //add new cron
            //$schedule->command('ArchiveServiceRequest:cron')->dailyAt('01:00')->runInBackground();
            $schedule->command('DeleteTempCustomer:cron')->dailyAt('01:00')->runInBackground();
            $schedule->command('send_sms_notification:cron')->everyFifteenMinutes()->runInBackground();
            $schedule->command('notification:send-acknowledgement')->everyFifteenMinutes()->runInBackground();  
            //$schedule->command('PasswordExpired:cron')->dailyAt('01:00')->runInBackground();
            //$schedule->command('RemindPasswordExpired:cron')->dailyAt('01:00')->runInBackground(); 

        //end add new cron
         
         
        // Daily Pending Requests Report
        $schedule->call(function () {
            $servicerequests = file_get_contents(env('APP_URL')."/pendingtoday/report");
            if ($servicerequests != 'success') {
                \Mail::raw("Pending Requests Daily report for Olympus not delivered. Please check the logs.".request()->ip().$servicerequests, function ($message) {
                    $message->to(\Config('oly.developer_email'));
                });
            }
        })->dailyAt('17:00');

        // Daily Pending Requests for more than 1 week Report
        $schedule->call(function () {
            $servicerequests = file_get_contents(env('APP_URL')."/pendingweeklate/report");
            if ($servicerequests != 'success') {
                \Mail::raw("Pending Requests for more than 1 week  report for Olympus not delivered. Please check the logs.".request()->ip().$servicerequests, function ($message) {
                    $message->to(\Config('oly.developer_email'));
                });
            }
        })->dailyAt('17:00');

        // Send notification to user to update
        $schedule->call(function () {
            $servicerequests = file_get_contents(env('APP_URL')."/updateNotification");
            if ($servicerequests != 'success') {
                \Mail::raw("Upate notification for Olympus not delivered. Please check the logs.".request()->ip().$servicerequests, function ($message) {
                    $message->to(\Config('oly.developer_email'));
                });
            }
        })->daily()->between('1:00', '3:00');




        // Monthly Customer List
        $schedule->call(function () {
            foreach (['','/north','/east','/west','/south'] as $location) {
                $servicerequests = file_get_contents(env('APP_URL')."/customermonthly".$location);
                if ($servicerequests != 'success') {
                    \Mail::raw("Monthly Customer List for Olympus not delivered. Please check the logs.".request()->ip().$servicerequests, function ($message) {
                        $message->to(\Config('oly.developer_email'));
                    });
                }
            }
        })->monthlyOn(1, '8:10');



        // Monthly Feedback Report
        $schedule->call(function () {
            foreach (['','/north','/east','/west','/south'] as $location) {
                $servicerequests = file_get_contents(env('APP_URL')."/feedbackmonthly/report".$location);
                if ($servicerequests != 'success') {
                    \Mail::raw("Monthly Feedback Report for Olympus not delivered. Please check the logs.".request()->ip().$servicerequests, function ($message) {
                        $message->to(\Config('oly.developer_email'));
                    });
                }
            }
        })->monthlyOn(1, '8:15');




        // Monthly MIS Report
        $schedule->call(function () {
            foreach (['','/north','/east','/west','/south'] as $location) {
                $servicerequests = file_get_contents(env('APP_URL')."/mis".$location);
                if ($servicerequests != 'success') {
                    \Mail::raw("Mis Monthly report for Olympus not delivered. Please check the logs.".request()->ip().$servicerequests, function ($message) {
                        $message->to(\Config('oly.developer_email'));
                    });
                }
            }
        })->monthlyOn(1, '8:00');



        // Weekly Escalation Report
        $schedule->call(function () {
            $servicerequests = file_get_contents(env('APP_URL')."/weeklyescalation/report");
            if ($servicerequests != 'success') {
                \Mail::raw("Mis Weekly report for Olympus not delivered. Please check the logs.".request()->ip().$servicerequests, function ($message) {
                    $message->to(\Config('oly.developer_email'));
                });
            }
        })->weeklyOn(1, '8:00');




        // Weekly MIS Excel Report
        $schedule->call(function () {
            foreach (['','/north','/east','/west','/south'] as $location) {
                $servicerequests = file_get_contents(env('APP_URL')."/weeklymis".$location);
                if ($servicerequests != 'success') {
                    \Mail::raw("Mis Weekly report for Olympus not delivered. Please check the logs.".request()->ip().$servicerequests, function ($message) {
                        $message->to(\Config('oly.developer_email'));
                    });
                }
            }
        })->weeklyOn(5, '14:00');


        $schedule->call(function () {
            $servicerequests = ServiceRequests::where('is_escalated', true)->update(['is_escalated' => false]);
        })->everyMinute();

        $schedule->call(function () {
            $servicerequests = ServiceRequests::where('is_practice', true)->where('status', "!=", 'Closed')->where('request_type', 'service')->get();
            $request_types=array(
                0=>"Received",
                1=>"Assigned",
                2=>"Attended",
                3=>"Re-Assigned",
                4=>"Received_At_Repair_Center",
                5=>"Quotation_Prepared",
                6=>"PO_Received",
                7=>"Repair_Started",
                8=>"Repair_Completed",
                9=>"Ready_To_Dispatch",
                10=>"Dispatched",
                11=>"Closed"
            );
            foreach ($servicerequests as $servicerequest) {
                $customer = \App\Customers::findOrFail($servicerequest->customer_id);
                $current_index = array_search($servicerequest->status, $request_types);
                if($servicerequest->status == 'Received'){
                    $servicerequest->employee_code = 'F_0000000';
                }
                $servicerequest->status = $request_types[$current_index+1];
                $servicerequest->save();
                $status = new \App\StatusTimeline;
                $status->status =$servicerequest->status;
                $status->customer_id = $servicerequest->customer_id;
                $status->request_id = $servicerequest->id;
                $status ->save();

                NotifyCustomer::send_notification('request_update', $servicerequest, $customer);
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
