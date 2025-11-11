<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'App\Events\RequestStatusUpdated' => [
            'App\Listeners\MailRequestUpdated',
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
