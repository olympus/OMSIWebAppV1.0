<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelatedProduct extends Model
{
    use SoftDeletes;

    protected $table = 'related_products';

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function compatibleProduct()
    {
        return $this->belongsTo(Product::class, 'compatible_product_id');
    }


}
