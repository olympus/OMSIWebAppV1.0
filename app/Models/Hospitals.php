<?php

namespace App\Models;

use App\Models\Departments;
use Illuminate\Database\Eloquent\Model;

class Hospitals extends Model
{
	//

	protected $guarded = [];

    public function departments(){
        return $this->hasMany(Departments::class,'id','dept_id');
    }

    public function customer()
    {
        return $this->hasOne(Customers::class,'id','customer_id');

    }
}
