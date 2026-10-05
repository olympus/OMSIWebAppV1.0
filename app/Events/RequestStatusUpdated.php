<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;

class RequestStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $servicerequest;
    public $oldData;
    public $customer;

    public function __construct(Model $servicerequest, $customer, $oldData)
    {
        $this->servicerequest = $servicerequest;
        $this->oldData = $oldData;
        $this->customer = $customer;
    }
}
