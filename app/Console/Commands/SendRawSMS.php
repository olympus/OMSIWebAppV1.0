<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customers;

class SendRawSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send_raw_sms {--all=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send raw SMS message to all users';

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
        $message = "In this crisis, our first priority is the Safety and well-being of those we work with and service including our customers, employees, suppliers and partners.\nWith restrictions in place, we may not be able to give you the smooth support experience that you are used to with Olympus. While we try to find the balance between work and safety, we seek your kind understanding and bear with us inevitable delay/s.\nWe request you to use My Voice App anytime for your needs and assure that with resources available in hand we will try to resolve all your concerns at the earliest. (Maybe over the phone or through video call... etc).\nIn form of human touch, our strong Field force is available to serve you. Their contact numbers are readily available in App at history page once you register request in My Voice App. So please do not hesitate to approach our members directly.\nIf your friends are facing communication issues with Olympus, it would be appreciated if you recommend My Voice App download which will ascertain recorded and flawless communication.\nWe are sure that with your help and support, we will be sailing out together through these tough times.\nTake care of yourself and your near ones.";
        $customers = Customers::all();
        // $customers = Customers::where('id','>',3405)->get();

        if($this->option('all')){
            send_sms('blank_raw', $customers->find(7), "", $message);
        }else{
            $bar = $this->output->createProgressBar(count($customers));
            $bar->start();

            foreach ($customers as $customer) {
                send_sms('blank_raw', $customer, "", $message);
                $bar->advance();
            }
            $bar->finish();
        }
    }
}
