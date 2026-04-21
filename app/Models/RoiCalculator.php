<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoiCalculator extends Model
{
    protected $table = 'roi_calculators';

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    } 
}
