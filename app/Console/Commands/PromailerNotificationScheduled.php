<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Artisan;

class PromailerNotificationScheduled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promailer:scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send promailer notification if scheduled for today';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
	    $this->scheduled = [
            	['id' => 82, 'date' => '12/02/2022']
            ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	$scheduled_today = array_filter($this->scheduled, function ($item) {
    	    if ($item['date'] === date('d/m/Y')) {
                return true;
    	    }
            return false;
	});
        foreach ($scheduled_today as $notification) {
          \Log::info("Sending Scheduled Notification for ".$notification['id']." at ". date('d/m/Y H:i:s'));
          Artisan::call('promailer:notification', ['id' => $notification['id'], 'all' => true]);
        }
    }
}
