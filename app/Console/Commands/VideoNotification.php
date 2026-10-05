<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customers;
use App\Jobs\SendVideoNotificationJob;

class VideoNotification extends Command
{
    protected $signature = 'video:notification {video_id}';
    protected $description = 'Send notification for video to all customers (queued)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $videoId = $this->argument('video_id');
        $query = Customers::where('status', 'active')
            ->whereNotNull('device_token')
            ->where('device_token', '!=', '')
            ->orderBy('id');
        $total = $query->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk(500, function ($customers) use ($videoId, $bar) {
            foreach ($customers as $customer) {
                dispatch(new SendVideoNotificationJob($videoId, $customer->id));
                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\nAll Video notification jobs have been queued!");
    }
}
