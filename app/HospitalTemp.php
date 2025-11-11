<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class HospitalTemp extends Model
{


	protected $guarded = [];
    protected $table = 'hospital_temps';

    public function departments(){
        return $this->hasMany('App\Departments','id','dept_id');
    }
}
