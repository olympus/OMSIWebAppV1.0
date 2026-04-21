<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSpeciality extends Model
{
    use SoftDeletes;

    protected $table = 'product_specialities';

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
    ];
    

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    public function speciality()
    {
        return $this->belongsTo(Speciality::class, 'speciality_id');
    }

    public function subSpeciality()
    {
        return $this->belongsTo(Speciality::class, 'sub_speciality_id');
    }
}
