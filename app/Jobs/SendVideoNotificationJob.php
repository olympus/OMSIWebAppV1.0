<?php

namespace App\Jobs;

use App\NotifyCustomer;
use App\Video;
use App\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendVideoNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $video_id;
    protected $customer_id;

    public function __construct($video_id, $customer_id)
    {
        $this->video_id = $video_id;
        $this->customer_id = $customer_id;
    }

    public function handle()
    {
        $video = Video::find($this->video_id);
        $customer = Customers::find($this->customer_id);

        if ($video && $customer && $customer->device_token) {
            try {
                NotifyCustomer::send_new_notification('video_publish', '', $customer, $video);
                \Log::channel('single')->info("Send_Notification to {$customer->id}");
            } catch (\Exception $ex) {
                \Log::error("Queue Video: Error for customer {$customer->id}: " . $ex->getMessage());
            }
        }
    }
}
