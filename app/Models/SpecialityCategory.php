<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialityCategory extends Model
{
    use SoftDeletes;

    // protected $fillable = [
    //     'speciality_id',
    //     'category_id',
    //     'subcategory_id',
    //     'order',
    //     'status',
    // ];

    protected $guarded = [];
    

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class);
    }

    public function subSpeciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class, 'sub_speciality_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }
}
