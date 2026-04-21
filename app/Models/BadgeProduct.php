<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeProduct extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }
}
