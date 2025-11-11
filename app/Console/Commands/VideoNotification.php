<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\NotifyCustomer;
use App\Customers;
use App\Video;

class VideoNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:videonotification {video} {--test=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification for video to all customers';

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
        $video = Video::findOrFail($this->argument('video'));
        $customers = Customers::all();
        $customers = Customers::where('id','>',3405)->get();

        if($this->option('test')){
            $bar = $this->output->createProgressBar(count($customers));
            $bar->start();

            foreach ($customers as $customer) {
                // if($customer->id === 7){
                    NotifyCustomer::send_notification('video_publish', '', $customer, $video);
                // }
                // sleep(1);
                $bar->advance();
            }
            $bar->finish();
        }else{
            NotifyCustomer::send_notification('video_publish', '', $customers->find(7), $video);
        }
    }
}
