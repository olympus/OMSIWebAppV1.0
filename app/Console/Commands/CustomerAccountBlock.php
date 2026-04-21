<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customers;
use Carbon\Carbon;
use App\Jobs\SendKycNotificationJob;

class CustomerAccountBlock extends Command
{
    protected $signature = 'app:customer-account-block';
    protected $description = 'Send KYC reminders and block expired accounts (Optimized for 50k users)';

    public function handle()
    {
        $today = Carbon::now()->startOfDay();

        Customers::whereNotNull('account_verify_at')
            ->whereIn('id', [7089, 7090])
            ->where('is_deleted', 0)
            ->whereNull('deleted_at')
            ->where('is_account_block', 0)
            ->select('id', 'device_token', 'account_verify_at')
            ->chunkById(1000, function ($customers) use ($today) {

                foreach ($customers as $customer) {
                    //dd($customer);
                    $verifyDate = Carbon::parse($customer->account_verify_at)->startOfDay();
                    $deadline = $verifyDate->copy()->addDays(90);

                    $daysLeft = $today->diffInDays($deadline, false);
                    //dd($daysLeft);
                    if (in_array($daysLeft, [15, 7, 3, 1])) {

                        dispatch(new SendKycNotificationJob(
                            $customer->id,
                            $customer->device_token,
                            $daysLeft,
                            'reminder'
                        ));

                    } elseif ($daysLeft <= 0) {

                        Customers::where('id', $customer->id)
                            ->update(['is_account_block' => 1]);

                        dispatch(new SendKycNotificationJob(
                            $customer->id,
                            $customer->device_token,
                            0,
                            'blocked'
                        ));
                    }
                }
            });

        $this->info('KYC process completed successfully.');
    }
}
