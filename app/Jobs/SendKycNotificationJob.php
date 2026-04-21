<?php

namespace App\Jobs;

use App\Models\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\NotifyCustomer;

class SendKycNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $customerId;
    public $deviceToken;
    public $daysLeft;
    public $type;

    public function __construct($customerId, $deviceToken, $daysLeft, $type)
    {
        $this->customerId = $customerId;
        $this->deviceToken = $deviceToken;
        $this->daysLeft = $daysLeft;
        $this->type = $type;
    }

    public function handle()
    {
        if (empty($this->deviceToken)) {
            return;
        }

        $customer = Customers::find($this->customerId); 
        if (!$customer) {
            return;
        }

        if ($this->type === 'blocked') {
            $title = "Account Blocked:";
            $message = "Your account is blocked due to pending KYC. Please contact your supervisor.";

        } else {
            switch ($this->daysLeft) {
                case 15:
                    $title = "Reminder:";
                    $message = "Your KYC is pending. Update within 15 days to avoid account deactivation.";
                    break;
                case 7:
                    $title = "Reminder:";
                    $message = "Your KYC is pending. Update within 7 days to avoid deactivation.";
                    break;
                case 3:
                    $title = "Urgent Reminder:";
                    $message = "Your KYC is pending. Update within 3 days to avoid deactivation.";
                    break;
                case 1:
                    $title = "Final Reminder:";
                    $message = "Last day to update your KYC. Complete it today to avoid deactivation.";
                    break;
                default:
                    $message = "KYC Reminder";
            }
        }

        // Your existing notification method
        NotifyCustomer::sendCustomerNotification(
            'kyc_reminder',
            $message,
            $customer
        );
    }
}
