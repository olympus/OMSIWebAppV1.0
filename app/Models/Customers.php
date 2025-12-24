<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

//

class Customers extends Model implements JWTSubject
{
	//
    use SoftDeletes;

    // $customer = factory(App\Customer::class)->make();

    protected $table = 'customers';

    protected $guarded = [];

    public function isEmployee($id)
    {
        $customer = $this->findOrFail($id);
        if(strpos($customer->email, '@olympus.com')){
            return true;
        }else{
            return false;
        }
    }

    protected $hidden = [
        'password',
        //'remember_token',
        //'otp',
    ];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function passwordHistories()
    {
        return $this->hasMany('App\PasswordHistory');
    }
}
