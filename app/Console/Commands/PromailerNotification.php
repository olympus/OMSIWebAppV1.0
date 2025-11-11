<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\NotifyCustomer;
use App\Customers;
use App\Promailer;

class PromailerNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promailer:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification for promailer to all customers';

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
        $promailer = Promailer::where('id', 91)->first();

        if (!$promailer) {
            \Log::error("Promailer with ID 91 not found");
            return;
        }

        Customers::where('is_deleted', 0)
            ->whereNotNull('device_token')
            ->chunk(100, function ($customers) use ($promailer) {
                foreach ($customers as $customer) {
                    NotifyCustomer::send_notification('promailer_publish', $promailer, $customer); 
                    \Log::channel('single')->info("Send_Notification to {$customer->id}"); 
                }
            });

            \Log::channel('single')->info("All notifications processed successfully.");
    }

}
