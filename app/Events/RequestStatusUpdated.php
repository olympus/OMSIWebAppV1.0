<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Models\CombinedServiceRequests;

class RequestStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $servicerequest;
    public $oldData;
    public $customer;

    public function __construct(CombinedServiceRequests $servicerequest, $customer, $oldData)
    {
        $this->servicerequest = $servicerequest;
        $this->oldData = $oldData;
        $this->customer = $customer;
    }
}
