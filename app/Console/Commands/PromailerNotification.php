<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customers;
use App\Jobs\SendPromailerNotificationJob;

class PromailerNotification extends Command
{
    protected $signature = 'promailer:notification {promailer_id}';
    protected $description = 'Send notification for promailer to all customers (queued)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $promailerId = $this->argument('promailer_id');
        $query = Customers::where('is_deleted', 0)
            ->where('status', 'active')
            ->whereNotNull('device_token')
            ->where('device_token', '!=', '')
            ->orderBy('id');
        $total = $query->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk(500, function ($customers) use ($promailerId, $bar) {
            foreach ($customers as $customer) {
                dispatch(new SendPromailerNotificationJob($promailerId, $customer->id));
                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\nAll Promailer notification jobs have been queued!");
    }
}
