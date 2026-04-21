<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Badge extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function badgeProducts()
    {
        return $this->hasMany(BadgeProduct::class, 'badge_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'badge_products', 'badge_id', 'product_id')
            ->withPivot(['status'])
            ->withTimestamps();
    }
}
