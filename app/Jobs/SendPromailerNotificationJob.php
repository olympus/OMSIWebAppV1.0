<?php

namespace App\Jobs;

use App\NotifyCustomer;
use App\Promailer;
use App\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPromailerNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $promailer_id;
    protected $customer_id;

    public function __construct($promailer_id, $customer_id)
    {
        $this->promailer_id = $promailer_id;
        $this->customer_id = $customer_id;
    }

    public function handle()
    {
        $promailer = Promailer::find($this->promailer_id);
        $customer = Customers::find($this->customer_id);

        if ($promailer && $customer && $customer->device_token) {
            try {
                NotifyCustomer::send_notification('promailer_publish', $promailer, $customer);
                \Log::channel('single')->info("Send_Notification to {$customer->id}");
            } catch (\Exception $ex) {
                \Log::error("Queue Promailer: Error for customer {$customer->id}: " . $ex->getMessage());
            }
        }
    }
}
