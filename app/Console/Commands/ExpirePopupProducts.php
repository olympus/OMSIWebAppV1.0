<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Carbon\Carbon;

class ExpirePopupProducts extends Command
{
    protected $signature = 'products:expire-popup';

    protected $description = 'Expire popup products after 10 days';

    public function handle()
    {

        $date = Carbon::now()->subDays(10);

        $updated = Product::where('latest_product_show_in_popup', 1)
            ->where('created_at', '<=', $date)
            ->update([
                'latest_product_show_in_popup' => 0
            ]);

        //$this->info("Popup expired for {$updated} products");

    }
}
