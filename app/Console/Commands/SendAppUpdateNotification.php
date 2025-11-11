<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Customers; 
use App\NotifyCustomer;

class SendAppUpdateNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendAppUpdateNotification:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will run when the app updates, notifying customers to update their app.';

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

        Log::channel('app_update')->info('SendAppUpdateNotification cron started.');

        Customers::whereNotNull('device_token')
        ->where('is_expired', 0)
        ->where('is_deleted', 0)
        ->chunk(100, function ($customers) {
            foreach ($customers as $customer) {
                Log::channel('app_update')->info('Sending notification to customer ID: ' . $customer->id);
                NotifyCustomer::send_notification('send_app_update_notification', '', $customer);
            }
        });

        Log::channel('app_update')->info('SendAppUpdateNotification cron finished.');
    }
}
