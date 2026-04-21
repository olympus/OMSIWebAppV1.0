<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubSpeciality extends Model
{
    use SoftDeletes;

    protected $table = 'sub_specialities';
    protected $guarded = [];

    public function speciality()
    {
        return $this->belongsTo(Speciality::class, 'speciality_id');
    }
}
